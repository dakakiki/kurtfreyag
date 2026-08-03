<?php
if ( ! defined('ABSPATH') ) exit;


/**
 * RGBA -> HEX
 */
function rgba_to_hex($rgba) {
    if (empty($rgba)) {
        return '';
    }

    preg_match('/rgba?\((\d+),\s*(\d+),\s*(\d+)/', $rgba, $matches);

    if (count($matches) < 4) {
        return '';
    }

    return sprintf(
        '#%02X%02X%02X',
        $matches[1],
        $matches[2],
        $matches[3]
    );
}


/**
 * HEX -> RGBA
 */
function hex_to_rgba($hex, $alpha = 1) {
    $hex = str_replace('#', '', $hex);

    if (strlen($hex) === 3) {
        $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
        $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
        $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
    } elseif (strlen($hex) === 6) {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    } else {
        return '';
    }

    return sprintf('rgba(%d, %d, %d, %s)', $r, $g, $b, $alpha);
}

/**
 * HEX -> RGB values
 */
function hex_to_rgb_values($hex) {
    $hex = str_replace('#', '', $hex);

    if (strlen($hex) === 3) {
        $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
        $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
        $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
    } elseif (strlen($hex) === 6) {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    } else {
        return '';
    }

    return "{$r}, {$g}, {$b}";
}