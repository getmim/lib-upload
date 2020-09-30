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

        $fvalues = [];
        foreach($values as $value)
            $fvalues[$value] = json_decode($value);

        return Formatter::formatMany('std-cover', $fvalues);
    }
}