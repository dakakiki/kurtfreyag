<?php
/**
 * Layout: layout_split_content
 *
 * XD reference - two related sections use this pattern:
 *
 *   Homepage "Wir - die Kurt Frey AG"
 *     image  474 x 527 at x140, 3px radius
 *     card   754 x 677 at x546, #00529E, 60px radius
 *     title  Lato Bold 53 white, text Lato Regular 16 white
 *     button pill 136 x 48, #EDF6FC, Lato Bold 16 #00529E
 *
 *   Über uns "Gemeinsam stark" and siblings
 *     card   573 wide, image 768 wide, alternating sides
 *     image  60px radius on the container edge, square on the inside
 *
 * Both are the same idea: a blue card with the copy, and an image that
 * overlaps it from one side. image_position picks the side and
 * styles.layout_style picks the proportions.
 */

$split_row      = get_row_index();
$split_content  = get_sub_field( 'content' );
$split_image_gr = get_sub_field( 'image_content' );
$split_styles   = get_sub_field( 'styles' );
$split_anchor   = sanitize_title( (string) get_sub_field( 'anchor' ) );

$split_image    = $split_image_gr['image'] ?? null;
$split_position = $split_image_gr['image_position'] ?? '';

/*
 * The ACF choice values are the labels themselves, so they are matched rather
 * than used directly - a class name should not depend on editorial wording.
 */
$split_side = ( stripos( (string) $split_position, 'right' ) !== false )
	? 'img-right'
	: 'img-left';

$split_style = ! empty( $split_styles['layout_style'] ) ? $split_styles['layout_style'] : 'l-split-5050';
$split_style = in_array( $split_style, array( 'l-split-5050', 'l-split-3070' ), true ) ? $split_style : 'l-split-5050';

$split_title   = $split_content['title'] ?? '';
$split_text    = $split_content['text'] ?? '';
$split_buttons = $split_content['buttons'] ?? array();

/* Nothing to show: skip rather than print an empty blue card. */
if ( ! $split_title && ! $split_text && empty( $split_image ) ) {
	return;
}
?>

<section
	<?php if ( $split_anchor ) : ?>id="<?= esc_attr( $split_anchor ); ?>"<?php endif; ?>
	class="layout-split-content <?= esc_attr( $split_style ); ?> <?= esc_attr( $split_side ); ?>"
>

	<div class="split-content" id="lyt-<?= (int) $split_row; ?>">

		<div class="split-content__container">

			<div class="split-content__grid">

				<?php if ( ! empty( $split_image ) ) : ?>
					<div class="split-content__media fade-up">
						<?= theme_acf_image( $split_image, 'large', array(
							'loading' => 'lazy',
							'sizes'   => '(max-width: 991px) 100vw, 50vw',
						) ); ?>
					</div>
				<?php endif; ?>

				<div class="split-content__body group-fade-up">

					<?php if ( $split_title ) : ?>
						<h2 class="split-content__title fade-up"><?= wp_kses_post( $split_title ); ?></h2>
					<?php endif; ?>

					<?php if ( $split_text ) : ?>
						<div class="split-content__text fade-up"><?= wp_kses_post( $split_text ); ?></div>
					<?php endif; ?>

					<?php if ( ! empty( $split_buttons ) ) : ?>

						<div class="split-content__buttons fade-up">

							<?php foreach ( $split_buttons as $button ) : ?>

								<?php
								if ( empty( $button['button_link']['url'] ) ) {
									continue;
								}

								$link   = $button['button_link'];
								$style  = ! empty( $button['button_style'] ) ? $button['button_style'] : 'b-light';
								$target = ! empty( $link['target'] ) ? $link['target'] : '_self';
								?>

								<a
									class="<?= esc_attr( $style ); ?>"
									href="<?= esc_url( $link['url'] ); ?>"
									target="<?= esc_attr( $target ); ?>"
									<?= $target === '_blank' ? 'rel="noopener"' : ''; ?>
								>
									<span><?= esc_html( $link['title'] ); ?></span>
								</a>

							<?php endforeach; ?>

						</div>

					<?php endif; ?>

				</div>

			</div>

		</div>

	</div>

</section>