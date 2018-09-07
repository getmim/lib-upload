<?php
/**
 * Basic file upload keeper
 * @package lib-upload
 * @version 0.0.1
 */

namespace LibUpload\Keeper;

use Mim\Library\Fs;

class Local implements \LibUpload\Iface\Keeper
{

    private static $error;

    static function save(object $file): bool{
        $base = \Mim::$app->config->libUpload->base->local ?? 'media';
        if(substr($base,0,1) != '/')
            $base = realpath(BASEPATH . '/' . $base);

        if(!is_writable($base)){
            self::$error = 'Target dir is not writable';
            return false;
        }

        $target = $base . '/' . $file->target;

        $result = Fs::copy($file->source, $target);
        if($result)
            return true;
        self::$error = 'Unable to copy file upload';
        return false;
    }
    
    static function lastError(): ?string{
        return self::$error;
    }
}