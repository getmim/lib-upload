<?php
/**
 * Form
 * @package lib-upload
 * @version 0.1.0
 */

namespace LibUpload\Library;

class Form
{
    static function combine(object &$object, string $field): void{
        $object->$field = (object)[
            'url'   => '',
            'label' => '',
        ];

        if(isset($object->{'cover-url'})){
            $object->$field->url = $object->{'cover-url'};
            unset($object->{'cover-url'});
        }

        if(isset($object->{'cover-label'})){
            $object->$field->label = $object->{'cover-label'};
            unset($object->{'cover-label'});
        }

        $object->$field = json_encode($object->$field);
    }

    static function parse(object &$object, string $field): void{
        $prop = $object->$field;
        $deff = '{"url":"","label":""}';
        if(!$prop)
            $prop = $deff;
        $prop = json_decode($prop);

        $object->{'cover-url'}   = $prop->url ?? '';
        $object->{'cover-label'} = $prop->label ?? '';
    }
}