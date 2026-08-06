<?php

/**
 * KurtFreyAG
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @link https://codex.wordpress.org/Child_Themes
 *
 * For more information on hooks, actions, and filters,
 * {@link https://codex.wordpress.org/Plugin_API}
 *
 * @package KurtFreyAG
 * @subpackage KurtFreyAG
 * @since KurtFreyAG
 */


define( 'THEME_VERSION', wp_get_theme()->get('Version') );
define( 'THEME_PATH', get_template_directory() );
define( 'THEME_URI', get_template_directory_uri() );


// ============================================================
// ASSETS (dist-based, cache-busted, min preferred)
// ============================================================

/**
 * Return filemtime version for a theme-relative file path (null if missing).
 */
function theme_asset_ver(string $path_rel): ?int {
    $path_abs = get_theme_file_path(ltrim($path_rel, '/'));
    return file_exists($path_abs) ? filemtime($path_abs) : null;
}

/**
 * Dist helpers
 */
function theme_dist_uri(string $rel): string {
    return get_stylesheet_directory_uri() . '/dist/' . ltrim($rel, '/');
}
function theme_dist_path(string $rel): string {
    return get_stylesheet_directory() . '/dist/' . ltrim($rel, '/');
}

/**
 * Pick minified if exists, otherwise fallback.
 * Returns [uri, ver]
 */
function theme_pick_dist(string $minRel, string $rel): array {
    $minPath = theme_dist_path($minRel);
    if (file_exists($minPath)) {
        return [theme_dist_uri($minRel), filemtime($minPath)];
    }

    $path = theme_dist_path($rel);
    return [theme_dist_uri($rel), file_exists($path) ? filemtime($path) : null];
}

/**
 * Frontend guard.
 */
function theme_is_frontend(): bool {
    return !is_admin() && !wp_doing_ajax() && !wp_is_json_request();
}


// ============================================================
// 0) Dequeue Gutenberg CSS (frontend)
// ============================================================
add_action('wp_enqueue_scripts', function () {
    if (!theme_is_frontend()) return;

    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
    wp_dequeue_style('classic-theme-styles');
}, 100);


// ============================================================
// 1) Main enqueue (CSS + base JS)
// ============================================================
add_action('wp_enqueue_scripts', 'my_theme_enqueue_assets', 20);

function my_theme_enqueue_assets() {

    if ( is_admin() ) {
        return;
    }

    /* ---------- CSS (dist only) ---------- */
    $css_rel  = 'css/style.min.css';
    $css_path = theme_dist_path( $css_rel );

    wp_enqueue_style(
        'theme-style',
        theme_dist_uri( $css_rel ),
        [],
        file_exists( $css_path ) ? filemtime( $css_path ) : null
    );

    /* ---------- JS ---------- */

    // Ensure jQuery is loaded
    wp_enqueue_script('jquery');

    // Defer jQuery (WordPress 6.3+)
    if ( function_exists( 'wp_script_add_data' ) ) {
        wp_script_add_data( 'jquery-core', 'strategy', 'defer' );
        wp_script_add_data( 'jquery-migrate', 'strategy', 'defer' );
    }

    /* ---------- custom.min.js (dist only) ---------- */
    $custom_rel  = 'js/custom.min.js';
    $custom_path = theme_dist_path( $custom_rel );

    wp_enqueue_script(
        'theme-custom',
        theme_dist_uri( $custom_rel ),
        [ 'jquery' ],
        file_exists( $custom_path ) ? filemtime( $custom_path ) : null,
        [
            'in_footer' => true,
            'strategy'  => 'defer',
        ]
    );

    /* ---------- gsap.min.js (dist only) ---------- */
    $gsap_rel  = 'js/gsap.min.js';
    $gsap_path = theme_dist_path( $gsap_rel );

    wp_enqueue_script(
        'theme-gsap',
        theme_dist_uri( $gsap_rel ),
        [],
        file_exists( $gsap_path ) ? filemtime( $gsap_path ) : null,
        [
            'in_footer' => true,
            'strategy'  => 'defer',
        ]
    );

    /* ---------- Localize ---------- */
    wp_localize_script( 'theme-custom', 'the_ajax_script', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
    ]);
}



// ============================================================
// 2) Detect layouts on current page
// ============================================================
function my_acf_detect_layouts_on_page( $post_id = null ): array {
    if ( ! function_exists( 'get_field' ) ) {
        return [];
    }

    if ( null === $post_id ) {
        $post_id = get_queried_object_id();
    }

    if ( empty( $post_id ) ) {
        return [];
    }

    $flex_field = apply_filters( 'my_acf_flex_field', 'layouts' );
    $rows       = get_field( $flex_field, $post_id, false );

    if ( empty( $rows ) || ! is_array( $rows ) ) {
        return [];
    }

    $layouts = [];

    foreach ( $rows as $row ) {
        if ( ! empty( $row['acf_fc_layout'] ) ) {
            $layouts[] = $row['acf_fc_layout'];
        }
    }

    return array_values( array_unique( $layouts ) );
}


// ====================================================================
// 3) Load JS depending on the ACF layout and include Slick JS if needed
// ====================================================================
add_action( 'wp_enqueue_scripts', 'my_acf_enqueue_layout_assets', 25 );

