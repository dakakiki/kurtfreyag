<?php
/**
 * Reference CPT helpers.
 *
 * Everything that reads the referenz post type lives here rather than inside
 * the layout template, so the block, the AJAX handler and anything added
 * later all ask the same question in the same way.
 *
 * @package KurtFreyAG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * How many projects one page of the block shows.
 *
 * TEMPORARY: 2 while the load more control is being tested. Put this back to
 * 9 - the XD's three by three grid - before committing.
 */
const KFA_REF_PER_PAGE = 9;

/** Query string keys for the block's state. */
const KFA_REF_TERM_VAR = 'ref_group';
const KFA_REF_PAGE_VAR = 'ref_page';

/** AJAX action name. */
const KFA_REF_AJAX_ACTION = 'kfa_load_references';


/**
 * Post type and taxonomy, filterable so a rename stays a one line change.
 */
function kfa_ref_post_type(): string {

	return (string) apply_filters( 'kfa_reference_post_type', 'referenz' );
}

function kfa_ref_taxonomy(): string {

	return (string) apply_filters( 'kfa_reference_taxonomy', 'referenzgruppe' );
}


/* -------------------------------------------------------------------------
 * Queries
 * ---------------------------------------------------------------------- */

/**
 * Projects, newest first.
 *
 * @param int   $count Posts per page.
 * @param array $args  Extra WP_Query arguments, merged over the defaults.
 */
function kfa_get_reference_query( int $count = KFA_REF_PER_PAGE, array $args = array() ): WP_Query {

	$defaults = array(
		'post_type'           => kfa_ref_post_type(),
		'post_status'         => 'publish',
		'posts_per_page'      => max( 1, $count ),
		'orderby'             => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'ignore_sticky_posts' => true,

		/* The block needs the row count to decide on the load more control. */
		'no_found_rows'       => false,
	);

	$query_args = wp_parse_args( $args, $defaults );

	/**
	 * Filters the arguments used for every reference query.
	 */
	$query_args = apply_filters( 'kfa_reference_query_args', $query_args, $count );

	return new WP_Query( $query_args );
}


/**
 * Term filter as a tax_query fragment.
 */
