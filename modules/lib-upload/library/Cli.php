<?php
/**
 * Cli
 * @package lib-upload
 * @version 0.6.2
 */

namespace LibUpload\Library;

use Cli\Library\Bash;

class Cli
{
    static function local(array $config, bool $value, array $app_config){
        if(!$value)
            return null;

        $med_base = Bash::ask(['space' => 4, 'text' => 'Local path ( relative to BASEPATH / absolute )', 'default' => 'media']);

        $host_schema = $app_config['secure'] ? 'https' : 'http';
        $app_host    = $app_config['host'];
        $d_host = $host_schema . '://' . $app_host . '/' . (substr($med_base,0,1) == '/' ? '' : 'media/');

        $med_host = Bash::ask(['space' => 4, 'text' => 'Media url prefix', 'default' => $d_host]);
        
        return [
            'local' => $med_base,
            'host'  => $med_host
        ];
    }
}