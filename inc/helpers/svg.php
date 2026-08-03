<?php
if ( ! defined('ABSPATH') ) exit;


/**
 * SVG unique IDs
 */
function inline_svg_with_unique_ids($url, $prefix) {

    // Convert URL to local path
    $upload_dir = wp_get_upload_dir();
    $baseurl    = $upload_dir['baseurl'];
    $basedir    = $upload_dir['basedir'];

    // If SVG is in uploads
    if (strpos($url, $baseurl) === 0) {
        $file_path = str_replace($baseurl, $basedir, $url);
    } else {
        // If SVG is inside theme
        $file_path = str_replace(
            get_template_directory_uri(),
            get_template_directory(),
            $url
        );
    }

    if (!file_exists($file_path)) {
        return '';
    }

    $svg = file_get_contents($file_path);
    if (!$svg) return '';

    // Remove XML declaration
    $svg = preg_replace('/<\?xml.*?\?>\s*/', '', $svg);

    // Collect all ids
    if (preg_match_all('/\sid="([^"]+)"/', $svg, $m)) {
        $ids = array_unique($m[1]);

        foreach ($ids as $id) {
            $new = $prefix . '-' . $id;

            $svg = str_replace('id="'.$id.'"', 'id="'.$new.'"', $svg);
            $svg = str_replace('url(#'.$id.')', 'url(#'.$new.')', $svg);
            $svg = str_replace('href="#'.$id.'"', 'href="#'.$new.'"', $svg);
            $svg = str_replace('xlink:href="#'.$id.'"', 'xlink:href="#'.$new.'"', $svg);
        }
    }

    return $svg;
}


add_filter('upload_mimes', function ($mimes) {
  if (current_user_can('manage_options')) {
    $mimes['svg'] = 'image/svg+xml';
  }
  return $mimes;
});

add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
  if (!current_user_can('manage_options')) return $data;

  $filetype = wp_check_filetype($filename, $mimes);
  if ($filetype['ext'] === 'svg') {
    $data['ext']  = 'svg';
    $data['type'] = 'image/svg+xml';
  }
  return $data;
}, 10, 4);

add_filter('wp_prepare_attachment_for_js', function ($response, $attachment, $meta) {
  if ($response['mime'] === 'image/svg+xml') {
    $response['sizes'] = [
      'full' => [
        'url' => $response['url'],
        'width' => 0,
        'height' => 0,
        'orientation' => 'portrait',
      ]
    ];
  }
  return $response;
}, 10, 3);