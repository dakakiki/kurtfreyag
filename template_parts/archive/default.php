<?php
/**
 * Default archive part.
 *
 * Safety net for post types that have no dedicated part yet. Deliberately
 * plain: it is a fallback, not a design.
 *
 * @package KurtFreyAG
 */
?>

<main id="main" class="archive-default">

	<div class="spacer"></div>

	<div class="archive-default__container">

		<h1 class="archive-default__title"><?php post_type_archive_title(); ?></h1>

		<?php if ( have_posts() ) : ?>

			<ul class="archive-default__list">

				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
				<?php endwhile; ?>

			</ul>

			<?php the_posts_pagination(); ?>

		<?php else : ?>

			<p><?php esc_html_e( 'Keine Einträge gefunden.', 'KurtFreyAG' ); ?></p>

		<?php endif; ?>

	</div>

</main>