<?php
/**
 * News card.
 *
 * Expects to run inside a news loop, after the_post(), so it reads the current
 * post. Used by the slider today and by the archive and related-posts blocks
 * later, which is why it carries no slider specific markup.
 *
 * @param array $args {
 *     Optional. Passed through get_template_part().
 *
 *     @type string $size    Image size name. Default 'medium_large'.
 *     @type string $sizes   sizes attribute for the thumbnail.
 *     @type string $heading Heading tag for the title. Default 'h3'.
 *     @type string $class   Extra class on the card wrapper.
 * }
 *
 * @package KurtFreyAG
 */

$card_size    = $args['size'] ?? 'medium_large';
$card_sizes   = $args['sizes'] ?? '(max-width: 640px) 88vw, (max-width: 991px) 44vw, 310px';
$card_class   = $args['class'] ?? '';

/*
 * The slider sits under an h2, so h3 is the right default. An archive that
 * lists cards under an h1 can pass h2 instead.
 */
$card_heading = $args['heading'] ?? 'h3';
$card_heading = in_array( $card_heading, array( 'h2', 'h3', 'h4' ), true ) ? $card_heading : 'h3';
?>

<article class="news-card <?= esc_attr( $card_class ); ?>">

	<a class="news-card__link" href="<?php the_permalink(); ?>">

		<div class="news-card__media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( $card_size, array(
					'loading' => 'lazy',
					'sizes'   => $card_sizes,
				) ); ?>
			<?php endif; ?>
		</div>

		<div class="news-card__body">

			<time class="news-card__date" datetime="<?= esc_attr( get_the_date( 'c' ) ); ?>">
				<?= esc_html( get_the_date() ); ?>
			</time>

			<<?= $card_heading; ?> class="news-card__title"><?php the_title(); ?></<?= $card_heading; ?>>

		</div>

	</a>

</article>