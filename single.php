<?php
/**
 * Single post dispatcher.
 *
 * Every single view routes through here and is rendered by a per post type
 * part in template_parts/single/. Adding a type means adding one file there,
 * with no change to this template.
 *
 *   news      -> template_parts/single/news.php
 *   referenz  -> template_parts/single/referenz.php
 *   anything else, or a missing part -> template_parts/single/default.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package KurtFreyAG
 * @subpackage KurtFreyAG
 * @since 1.0.0
 */

get_header();
?>

<main id="main">

	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>

		<?php
		$single_type = sanitize_key( (string) get_post_type() );
		$single_part = $single_type ? locate_template( "template_parts/single/{$single_type}.php" ) : '';

		if ( $single_part ) {
			load_template( $single_part, false );
		} else {

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && $single_type ) {
				trigger_error( "Missing single template part: template_parts/single/{$single_type}.php", E_USER_WARNING );
			}

			get_template_part( 'template_parts/single/default' );
		}
		?>

	<?php endwhile; ?>

	<?php wp_reset_postdata(); ?>

</main>

<?php get_footer(); ?>