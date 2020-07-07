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

    private static function setError(string $error){
        self::$error = $error;
        return null;
    }

    static function getId(string $file): ?string{
        $host = \Mim::$app->config->libUpload->base->host;
        $host_len = strlen($host);

        if(substr($file, 0, $host_len) != $host)
            return null;
        return substr($file, $host_len);
    }

    static function save(object $file): ?string{
        $config = &\Mim::$app->config->libUpload->base;

        $base = $config->local ?? 'media';
        if(substr($base,0,1) != '/')
            $base = realpath(BASEPATH . '/' . $base);

        if(!is_writable($base))
            return self::setError('Target dir is not writable');

        $target = $base . '/' . $file->target;

        $result = Fs::copy($file->source, $target);
        if(!$result)
            return self::setError('Unable to copy file upload');

        return $config->host . $file->target;
    }
    
    static function lastError(): ?string{
        return self::$error;
    }
}