<?php
/**
 * Validator handler
 * @package lib-upload
 * @version 0.0.1
 */

namespace LibUpload\Validator;

use LibUpload\Model\Media;

class Upload
{
    private static function getFileId(string $file): ?string{
        $handlers = \Mim::$app->config->libUpload->keeper->handlers;
        
        foreach($handlers as $handler){
            $class = $handler->class;
            $value = $class::getId($file);
            if($value)
                return $value;
        }

        return null;
    }

    private static function validateMedia(object $media, string $form): ?array{
        $rules = \Mim::$app->config->libUpload->forms->$form ?? null;
        if(!$rules)
            return null;

        // size
        if(isset($rules->size)){
            $size = $rules->size;

            // size min
            if(isset($size->min) && $size->min > $media->size)
                return ['16.0.1'];
            
            // size max
            if(isset($size->max) && $size->max < $media->size)
                return ['16.0.2'];
        }

        // mime
        if(isset($rules->mime)){
            $matched = false;
            foreach($rules->mime as $mime){
                if(fnmatch($mime, $media->mime)){
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
            $exts = explode('.', $media->name);
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
        if(isset($rules->image) && fnmatch('image/*', $media->mime)){
            $image = $rules->image;

            $file_width  = $media->width;
            $file_height = $media->height;

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

    static function file($value, $opts, $object, $field, $rules): ?array{
        $form = $object->form ?? null;
        if(!$form)
            return null;

        if(!is_array($value))
            return null;

        $media = (object)[
            'size'   => $value['size'],
            'mime'   => $value['type'],
            'name'   => $value['name'],
            'width'  => null,
            'height' => null
        ];

        if(fnmatch('image/*', $value['type'])){
            list($file_width, $file_height) = getimagesize($value['tmp_name']);
            $media->width = $file_width;
            $media->height = $file_height;
        }

        return self::validateMedia($media, $form);
    }

    static function form($value, $opts, $object, $field, $rules): ?array{
        if(isset(\Mim::$app->config->libUpload->forms->$value))
            return null;
        return ['15.0'];
    }

    static function upload($value, $options, $object, $field, $rules): ?array {
        if(is_null($value) || !$value)
            return null;

        $file_id = self::getFileId($value);
        if(!$file_id)
            return ['17.0'];

        $media = Media::getOne(['path'=>$file_id]);
        if(!$media)
            return ['17.0'];

        if($options === true)
            return null;

        if(self::validateMedia($media, $options))
            return ['17.1'];

        return null;
    }

    static function uploadList($value, $options, $object, $field, $rules): ?array{
        if(is_null($value) || !$value)
            return null;

        $value = (array)$value;
        if(!$value)
            return null;

        foreach($value as &$val){
            $val = self::getFileId($val);
            if(!$val)
                return ['18.0'];
        }

        $media = Media::get(['path'=>$value]);
        if(!$media)
            return ['18.0'];

        $media_paths = array_column($media, 'path');
        foreach($value as $val){
            if(!in_array($val, $media_paths))
                return ['18.0'];
        }

        if($options === true)
            return null;

        foreach($media as $med){
            if(self::validateMedia($med, $options))
                return ['18.1'];
        }

        return null;
    }
}