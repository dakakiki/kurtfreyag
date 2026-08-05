<?php
/**
 * Default single part.
 *
 * Safety net for post types with no dedicated part yet. The old single.php
 * included this file but it was never in the theme, so any type other than
 * news produced a fatal include warning.
 *
 * @package KurtFreyAG
 */
?>

<article <?php post_class( 'single-default' ); ?> id="post-<?php the_ID(); ?>">

	<div class="spacer"></div>

	<div class="single-default__container">

		<h1 class="single-default__title"><?php the_title(); ?></h1>

		<div class="single-default__content">
			<?php the_content(); ?>
		</div>

	</div>

</article>