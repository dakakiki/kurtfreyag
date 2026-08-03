<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * Slug from the string
 */
function slugify_string($string)
{
    // Convert to lowercase (UTF-8 safe)
    $string = mb_strtolower($string, 'UTF-8');

    // Replace special characters (č, ć, š, ž, đ...)
    $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);

    // Replace non-alphanumeric characters with underscore
    $string = preg_replace('/[^a-z0-9]+/', '_', $string);

    // Trim underscores from start and end
    $string = trim($string, '_');

    return $string;
}