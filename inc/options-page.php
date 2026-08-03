<?php
/**
 * Theme Settings options page.
 *
 * The Theme Settings field group (group_686bd3cce7d2d) is bound to
 * options_page == theme-settings, but nothing registered that page, so every
 * get_field(..., 'option') call returned null. This registers it.
 *
 * @package KurtFreyAG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', function () {

	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page( array(
		'page_title'  => __( 'Theme Settings', 'KurtFreyAG' ),
		'menu_title'  => __( 'Theme Settings', 'KurtFreyAG' ),
		'menu_slug'   => 'theme-settings',
		'capability'  => 'manage_options',
		'position'    => 59,
		'icon_url'    => 'dashicons-admin-generic',
		'redirect'    => false,
		'autoload'    => true,
	) );
} );


/**
 * Turn a raw phone string into a tel: href.
 *
 * "062 839 90 60" -> "tel:+41628399060" when a country code is configured,
 * otherwise it keeps whatever digits the editor entered.
 */
function kfa_tel_href( string $phone, string $country_prefix = '+41' ): string {

	$digits = preg_replace( '/[^0-9\+]/', '', $phone );

	if ( $digits === '' ) {
		return '';
	}

	if ( strpos( $digits, '+' ) !== 0 && strpos( $digits, '0' ) === 0 ) {
		$digits = $country_prefix . substr( $digits, 1 );
	}

	return 'tel:' . $digits;
}


/**
 * Layout slugs present on the current page, safe to call before ACF loads.
 *
 * Used by footer.php to decide between the full and the compact footer.
 */
function kfa_current_layouts(): array {

	if ( ! function_exists( 'my_acf_detect_layouts_on_page' ) ) {
		return array();
	}

	return my_acf_detect_layouts_on_page();
}