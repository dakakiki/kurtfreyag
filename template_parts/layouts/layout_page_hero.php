<?php
/**
 * Layout: layout_page_hero
 *
 * XD composition (1440 x 800 artboard):
 *   image    x373 -> 1440, full height, 150px radius on the bottom right
 *   overlay  blue quad, straight left edge, diagonal right edge from x488
 *            at the top to x755 at the bottom - the "cut" shape
 *   content  sits on the blue, title at x140
 *
 * The image sits under the overlay, so both are painted before the content.
 *
 * The hero is always full height - there is no height field in ACF.
 */

$hero_row     = get_row_index();
$hero_content = get_sub_field( 'content' );
$hero_image   = get_sub_field( 'image' );

$hero_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

?>

<section
	<?php if ( $hero_anchor ) : ?>id="<?= esc_attr( $hero_anchor ); ?>"<?php endif; ?>
	class="layout-page-hero"
	data-animate-now
>

	<div class="page-hero" id="lyt-<?= (int) $hero_row; ?>">

		<?php if ( ! empty( $hero_image ) ) : ?>
			<div class="page-hero__image fade-right">
				<?= theme_acf_image(
					$hero_image,
					'full',
					array(
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'sizes'         => '(max-width: 991px) 100vw, 75vw',
					)
				); ?>
			</div>
		<?php endif; ?>

		<div class="page-hero__overlay" aria-hidden="true"></div>

		<div class="page-hero__content">

			<div class="page-hero__wrap group-fade-up">

				<?php if ( ! empty( $hero_content['title'] ) ) : ?>
					<h1 class="fade-up"><?= wp_kses_post( $hero_content['title'] ); ?></h1>
				<?php endif; ?>

				<?php if ( ! empty( $hero_content['text'] ) ) : ?>
					<div class="page-hero__wrap--text fade-up"><?= wp_kses_post( $hero_content['text'] ); ?></div>
				<?php endif; ?>

				<?php if ( ! empty( $hero_content['buttons'] ) ) : ?>

					<div class="page-hero__wrap--btns fade-up">

						<?php foreach ( $hero_content['buttons'] as $button ) : ?>

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

</section>