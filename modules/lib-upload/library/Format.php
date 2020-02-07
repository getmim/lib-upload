<?php
/**
 * Format
 * @package lib-upload
 * @version 0.1.0
 */

namespace LibUpload\Library;

use LibFormatter\Library\Formatter;

class Format
{
    static function stdCover($value, string $field, object $object, object $format, $options){
        if(is_null($value))
            return $value;

        $value = json_decode($value);
        if(!$value)
            return null;
        return Formatter::format('std-cover', $value);
    }
}