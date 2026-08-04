<?php
/**
 * News CPT helpers.
 *
 * Anything that reads the news post type lives here rather than inside a
 * layout template, so the slider, a future archive and a related-posts block
 * all ask the same question in the same way.
 *
 * @package KurtFreyAG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * How many posts the news slider shows.
 *
 * Nine fills exactly three pages of three, so the last page is never short.
 */
const KFA_NEWS_SLIDER_COUNT = 9;


/**
 * Latest published news posts, newest first.
 *
 * @param int   $count Number of posts to fetch.
 * @param array $args  Extra WP_Query arguments, merged over the defaults.
 *
 * @return WP_Query
 */
function kfa_get_news_query( int $count = KFA_NEWS_SLIDER_COUNT, array $args = array() ): WP_Query {

	$defaults = array(
		'post_type'              => 'news',
		'post_status'            => 'publish',
		'posts_per_page'         => max( 1, $count ),
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'ignore_sticky_posts'    => true,

		/*
		 * The slider never paginates and reads no terms, so both lookups are
		 * skipped. On a long news list that is two queries saved per page.
		 */
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	);

	$query_args = wp_parse_args( $args, $defaults );

	/**
	 * Filters the arguments used for every news query.
	 *
	 * @param array $query_args Query arguments.
	 * @param int   $count      Requested post count.
	 */
	$query_args = apply_filters( 'kfa_news_query_args', $query_args, $count );

	return new WP_Query( $query_args );
}


/**
 * Posts for the news slider.
 *
 * Separate from kfa_get_news_query() so the slider count can be changed
 * without touching every other news query on the site.
 *
 * @return WP_Query
 */
function kfa_get_news_slider_query(): WP_Query {

	/**
	 * Filters how many posts the news slider pulls in.
	 *
	 * @param int $count Post count.
	 */
	$count = (int) apply_filters( 'kfa_news_slider_count', KFA_NEWS_SLIDER_COUNT );

	return kfa_get_news_query( $count );
}