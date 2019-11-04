<?php
/**
 * File upload keeper
 * @package lib-upload
 * @version 0.0.1
 */

namespace LibUpload\Iface;

interface Keeper
{
    static function getId(string $file): ?string;
    static function lastError(): ?string;
    static function save(object $file): ?string;
}