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
    static function stdCover(array $values, string $field, array $object, object $format, $options){
        if(!$values)
            return [];

        foreach($values as &$value)
            $value = json_decode($value);
        unset($value);

        return Formatter::formatMany('std-cover', $values);
    }
}