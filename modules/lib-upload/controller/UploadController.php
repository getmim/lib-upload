<?php
/**
 * UploadController
 * @package lib-upload
 * @version 0.0.1
 */

namespace LibUpload\Controller;

use LibForm\Library\Form;
use LibUpload\Model\Media;

class UploadController extends \Api\Controller
{
    public function filterAction(){
        if(!$this->user->isLogin())
            return $this->resp(401);

        $cond = [];

        if($this->config->libUpload->filter->own)
            $cond['user'] = $this->user->id;

        if(!is_null($hash = $this->req->getQuery('hash')))
            $cond['identity'] = $hash;

        if(!is_null($mime = $this->req->getQuery('type'))){
            if(false === strstr($mime, '*')){
                $cond['mime'] = explode(',', $mime);
            }else{
                $mimes = explode('/', $mime);
                $left  = false;
                $right = false;

                if(count($mimes) === 2){
                    if($mimes[0] === '*')
                        $left = true;
                    if($mimes[1] === '*')
                        $right = true;

                    if($left && $right)
                        $position = 'both';
                    elseif($left)
                        $position = 'left';
                    elseif($right)
                        $position = 'right';

                    $mime = str_replace('*', '', $mime);
                    $cond['mime'] = ['__like', $mime, $position];
                }
            }
        }

        if(!is_null($name = $this->req->getQuery('query') ?? $this->req->getQuery('name')))
            $cond['original'] = ['__like', $name];

        $result = [];

        $media = Media::get($cond, 20, 1);
        if($media){
            foreach($media as $medium){
                $file_urls = json_decode($medium->urls);
                $result[] = [
                    'url'  => $file_urls[0] ?? NULL,
                    'path' => $medium->path,
                    'name' => $medium->original,
                    'type' => $medium->mime,
                    'size' => (int)$medium->size
                ];
            }
        }

        $this->resp(0, $result);
    }

    public function initAction() {
        if(!$this->user->isLogin())
            return $this->resp(401);

        $form = new Form('lib-upload');

        if(!$form->validate())
            return $this->resp(422, $form->getErrors());

        $result  = $form->getResult();
        $up_form = (array)($this->config->libUpload->forms->{$result->form}->keeper ?? []);

        $file_md5 = md5_file($result->file['tmp_name']);

        $handlers = $this->config->libUpload->keeper->handlers;
        if($up_form){
            $used_handlers = [];
            foreach($handlers as $keeper => $opt){
                if(in_array($keeper, $up_form))
                    $used_handlers[$keeper] = $opt;
            }
            $handlers = $used_handlers;
        }
        
        // make sure the file is not yet uploaded
        $media = Media::getOne(['identity'=>$file_md5]);
        $file_urls = [];
        if(!$media){
            $image_width = 0;
            $image_height= 0;

            $file = (object)[
                'name'   => $result->file['name'],
                'type'   => $result->file['type'],
                'size'   => $result->file['size'],
                'source' => $result->file['tmp_name'],
                'target' => null
            ];

            if(fnmatch('image/*', $file->type))
                list($image_width, $image_height) = getimagesize($file->source);

            $target = substr($file_md5, 0, 2) . '/'
                    . substr($file_md5, 2, 2) . '/'
                    . substr($file_md5, 4, 2) . '/'
                    . substr($file_md5, 6, 2) . '/';

            $exts = explode('.', $file->name);
            $ext  = end($exts);
            $ext  = strtolower($ext);

            $target_name = $file_md5 . '.' . $ext;

            $target.= $target_name;

            $file->target = $target;

            $error = false;
            
            foreach($handlers as $keeper => $opt){
                if(!$opt->use)
                    continue;
                $class = $opt->class;
                if(!($file_url = $class::save($file))){
                    $error = $class::lastError();
                    break;
                }

                $file_urls[] = $file_url;
            }

            if($error)
                return $this->resp(500, null, $error);

            // now insert it to db
            $media = [
                'name'      => $target_name,
                'original'  => $file->name,
                'mime'      => $file->type,
                'user'      => $this->user->id,
                'path'      => $target,
                'form'      => $result->form,
                'size'      => $file->size,
                'identity'  => $file_md5,
                'urls'      => json_encode($file_urls)
            ];

            if($image_height)
                $media['height'] = $image_height;
            if($image_width)
                $media['width'] = $image_width;

            if(!$id = Media::create($media))
                return $this->resp(500, Media::lastError());

            $media = Media::getOne(['id'=>$id]);
        }

        $file_urls = json_decode($media->urls);
        return $this->resp(0, [
            'url'  => $file_urls[0] ?? NULL,
            'path' => $media->path,
            'name' => $media->original,
            'type' => $media->mime,
            'size' => (int)$media->size
        ]);
    }
}