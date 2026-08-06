<?php
/**
 * Reference card.
 *
 * Expects to run inside a referenz loop, after the_post().
 *
 * XD: the picture fills the card with the project name over its lower half;
 * on hover the card turns solid blue and the ACF details take its place.
 *
 * @package KurtFreyAG
 */

$ref_details = kfa_get_reference_details( get_the_ID() );
?>

<article class="reference-card" data-reference-card>

	<div class="reference-card__media">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium_large', array(
				'loading' => 'lazy',
				'alt'     => get_the_title(),
				'sizes'   => '(max-width: 640px) 88vw, (max-width: 991px) 46vw, 376px',
			) ); ?>
		<?php endif; ?>
	</div>

	<h3 class="reference-card__title"><?php the_title(); ?></h3>

	<?php if ( ! empty( $ref_details ) ) : ?>

		<?php
		/*
		 * A button rather than a plain overlay: the details are the only thing
		 * behind an interaction here, so they need a control that works with a
		 * keyboard and a tap, not only with a mouse pointer.
		 */
		?>
		<button
			type="button"
			class="reference-card__toggle"
			data-reference-toggle
			aria-expanded="false"
			aria-label="<?= esc_attr( sprintf(
				/* translators: %s: project title. */
				__( 'Details zu %s anzeigen', 'KurtFreyAG' ),
				wp_strip_all_tags( get_the_title() )
			) ); ?>"
		></button>

		<div class="reference-card__details">

			<dl class="reference-card__list">

				<?php foreach ( $ref_details as $detail ) : ?>
					<div class="reference-card__row<?= $detail['inline'] ? ' is-inline' : ''; ?>">
						<dt><?= esc_html( $detail['label'] ); ?>:</dt>
						<dd><?= nl2br( esc_html( $detail['value'] ) ); ?></dd>
					</div>
				<?php endforeach; ?>

			</dl>

		</div>

	<?php endif; ?>

</article>