<?php
if ( ! defined('ABSPATH') ) exit;

function get_featured_image_data($post_id, $size = 'medium') {
    $thumbnail_id = get_post_thumbnail_id($post_id);

    if (!$thumbnail_id) return null;

    $image = wp_get_attachment_image_src($thumbnail_id, $size);

    return [
        'url'    => $image[0],
        'width'  => $image[1],
        'height' => $image[2],
        'alt'    => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true),
    ];
}