<?php
/**
 * Downloader
 * @package lib-upload
 * @version 0.1.0
 */

namespace LibUpload\Library;

use LibCurl\Library\Curl;
use LibValidator\Library\Validator;
use LibUpload\Model\Media;

class Downloader
{
    protected static $last_error;

    public static function lastError()
    {
        return self::$last_error;
    }

    public static function download(string $url, string $form): ?object
    {
        $tmp_file = tempnam(sys_get_temp_dir(), '-dl');
        $opts = [
            'url' => $url,
            'download' => $tmp_file
        ];
        Curl::fetch($opts);

        $file = [
            'error' => null,
            'size' => filesize($tmp_file),
            'type' => mime_content_type($tmp_file),
            'name' => $url,
            'tmp_name' => $tmp_file
        ];

        $object = (object)[
            'form' => $form,
            'file' => $file
        ];
        $rules = (object)[
            'file' => (object)[
                'rules' => (object)[
                    'required' => true,
                    'upload-file' => $form
                ]
            ]
        ];
        list($result, $error) = Validator::validate($rules, $object);

        if ($error) {
            self::$last_error = $error['file']->text;
            return null;
        }

        $file_md5 = md5_file($tmp_file);
        $handlers = \Mim::$app->config->libUpload->keeper->handlers;
        $up_form = (array)(\Mim::$app->config->libUpload->forms->{$form}->keeper ?? []);
        if ($up_form) {
            $used_handlers = [];
            foreach ($handlers as $keeper => $opt) {
                if (in_array($keeper, $up_form)) {
                    $used_handlers[$keeper] = $opt;
                }
            }
            $handlers = $used_handlers;
        }

        // make sure the file is not yet uploaded
        $media = Media::getOne(['identity'=>$file_md5]);
        $file_urls = [];

        if (!$media) {
            $image_width = 0;
            $image_height= 0;

            $file = (object)[
                'name'   => $file['name'],
                'type'   => $file['type'],
                'size'   => $file['size'],
                'source' => $file['tmp_name'],
                'target' => null
            ];

            if (fnmatch('image/*', $file->type)) {
                list($image_width, $image_height) = getimagesize($file->source);
            }

            $target = substr($file_md5, 0, 2) . '/'
                    . substr($file_md5, 2, 2) . '/'
                    . substr($file_md5, 4, 2) . '/'
                    . substr($file_md5, 6, 2) . '/';

            $exts = explode('.', $file->name);
            $ext  = end($exts);
            $ext  = strtolower($ext);

            $target_name = $file_md5 . '.' . $ext;

            $target .= $target_name;

            $file->target = $target;

            $error = false;

            foreach ($handlers as $keeper => $opt) {
                if (!$up_form && !$opt->use) {
                    continue;
                }

                $class = $opt->class;
                if (!($file_url = $class::save($file))) {
                    $error = $class::lastError();
                    break;
                }

                $file_urls[] = $file_url;
            }

            if ($error) {
                self::$last_error = $error;
                return null;
            }

            if (!$file_urls) {
                self::$last_error = 'No file keeper used to save the file';
                return nul;
            }

            // now insert it to db
            $media = [
                'name'      => $target_name,
                'original'  => $file->name,
                'mime'      => $file->type,
                'user'      => 0,
                'path'      => $target,
                'form'      => $form,
                'size'      => $file->size,
                'identity'  => $file_md5,
                'urls'      => json_encode($file_urls)
            ];

            if ($image_height) {
                $media['height'] = $image_height;
            }
            if ($image_width) {
                $media['width'] = $image_width;
            }

            if (!$id = Media::create($media)) {
                self::$last_error = Media::lastError();
                return null;
            }

            // if ($this->object_id) {
            //     MAuth::create([
            //         'media'  => $id,
            //         'type'   => $this->object_type,
            //         'object' => $this->object_id
            //     ]);
            // }

            $media = Media::getOne(['id'=>$id]);
        }

        $file_urls = json_decode($media->urls);

        unlink($tmp_file);
        return (object)[
            'id'   => (int)$media->id,
            'url'  => $file_urls[0] ?? null,
            'path' => $media->path,
            'name' => $media->original,
            'type' => $media->mime,
            'size' => (int)$media->size
        ];
    }
}
