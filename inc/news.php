<?php
/**
 * News CPT helpers.
 *
 * Anything that reads the news post type lives here rather than inside a
 * layout template, so the slider, the archive layout and the AJAX handler all
 * ask the same question in the same way.
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
 * How many posts one page of the news archive layout shows.
 *
 * The XD lists three rows of three before the load more control.
 */
const KFA_NEWS_ARCHIVE_PER_PAGE = 9;

/** Query string keys for the archive state. */
const KFA_NEWS_YEAR_VAR = 'news_year';
const KFA_NEWS_PAGE_VAR = 'news_page';

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
 * Year filter as a date_query fragment.
 */
function kfa_news_year_args( int $year ): array {

	return $year ? array( 'date_query' => array( array( 'year' => $year ) ) ) : array();
}


/**
 * Everything up to and including the given page.
 *
 * Used for the server rendered list. Cumulative rather than one page at a
 * time, so following the load more link with JavaScript off adds posts
 * instead of replacing them.
 *
 * @param int $page Page number, 1 based.
 * @param int $year Four digit year, or 0 for all years.
 */
function kfa_get_news_archive_initial_query( int $page = 1, int $year = 0 ): WP_Query {

	$page = max( 1, $page );

	$args = array_merge(
		array(
			'posts_per_page' => KFA_NEWS_ARCHIVE_PER_PAGE * $page,

			/* found_posts decides whether the load more control is shown. */
			'no_found_rows'  => false,
		),
		kfa_news_year_args( $year )
	);

	return kfa_get_news_query( KFA_NEWS_ARCHIVE_PER_PAGE * $page, $args );
}


/**
 * A single page of the archive.
 *
 * Used by the AJAX handler, which appends one page at a time rather than
 * re-rendering what is already on screen.
 */
function kfa_get_news_archive_query( int $page = 1, int $year = 0 ): WP_Query {

	$args = array_merge(
		array(
			'posts_per_page' => KFA_NEWS_ARCHIVE_PER_PAGE,
			'paged'          => max( 1, $page ),
			'no_found_rows'  => false,
		),
		kfa_news_year_args( $year )
	);

	return kfa_get_news_query( KFA_NEWS_ARCHIVE_PER_PAGE, $args );
}


/* -------------------------------------------------------------------------
 * Archive state
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
 * shareable and crawlable without new rewrite rules - and because the layout
 * can sit on any page.
 */
function kfa_get_current_news_year(): int {

	if ( empty( $_GET[ KFA_NEWS_YEAR_VAR ] ) ) {
		return 0;
	}

	$year = absint( $_GET[ KFA_NEWS_YEAR_VAR ] );

	return in_array( $year, kfa_get_news_years(), true ) ? $year : 0;
}


/**
 * How many pages deep the visitor is, 1 based.
 *
 * Its own variable rather than `paged`, because the layout lives on an
 * ordinary page whose paged value belongs to WordPress.
 */
function kfa_get_current_news_page(): int {

	$page = isset( $_GET[ KFA_NEWS_PAGE_VAR ] ) ? absint( $_GET[ KFA_NEWS_PAGE_VAR ] ) : 1;

	return max( 1, $page );
}


/**
 * Current URL with the archive state applied.
 *
 * Keeps any other query arguments and always points at the news anchor, so
 * following the link does not drop the visitor back at the top of the page.
 */
function kfa_news_archive_url( int $page = 1, int $year = 0 ): string {

	$base = remove_query_arg( array( KFA_NEWS_YEAR_VAR, KFA_NEWS_PAGE_VAR ) );

	$args = array();

	if ( $year ) {
		$args[ KFA_NEWS_YEAR_VAR ] = $year;
	}

	if ( $page > 1 ) {
		$args[ KFA_NEWS_PAGE_VAR ] = $page;
	}

	return $args ? add_query_arg( $args, $base ) : $base;
}


/**
 * Link for one year pill. Filtering always restarts at page one.
 *
 * The pills are buttons driven by AJAX, so nothing calls this today. Kept
 * because the URL format is still what the block reads on load, so this is
 * the one place that knows how to build it.
 */
function kfa_news_year_url( int $year = 0 ): string {

	return kfa_news_archive_url( 1, $year );
}


/** Link for the load more control. */
function kfa_news_page_url( int $page, int $year = 0 ): string {

	return kfa_news_archive_url( $page, $year );
}


/* -------------------------------------------------------------------------
 * Rendering
 * ---------------------------------------------------------------------- */

/**
 * Render the cards of a news query to a string.
 *
 * The layout prints cards straight from the loop; this exists so the AJAX
 * handler produces byte for byte the same markup from the same partial.
 */
