<?php
/**
 * Archive dispatcher.
 *
 * Every archive routes through here and is rendered by a per post type part
 * in template_parts/archive/. Adding an archive means adding one file there,
 * with no change to this template.
 *
 *   news      -> template_parts/archive/news.php
 *   referenz  -> template_parts/archive/referenz.php
 *   anything else, or a missing part -> template_parts/archive/default.php
 *
 * @package KurtFreyAG
 */

get_header();

/*
 * get_post_type() reads the first post in the loop, which is empty on an
 * archive with no results, so the queried object is the reliable source.
 */
$archive_object = get_queried_object();

$archive_type = ( $archive_object instanceof WP_Post_Type )
	? $archive_object->name
	: (string) get_query_var( 'post_type' );

$archive_type = sanitize_key( is_array( $archive_type ) ? reset( $archive_type ) : $archive_type );

$archive_part = $archive_type
	? locate_template( "template_parts/archive/{$archive_type}.php" )
	: '';

if ( $archive_part ) {
	load_template( $archive_part, false );
} else {

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && $archive_type ) {
		trigger_error( "Missing archive template part: template_parts/archive/{$archive_type}.php", E_USER_WARNING );
	}

	get_template_part( 'template_parts/archive/default' );
}

get_footer();