function my_acf_enqueue_layout_assets() {

    if ( is_admin() ) {
        return;
    }

    /*
     * Map ACF layouts to their JS files
     */
    $layout_scripts = [
        'layout_news_slider' => [
            'min'         => 'js/layout_news_slider.min.js',
            'file'        => 'js/layout_news_slider.js',
            'deps'        => [ 'jquery' ],
            'needs_slick' => true,
        ],
        'layout_news_archive' => [
            'min'         => 'js/layout_news_archive.min.js',
            'file'        => 'js/layout_news_archive.js',
            'deps'        => [ 'jquery' ],
            'needs_slick' => false,
        ],
        'layout_areas' => [
            'min'         => 'js/layout_areas.min.js',
            'file'        => 'js/layout_areas.js',
            'deps'        => [ 'jquery' ],
            'needs_slick' => false,
        ],
    ];

    /*
     * Detect layouts present on the current page
     */
    $present_layouts = my_acf_detect_layouts_on_page();

    if ( empty( $present_layouts ) ) {
        return;
    }

    /*
     * Slick JS path only
     * CSS is already included in main compiled stylesheet
     */
    $slick_css_rel = 'css/slick.min.css';
    $slick_js_rel = 'js/slick.min.js';

    $need_slick = false;
    $to_enqueue = [];

    /*
     * Match detected layouts with registered layout scripts
     */
    foreach ( $layout_scripts as $layout_slug => $cfg ) {
        if ( ! in_array( $layout_slug, $present_layouts, true ) ) {
            continue;
        }

        $to_enqueue[ $layout_slug ] = $cfg;

        if ( ! empty( $cfg['needs_slick'] ) ) {
            $need_slick = true;
        }
    }

    if ( empty( $to_enqueue ) ) {
        return;
    }

    /*
     * Enqueue Slick JS first if any matched layout needs it
     */
    if ( $need_slick ) {
        $slick_css_path = theme_dist_path( $slick_css_rel );
        $slick_js_path = theme_dist_path( $slick_js_rel );

        wp_enqueue_style(
            'slick',
            theme_dist_uri( $slick_css_rel ),
            [],
            file_exists( $slick_css_path ) ? filemtime( $slick_css_path ) : null
        );

        wp_enqueue_script(
            'slick',
            theme_dist_uri( $slick_js_rel ),
            [ 'jquery' ],
            file_exists( $slick_js_path ) ? filemtime( $slick_js_path ) : null,
            [
                'in_footer' => true,
                'strategy'  => 'defer',
            ]
        );
    }

    /*
     * Enqueue matched layout scripts
     */
    foreach ( $to_enqueue as $layout_slug => $cfg ) {
        [ $src, $ver ] = theme_pick_dist( $cfg['min'], $cfg['file'] );

        $deps = $cfg['deps'] ?? [ 'jquery' ];

        if ( ! empty( $cfg['needs_slick'] ) ) {
            $deps[] = 'slick';
            $deps   = array_values( array_unique( $deps ) );
        }

        wp_enqueue_script(
            $layout_slug,
            $src,
            $deps,
            $ver,
            [
                'in_footer' => true,
                'strategy'  => 'defer',
            ]
        );

        // Expose theme URI for layout_documents (needed for PDF.js worker)
        // if ( $layout_slug === 'layout_documents' ) {
        //     wp_add_inline_script(
        //         'layout_documents',
        //         'window.__themeUri = ' . json_encode( get_template_directory_uri() ) . ';',
        //         'before'
        //     );
        // }
    }
}


/**
 * register menu
 */
function register_my_menus() {
    register_nav_menus(
      array(
        'menu-main' => __( 'Menu - Main' ),
        'menu-footer' => __( 'Menu - Footer' )
      )
    );
  }
  add_action( 'init', 'register_my_menus' );

/**
*	Add Feature IMG to posts
*/
function mytheme_post_thumbnails() {
    add_theme_support( 'post-thumbnails' );
}
add_action( 'after_setup_theme', 'mytheme_post_thumbnails' );


/**
*	Core theme supports
*
*	title-tag lets WordPress own the <title> element. Yoast overrides it with
*	its own value, but this keeps a valid title in place if the plugin is ever
*	deactivated.
*/
function mytheme_setup_supports() {

    add_theme_support( 'title-tag' );

    add_theme_support( 'html5', array(
        'search-form',
        'gallery',
        'caption',
        'style',
        'script',
    ) );
}
add_action( 'after_setup_theme', 'mytheme_setup_supports' );


add_filter('excerpt_more', function() {
    return '';
});


require_once THEME_PATH . '/inc/helpers/wp_editor.php';
require_once THEME_PATH . '/inc/helpers/fonts.php';
require_once THEME_PATH . '/inc/helpers/svg.php';
require_once THEME_PATH . '/inc/helpers/slugify.php';
require_once THEME_PATH . '/inc/helpers/acf.php';
require_once THEME_PATH . '/inc/helpers/colors.php';
require_once THEME_PATH . '/inc/helpers/date.php';
require_once THEME_PATH . '/inc/helpers/featured_image.php';
require_once THEME_PATH . '/inc/helpers/debug.php';

require_once THEME_PATH . '/inc/options-page.php';
require_once THEME_PATH . '/inc/nav-submenu-toggle.php';
require_once THEME_PATH . '/inc/news.php';