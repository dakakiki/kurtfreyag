<?php
/**
 * News CPT helpers.
 *
 * Anything that reads the news post type lives here rather than inside a
 * layout or archive template, so the slider, the archive and the AJAX handler
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
 * How many posts one page of the news archive shows.
 *
 * The XD lists three rows of three before the load more control.
 */
const KFA_NEWS_ARCHIVE_PER_PAGE = 9;

/** Query string key for the year filter. */
const KFA_NEWS_YEAR_VAR = 'news_year';

/** AJAX action name for the load more control. */
const KFA_NEWS_AJAX_ACTION = 'kfa_load_news';


/* -------------------------------------------------------------------------
 * Queries
 * ---------------------------------------------------------------------- */

/**
 * Latest published news posts, newest first.
 *
 * @param int   $count Number of posts to fetch.
 * @param array $args  Extra WP_Query arguments, merged over the defaults.
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


/**
 * One page of the news archive.
 *
 * Used by the AJAX handler. The archive itself runs on the main query, which
 * kfa_news_archive_pre_get_posts() shapes to match.
 *
 * @param int $page Page number, 1 based.
 * @param int $year Four digit year, or 0 for all years.
 */
function kfa_get_news_archive_query( int $page = 1, int $year = 0 ): WP_Query {

	$args = array(
		'posts_per_page' => KFA_NEWS_ARCHIVE_PER_PAGE,
		'paged'          => max( 1, $page ),

		/* Paged output needs the row count to know when to stop. */
		'no_found_rows'  => false,
	);

	if ( $year ) {
		$args['date_query'] = array( array( 'year' => $year ) );
	}

	return kfa_get_news_query( KFA_NEWS_ARCHIVE_PER_PAGE, $args );
}


/**
 * Shape the main query on the news archive.
 *
 * Doing this here rather than running a second query in the template keeps
 * max_num_pages, the paged variable and the next page URL correct, so the
 * archive still works with JavaScript switched off.
 */
function kfa_news_archive_pre_get_posts( WP_Query $query ): void {

	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! $query->is_post_type_archive( 'news' ) ) {
		return;
	}

	$query->set( 'posts_per_page', KFA_NEWS_ARCHIVE_PER_PAGE );

	$year = kfa_get_current_news_year();

	if ( $year ) {
		$query->set( 'date_query', array( array( 'year' => $year ) ) );
	}
}
add_action( 'pre_get_posts', 'kfa_news_archive_pre_get_posts' );


/* -------------------------------------------------------------------------
 * Year filter
 * ---------------------------------------------------------------------- */

/**
 * Years that actually have published news, newest first.
 *
 * @return int[]
 */
function kfa_get_news_years(): array {

	$cached = get_transient( 'kfa_news_years' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;

	$years = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT YEAR(post_date)
			 FROM {$wpdb->posts}
			 WHERE post_type = %s AND post_status = 'publish'
			 ORDER BY post_date DESC",
			'news'
		)
	);

	$years = array_map( 'intval', (array) $years );

	/* Cleared on publish, so a day is only the safety net. */
	set_transient( 'kfa_news_years', $years, DAY_IN_SECONDS );

	return $years;
}


/**
 * Drop the cached year list whenever a news post changes.
 */
function kfa_flush_news_years( $post_id ): void {

	if ( get_post_type( $post_id ) === 'news' ) {
		delete_transient( 'kfa_news_years' );
	}
}
add_action( 'save_post', 'kfa_flush_news_years' );
add_action( 'deleted_post', 'kfa_flush_news_years' );


/**
 * The year currently being filtered on, or 0 for all years.
 *
 * Read from the query string rather than a rewrite, so the filtered state is
 * shareable and crawlable without new rewrite rules.
 */
function kfa_get_current_news_year(): int {

	if ( empty( $_GET[ KFA_NEWS_YEAR_VAR ] ) ) {
		return 0;
	}

	$year = absint( $_GET[ KFA_NEWS_YEAR_VAR ] );

	return in_array( $year, kfa_get_news_years(), true ) ? $year : 0;
}


/**
 * Archive URL for one year, or for all years when $year is 0.
 */
function kfa_news_year_url( int $year = 0 ): string {

	$base = get_post_type_archive_link( 'news' );

	if ( ! $base ) {
		return home_url( '/' );
	}

	return $year ? add_query_arg( KFA_NEWS_YEAR_VAR, $year, $base ) : $base;
}


/* -------------------------------------------------------------------------
 * Rendering
 * ---------------------------------------------------------------------- */

/**
 * Render the cards of a news query to a string.
 *
 * The archive prints cards straight from the loop; this exists so the AJAX
 * handler produces byte for byte the same markup from the same partial.
 */
function kfa_render_news_cards( WP_Query $query, array $card_args = array() ): string {

	if ( ! $query->have_posts() ) {
		return '';
	}

	$card_args = wp_parse_args( $card_args, array(
		'class' => 'news-card--archive',
		'sizes' => '(max-width: 640px) 88vw, (max-width: 991px) 46vw, 376px',
	) );

	ob_start();

	while ( $query->have_posts() ) {
		$query->the_post();
		get_template_part( 'template_parts/news/card', null, $card_args );
	}

	wp_reset_postdata();

	return (string) ob_get_clean();
}


/* -------------------------------------------------------------------------
 * Load more
 * ---------------------------------------------------------------------- */

/**
 * AJAX: return the next page of archive cards.
 *
 * Responds to logged in and logged out visitors alike - this is public
 * content, and the nonce is here to keep the endpoint from being used as a
 * generic query runner, not to authenticate anyone.
 */
function kfa_ajax_load_news(): void {

	check_ajax_referer( KFA_NEWS_AJAX_ACTION, 'nonce' );

	$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
	$year = isset( $_POST['year'] ) ? absint( $_POST['year'] ) : 0;

	/* Only years that exist are honoured, as on the server rendered archive. */
	if ( $year && ! in_array( $year, kfa_get_news_years(), true ) ) {
		$year = 0;
	}

	$query = kfa_get_news_archive_query( $page, $year );

	$html = kfa_render_news_cards( $query );

	wp_send_json_success( array(
		'html'     => $html,
		'page'     => $page,
		'has_more' => $page < (int) $query->max_num_pages,
	) );
}
add_action( 'wp_ajax_' . KFA_NEWS_AJAX_ACTION, 'kfa_ajax_load_news' );
add_action( 'wp_ajax_nopriv_' . KFA_NEWS_AJAX_ACTION, 'kfa_ajax_load_news' );


/**
 * Load the archive script only where it is used.
 */
function kfa_news_archive_assets(): void {

	if ( ! is_post_type_archive( 'news' ) ) {
		return;
	}

	global $wp_query;

	[ $src, $ver ] = theme_pick_dist( 'js/news.min.js', 'js/news.js' );

	wp_enqueue_script(
		'kfa-news',
		$src,
		array(),
		$ver,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_localize_script( 'kfa-news', 'kfaNews', array(
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'action'   => KFA_NEWS_AJAX_ACTION,
		'nonce'    => wp_create_nonce( KFA_NEWS_AJAX_ACTION ),
		'year'     => kfa_get_current_news_year(),
		'page'     => max( 1, (int) get_query_var( 'paged' ) ),
		'maxPages' => (int) $wp_query->max_num_pages,
		'i18n'     => array(
			'loading' => __( 'Wird geladen …', 'KurtFreyAG' ),
			'more'    => __( 'Mehr laden', 'KurtFreyAG' ),
			'error'   => __( 'Laden fehlgeschlagen. Bitte erneut versuchen.', 'KurtFreyAG' ),
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'kfa_news_archive_assets' );