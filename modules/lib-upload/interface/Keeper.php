<?php
/**
 * File upload keeper
 * @package lib-upload
 * @version 0.0.1
 */

namespace LibUpload\Iface;

interface Keeper
{
    static function save(object $file): bool;
    static function get(object $file): ?object;
    static function lastError(): ?string;
}