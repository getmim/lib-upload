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

    public function initAction() {
        if(!$this->user->isLogin())
            return $this->resp(401);

        $form = new Form('lib-upload');

        if(!$form->validate())
            return $this->resp(422, $form->getErrors());

        $result = $form->getResult();

        $file_md5 = md5_file($result->file['tmp_name']);

        $handlers = $this->config->libUpload->keeper->handlers;

        // make sure the file is not yet uploaded
        $media = Media::getOne(['identity'=>$file_md5]);
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
                if(!$class::save($file)){
                    $error = $class::lastError();
                    break;
                }
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
                'identity'  => $file_md5
            ];

            if($image_height)
                $media['height'] = $image_height;
            if($image_width)
                $media['width'] = $image_width;

            $id = Media::create($media);

            $media = Media::getOne(['id'=>$id]);
        }

        $keeper = $this->config->libUpload->keeper->handler;
        $handler = $handlers->$keeper->class;
        
        return $this->resp(0, [
            'path' => $media->path
        ]);
    }
}