function kfa_render_news_cards( WP_Query $query, array $card_args = array() ): string {

	if ( ! $query->have_posts() ) {
		return '';
	}

	$card_args = wp_parse_args( $card_args, array(
		/* fade-up must match the server rendered cards, or the CSS that hides
		   uninitialised items would leave the appended ones invisible. */
		'class'   => 'news-card--archive fade-up',
		'heading' => 'h3',
		'sizes'   => '(max-width: 640px) 88vw, (max-width: 991px) 46vw, 376px',
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

	/* Only years that exist are honoured, as on the server rendered list. */
	if ( $year && ! in_array( $year, kfa_get_news_years(), true ) ) {
		$year = 0;
	}

	$query = kfa_get_news_archive_query( $page, $year );

	wp_send_json_success( array(
		'html'     => kfa_render_news_cards( $query ),
		'page'     => $page,
		'has_more' => $page < (int) $query->max_num_pages,
	) );
}
add_action( 'wp_ajax_' . KFA_NEWS_AJAX_ACTION, 'kfa_ajax_load_news' );
add_action( 'wp_ajax_nopriv_' . KFA_NEWS_AJAX_ACTION, 'kfa_ajax_load_news' );


/*
 * The load more control carries its own AJAX URL, action and nonce in data
 * attributes, so there is no wp_localize_script here. That call had to land
 * after my_acf_enqueue_layout_assets() registered the handle, and tying the
 * feature to a hook priority in another file proved too easy to break.
 */


/* -------------------------------------------------------------------------
 * Archive page lookup
 *
 * The single view borrows its hero from whichever page hosts the archive
 * layout, so a post opened from the homepage slider looks the same as one
 * opened from the archive itself.
 * ---------------------------------------------------------------------- */

/**
 * ID of the page that hosts layout_news_archive.
 *
 * ACF stores the flexible field's layout names inside the `layouts` meta
 * value, so a LIKE match finds the page without loading every page's rows.
 */
function kfa_get_news_archive_page_id(): int {

	/**
	 * Short circuit the lookup, for a site that would rather name the page.
	 *
	 * @param int $page_id Page ID, or 0 to let the lookup run.
	 */
	$filtered = (int) apply_filters( 'kfa_news_archive_page_id', 0 );

	if ( $filtered ) {
		return $filtered;
	}

	$cached = get_transient( 'kfa_news_archive_page' );

	if ( false !== $cached ) {
		return (int) $cached;
	}

	$found = get_posts( array(
		'post_type'              => 'page',
		'post_status'            => 'publish',
		'posts_per_page'         => 1,
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'meta_query'             => array(
			array(
				'key'     => 'layouts',
				'value'   => 'layout_news_archive',
				'compare' => 'LIKE',
			),
		),
	) );

	$page_id = $found ? (int) $found[0] : 0;

	set_transient( 'kfa_news_archive_page', $page_id, DAY_IN_SECONDS );

	return $page_id;
}


/**
 * Forget the cached archive page whenever a page is saved.
 */
function kfa_flush_news_archive_page( $post_id ): void {

	if ( get_post_type( $post_id ) === 'page' ) {
		delete_transient( 'kfa_news_archive_page' );
	}
}
add_action( 'save_post', 'kfa_flush_news_archive_page' );
add_action( 'deleted_post', 'kfa_flush_news_archive_page' );


/**
 * Permalink of the archive page, empty when there is none.
 */
function kfa_news_archive_link(): string {

	$page_id = kfa_get_news_archive_page_id();

	return $page_id ? (string) get_permalink( $page_id ) : '';
}


/**
 * Render the archive page's hero row.
 *
 * Runs the real layout_page_hero template against the archive page's row, so
 * the single view inherits any change made there - image, title, height - and
 * there is no second copy of the hero markup to keep in step.
 */
function kfa_render_news_hero(): void {

	$page_id = kfa_get_news_archive_page_id();

	if ( ! $page_id || ! function_exists( 'have_rows' ) ) {
		return;
	}

	if ( ! have_rows( 'layouts', $page_id ) ) {
		return;
	}

	while ( have_rows( 'layouts', $page_id ) ) {

		the_row();

		if ( get_row_layout() !== 'layout_page_hero' ) {
			continue;
		}

		get_template_part( 'template_parts/layouts/layout_page_hero' );

		/* Only the first hero: a page could hold more than one. */
		break;
	}

	/*
	 * the_row() moves the global post pointer around; put it back so the rest
	 * of the single view still reads the post being viewed.
	 */
	wp_reset_postdata();
}


/**
 * Slick for the related posts on a single news post.
 *
 * my_acf_enqueue_layout_assets() only fires for ACF layouts on a page, and a
 * single post has none, so the slider assets are requested here instead.
 */
function kfa_single_news_assets(): void {

	if ( ! is_singular( 'news' ) ) {
		return;
	}

	$slick_css = theme_dist_path( 'css/slick.min.css' );
	$slick_js  = theme_dist_path( 'js/slick.min.js' );

	wp_enqueue_style(
		'slick',
		theme_dist_uri( 'css/slick.min.css' ),
		array(),
		file_exists( $slick_css ) ? filemtime( $slick_css ) : null
	);

	wp_enqueue_script(
		'slick',
		theme_dist_uri( 'js/slick.min.js' ),
		array( 'jquery' ),
		file_exists( $slick_js ) ? filemtime( $slick_js ) : null,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	[ $src, $ver ] = theme_pick_dist( 'js/layout_news_slider.min.js', 'js/layout_news_slider.js' );

	wp_enqueue_script(
		'layout_news_slider',
		$src,
		array( 'jquery', 'slick' ),
		$ver,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'kfa_single_news_assets' );