<?php
if ( ! defined('ABSPATH') ) exit;


add_action('wp_head', function () {

    // Lato - normal
    $lato_normal = get_stylesheet_directory_uri() . '/assets/fonts/Lato/Lato-Regular.woff2';

    echo '<link rel="preload"
            href="' . esc_url($lato_normal) . '"
            as="font"
            type="font/woff2"
            crossorigin>' . "\n";

    // Lato - Bold
    $lato_bold = get_stylesheet_directory_uri() . '/assets/fonts/Lato/Lato-Bold.woff2';

    echo '<link rel="preload"
            href="' . esc_url($lato_bold) . '"
            as="font"
            type="font/woff2"
            crossorigin>' . "\n";


}, 1);