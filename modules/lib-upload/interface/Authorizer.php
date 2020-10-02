<?php
/**
 * Authorizer
 * @package lib-upload
 * @version 0.7.1
 */

namespace LibUpload\Iface;


interface Authorizer
{
    static function getAuthId(): ?int;
}