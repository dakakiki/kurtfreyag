<?php
if ( ! defined('ABSPATH') ) exit;


/**
 * ACF layouts
 */

/**
 * Get Theme HEADER Options
 */
function getHeaderSettings()
{

  $header_o = theme_get_option('header_content');

  return $header_o;
}

/**
 * Get Theme FOOTER Options
 */
function getFooterSettings()
{

  $footer_o = theme_get_option('footer_content');

  return $footer_o;
}

/**
 * Get Theme GENERAL Options
 */
function getGeneralOptions()
{

  $general_o = theme_get_option('general_options');

  return $general_o;
}


/**
 * Get Theme SN Options
 */
function getSNOptions()
{

  $sn_o = theme_get_option('sn_content');

  return $sn_o;
}

/**
 * Get Theme SN Options
 */
function getContactContent()
{

  $contact_o = theme_get_option('general_contact_content');

  return $contact_o;
}



/**
 * If header layout is active
 */
function isPageHeroActive($contentID) {

    $res = 0;
  
    $layouts = get_field('layouts',$contentID);
  
    if ($layouts):
  
      foreach ($layouts as $l => $layout):
        
        if ($layout['acf_fc_layout'] == 'layout_page_hero'):
  
          $res = 1;
  
          break;
  
        endif;
  
      endforeach;
  
    endif;
  
    return $res;
}


/**
 * If any layout is active
 */
function isLayoutAnyActive($contentID) {

$res = 0;

$layouts = get_field('layouts',$contentID);

if ($layouts):

    foreach ($layouts as $l => $layout):
    
    if ($layout['acf_fc_layout'] != 'layout_hero'):

        $res = 1;

        break;

    endif;

    endforeach;

endif;

return $res;
}

/**
 * Render an ACF image array as responsive markup.
 *
 * Uses wp_get_attachment_image() so WordPress adds srcset, sizes and the
 * width/height attributes that keep the layout from shifting while loading.
 * SVG has no intermediate sizes, so it falls back to a plain <img>.
 *
 * @param array  $image ACF image field value (return format: array).
 * @param string $size  Registered image size.
 * @param array  $attrs Extra attributes, e.g. loading / sizes / fetchpriority.
 */
function theme_acf_image($image, $size = 'large', $attrs = array()) {

    if (empty($image) || empty($image['ID'])) {
        return '';
    }

    $mime = !empty($image['mime_type']) ? $image['mime_type'] : '';

    if ($mime === 'image/svg+xml') {

        $out = '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '"';

        if (!empty($image['width']) && !empty($image['height'])) {
            $out .= ' width="' . esc_attr($image['width']) . '" height="' . esc_attr($image['height']) . '"';
        }

        foreach ($attrs as $key => $value) {
            $out .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        return $out . '>';
    }

    return wp_get_attachment_image($image['ID'], $size, false, $attrs);
}