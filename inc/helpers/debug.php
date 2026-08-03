<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * helper, print array
 * usage
 * dd_array($my_array);
 * dd_array($my_array, 'white', 'black');
 * dd_array($linkApply, 'white', 'black', true);
 * dd_array($my_array, '#0f0', '#111');
 * dd_array($my_array, 'white', 'black', true);
 */
if ( ! function_exists('dd_array') ) {
    function dd_array($input_array, $text_color = 'black', $bg_color = 'white', $die = false) {

        if (is_array($input_array) || is_object($input_array)) {

            echo '<pre style="
                color: ' . esc_attr($text_color) . ';
                background-color: ' . esc_attr($bg_color) . ';
                padding: 15px;
                border: 1px solid #ccc;
                font-size: 14px;
                line-height: 1.5;
                overflow: auto;
            ">';

            print_r($input_array);

            echo '</pre>';

            if ($die) {
                die();
            }
        }
    }
}


/**
 * wp_kses_post
 * esc_url
 * esc_html
 * esc_attr
 * esc_email
 */