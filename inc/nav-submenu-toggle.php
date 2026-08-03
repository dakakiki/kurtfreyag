<?php
/**
 * Submenu toggle button.
 *
 * The parent menu item stays an ordinary link that navigates to its page. The
 * submenu is opened only by the icon next to it, which is rendered here as a
 * real <button> so it is reachable by keyboard and carries its own state.
 *
 * Markup produced inside each parent <li>:
 *
 *   <a href="...">Leistungen</a>
 *   <button class="nav-wrap__toggle" aria-expanded="false" aria-controls="submenu-42">
 *   <ul class="sub-menu" id="submenu-42"> ... </ul>
 *
 * @package KurtFreyAG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Holds the item ID of the parent whose start_el() ran last.
 *
 * WordPress calls start_el() for the parent immediately before start_lvl() for
 * its children, so this is how the <ul> gets the id the button points at.
 */
final class KFA_Submenu_Toggle_State {

	public static $parent_id = 0;
}


/**
 * Menu locations that get the toggle. Filterable so the footer menu is left alone.
 */
function kfa_submenu_toggle_locations(): array {

	return apply_filters( 'kfa_submenu_toggle_locations', array( 'menu-main' ) );
}


/**
 * Append the toggle button after the link of any top level item with children.
 */
add_filter( 'walker_nav_menu_start_el', function ( $item_output, $item, $depth, $args ) {

	if ( (int) $depth !== 0 ) {
		return $item_output;
	}

	$location = is_object( $args ) && ! empty( $args->theme_location ) ? $args->theme_location : '';

	if ( ! in_array( $location, kfa_submenu_toggle_locations(), true ) ) {
		return $item_output;
	}

	if ( ! in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		return $item_output;
	}

	KFA_Submenu_Toggle_State::$parent_id = (int) $item->ID;

	$submenu_id = 'submenu-' . (int) $item->ID;

	/* translators: %s: parent menu item title. */
	$label = sprintf( __( 'Untermenü von %s öffnen', 'KurtFreyAG' ), wp_strip_all_tags( $item->title ) );

	$icon = THEME_URI . '/dist/images/ico_submenu.svg';

	$item_output .= sprintf(
		'<button type="button" class="nav-wrap__toggle" aria-expanded="false" aria-controls="%1$s" aria-label="%2$s">'
			. '<img class="nav-wrap__toggle-icon" src="%3$s" width="16" height="14" alt="" aria-hidden="true">'
		. '</button>',
		esc_attr( $submenu_id ),
		esc_attr( $label ),
		esc_url( $icon )
	);

	return $item_output;

}, 10, 4 );


/**
 * Give the submenu the id the button controls.
 */
add_filter( 'nav_menu_submenu_attributes', function ( $atts, $args, $depth ) {

	if ( (int) $depth !== 0 ) {
		return $atts;
	}

	$location = is_object( $args ) && ! empty( $args->theme_location ) ? $args->theme_location : '';

	if ( ! in_array( $location, kfa_submenu_toggle_locations(), true ) ) {
		return $atts;
	}

	if ( KFA_Submenu_Toggle_State::$parent_id ) {
		$atts['id'] = 'submenu-' . KFA_Submenu_Toggle_State::$parent_id;

		KFA_Submenu_Toggle_State::$parent_id = 0;
	}

	return $atts;

}, 10, 3 );