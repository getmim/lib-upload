<?php
/**
 * Validator handler
 * @package lib-upload
 * @version 0.0.1
 */

namespace LibUpload\Validator;

class Upload
{
    static function file($value, $opts, $object, $field, $rules): ?array{
        $form = $object->form ?? null;
        if(!$form)
            return null;
        $rules = \Mim::$app->config->libUpload->forms->$form ?? null;
        if(!$rules)
            return null;

        // size
        if(isset($rules->size)){
            $size = $rules->size;
            // size min
            if(isset($size->min) && $size->min > $value['size'])
                return ['16.0.1'];
            // size max
            if(isset($size->max) && $size->max < $value['size'])
                return ['16.0.2'];
        }

        // mime
        if(isset($rules->mime)){
            $matched = false;
            foreach($rules->mime as $mime){
                if(fnmatch($mime, $value['type'])){
                    $matched = true;
                    break;
                }
            }

            if(!$matched)
                return ['16.1'];
        }

        // exts
        if(isset($rules->exts)){
            $matched = false;
            $exts = explode('.', $value['name']);
            $ext  = end($exts);
            $ext  = strtolower($ext);

            foreach($rules->exts as $rext){
                if($rext === '*')
                    $rext = $ext;

                if($rext === $ext){
                    $matched = true;
                    break;
                }
            }

            if(!$matched)
                return ['16.2'];
        }

        // image
        if(isset($rules->image) && fnmatch('image/*', $value['type'])){
            $image = $rules->image;

            list($file_width, $file_height) = getimagesize($value['tmp_name']);

            // width
            if(isset($image->width)){
                $width = $image->width;
                if(isset($width->min) && $width->min > $file_width)
                    return ['16.3.1'];
                if(isset($width->max) && $width->max < $file_width)
                    return ['16.3.2'];
            }

            // height
            if(isset($image->height)){
                $height = $image->height;
                if(isset($height->min) && $height->min > $file_height)
                    return ['16.4.1'];
                if(isset($height->max) && $height->max < $file_height)
                    return ['16.4.2'];
            }
        }

        return null;
    }

    static function form($value, $opts, $object, $field, $rules): ?array{
        if(isset(\Mim::$app->config->libUpload->forms->$value))
            return null;
        return ['15.0'];
    }
}