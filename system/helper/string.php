<?php
/**
 * Created by PhpStorm.
 * User: TA-MEDIA
 * Date: 4/17/2018
 * Time: 3:25 PM
 */

require_once(DIR_SYSTEM . '/helper/library/str.php');

if (!function_exists('str_slug')) {
    function str_slug($title, $separator = '-')
    {
        return Str::slug($title, $separator);
    }
}

if (!function_exists('str_ascii')) {
    function str_ascii($title)
    {
        return Str::ascii($title);
    }
}