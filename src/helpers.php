<?php

use Illuminate\Support\Str;

if (!function_exists('isJson')) {
    function isJson($string, $associative = null): bool
    {
        if (!Str::startsWith($string, ['{', '['])) return false;

        $data = json_decode($string, $associative);

        if (!$associative && is_object($data) || $associative && is_array($data)) return true;

        return json_last_error() == JSON_ERROR_NONE;
    }
}