function kfa_reference_term_args( int $term_id ): array {

	if ( ! $term_id ) {
		return array();
	}

	return array(
		'tax_query' => array(
			array(
				'taxonomy' => kfa_ref_taxonomy(),
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		),
	);
}


/**
 * Everything up to and including the given page.
 *
 * Cumulative for the server rendered list, so following the load more link
 * with JavaScript off adds projects rather than replacing them.
 */
function kfa_get_reference_initial_query( int $page = 1, int $term_id = 0 ): WP_Query {

	$page = max( 1, $page );

	return kfa_get_reference_query(
		KFA_REF_PER_PAGE * $page,
		array_merge(
			array( 'posts_per_page' => KFA_REF_PER_PAGE * $page ),
			kfa_reference_term_args( $term_id )
		)
	);
}


/**
 * A single page, used by the AJAX handler.
 */
function kfa_get_reference_page_query( int $page = 1, int $term_id = 0 ): WP_Query {

	return kfa_get_reference_query(
		KFA_REF_PER_PAGE,
		array_merge(
			array(
				'posts_per_page' => KFA_REF_PER_PAGE,
				'paged'          => max( 1, $page ),
			),
			kfa_reference_term_args( $term_id )
		)
	);
}


/* -------------------------------------------------------------------------
 * Filter state
 * ---------------------------------------------------------------------- */

/**
 * Groups that actually have published projects.
 *
 * @return WP_Term[]
 */
function kfa_get_reference_terms(): array {

	$terms = get_terms( array(
		'taxonomy'   => kfa_ref_taxonomy(),
		'hide_empty' => true,
	) );

	return is_wp_error( $terms ) ? array() : $terms;
}


/**
 * The group currently being filtered on, or 0 for all of them.
 */
function kfa_get_current_reference_term(): int {

	if ( empty( $_GET[ KFA_REF_TERM_VAR ] ) ) {
		return 0;
	}

	$slug = sanitize_title( wp_unslash( $_GET[ KFA_REF_TERM_VAR ] ) );
	$term = get_term_by( 'slug', $slug, kfa_ref_taxonomy() );

	return ( $term && ! is_wp_error( $term ) ) ? (int) $term->term_id : 0;
}


/**
 * How many pages deep the visitor is, 1 based.
 *
 * Its own variable rather than `paged`, because the block sits on an ordinary
 * page whose paged value belongs to WordPress.
 */
function kfa_get_current_reference_page(): int {

	$page = isset( $_GET[ KFA_REF_PAGE_VAR ] ) ? absint( $_GET[ KFA_REF_PAGE_VAR ] ) : 1;

	return max( 1, $page );
}


/* -------------------------------------------------------------------------
 * Rendering
 * ---------------------------------------------------------------------- */

/**
 * The project details, in the order the XD lists them.
 *
 * Empty fields are dropped rather than printed with a blank value.
 *
 * @return array<int, array{label: string, value: string, inline: bool}>
 */
function kfa_get_reference_details( int $post_id ): array {

	$fields = array(
		'ref_bauherr'          => array( __( 'Bauherr', 'KurtFreyAG' ), false ),
		'ref_totalunternehmer' => array( __( 'Totalunternehmer', 'KurtFreyAG' ), false ),
		'ref_architekt'        => array( __( 'Architekt', 'KurtFreyAG' ), false ),
		'ref_aufgabe'          => array( __( 'Aufgabe', 'KurtFreyAG' ), false ),

		/* XD keeps the completion date on the same line as its label. */
		'ref_fertigstellung'   => array( __( 'Fertigstellung', 'KurtFreyAG' ), true ),
	);

	$details = array();

	foreach ( $fields as $name => $config ) {

		$value = get_field( $name, $post_id );

		if ( ! $value ) {
			continue;
		}

		$details[] = array(
			'label'  => $config[0],
			'value'  => (string) $value,
			'inline' => $config[1],
		);
	}

	return $details;
}


/**
 * Render the cards of a query to a string.
 *
 * The block prints cards straight from the loop; this exists so the AJAX
 * handler produces the same markup from the same partial.
 */
function kfa_render_reference_cards( WP_Query $query ): string {

	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();

	while ( $query->have_posts() ) {
		$query->the_post();
		get_template_part( 'template_parts/reference/card' );
	}

	wp_reset_postdata();

	return (string) ob_get_clean();
}


/* -------------------------------------------------------------------------
 * Load more and filtering
 * ---------------------------------------------------------------------- */

/**
 * AJAX: return one page of cards, filtered by group.
 *
 * Open to logged out visitors as well - this is public content, and the nonce
 * is here to keep the endpoint from being used as a generic query runner.
 */
function kfa_ajax_load_references(): void {

	check_ajax_referer( KFA_REF_AJAX_ACTION, 'nonce' );

	$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
	$slug = isset( $_POST['term'] ) ? sanitize_title( wp_unslash( $_POST['term'] ) ) : '';

	$term_id = 0;

	if ( $slug ) {
		$term = get_term_by( 'slug', $slug, kfa_ref_taxonomy() );

		if ( $term && ! is_wp_error( $term ) ) {
			$term_id = (int) $term->term_id;
		}
	}

	$query = kfa_get_reference_page_query( $page, $term_id );

	wp_send_json_success( array(
		'html'     => kfa_render_reference_cards( $query ),
		'page'     => $page,
		'has_more' => $page < (int) $query->max_num_pages,
	) );
}
add_action( 'wp_ajax_' . KFA_REF_AJAX_ACTION, 'kfa_ajax_load_references' );
add_action( 'wp_ajax_nopriv_' . KFA_REF_AJAX_ACTION, 'kfa_ajax_load_references' );