<?php
/**
 * Layout: layout_iwiaat - Items with icon and additional text
 *
 * Two shapes, chosen per item by whether background_color is set.
 *
 * Plain (no background) - XD Über Uns "Leitbild" / "Vision 2040":
 *   box    573 x 521, 3px #00519E border, 60px radius, 16 apart
 *   title  Lato Bold 53, blue or golden per title_color
 *   text   shown in full, no toggle
 *
 * Tinted (background chosen) - XD Über Uns "Energie mit Verantwortung" row:
 *   card   376 x 460, 60px radius, filled sky / cream / ice
 *   icon   256 x 256, centred, 39 below the top
 *   title  Lato Bold 25 blue, 36 in from the left
 *   arrow  27 x 24, 36 in from the right
 *   text   hidden until the title is clicked
 */

$iw_row    = get_row_index();
$iw_items  = get_sub_field( 'items' );
$iw_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

if ( empty( $iw_items ) ) {
	return;
}

/*
 * The title is a textarea now, so an editor can break it where they want with
 * a <br>. The tag is printed as authored - only line breaks and light
 * emphasis get through, since anything else would let arbitrary markup into
 * a heading.
 */
$iw_title_tags = array(
	'br'     => array(),
	'strong' => array(),
	'em'     => array(),
);

$iw_backgrounds = array( 'i-bg-sky', 'i-bg-cream', 'i-bg-ice' );
$iw_titles      = array( 'i-title-blue', 'i-title-golden' );

$iw_arrow = THEME_URI . '/dist/images/ico_area_arrow.svg';
?>

<section
	<?php if ( $iw_anchor ) : ?>id="<?= esc_attr( $iw_anchor ); ?>"<?php endif; ?>
	class="layout-iwiaat"
>

	<div class="iwiaat" id="lyt-<?= (int) $iw_row; ?>">

		<div class="iwiaat__container">

			<div class="iwiaat__grid" data-count="<?= count( $iw_items ); ?>">

				<?php foreach ( $iw_items as $index => $item ) : ?>

					<?php
					$title = $item['title'] ?? '';
					$text  = $item['text'] ?? '';
					$icon  = $item['icon'] ?? null;

					if ( ! $title && ! $text ) {
						continue;
					}

					$background = ( ! empty( $item['background_color'] ) && in_array( $item['background_color'], $iw_backgrounds, true ) )
						? $item['background_color']
						: '';

					$title_color = ( ! empty( $item['title_color'] ) && in_array( $item['title_color'], $iw_titles, true ) )
						? $item['title_color']
						: 'i-title-blue';

					/* The background is what turns the item into a collapsible card. */
					$is_card = (bool) $background;
					?>

					<article class="iwiaat__item <?= esc_attr( $is_card ? 'is-card ' . $background : 'is-plain' ); ?> fade-up">

						<?php if ( $is_card ) : ?>

							<?php if ( ! empty( $icon ) ) : ?>
								<div class="iwiaat__icon" aria-hidden="true">
									<?= theme_acf_image( $icon, 'medium', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
								</div>
							<?php endif; ?>

							<?php
							/*
							 * <details> for the same reasons as the service areas: closed
							 * by default, opens on the title, keyboard and screen reader
							 * behaviour for free, and readable with no script at all.
							 */
							?>
							<details class="iwiaat__details">

								<summary class="iwiaat__summary">

									<span class="iwiaat__title <?= esc_attr( $title_color ); ?>"><?= wp_kses( $title, $iw_title_tags ); ?></span>

									<img
										class="iwiaat__arrow"
										src="<?= esc_url( $iw_arrow ); ?>"
										width="27"
										height="24"
										alt=""
										aria-hidden="true"
									>

								</summary>

								<?php if ( $text ) : ?>
									<div class="iwiaat__body">
										<div class="iwiaat__body-inner"><?= wp_kses_post( $text ); ?></div>
									</div>
								<?php endif; ?>

							</details>

						<?php else : ?>

							<?php if ( ! empty( $icon ) ) : ?>
								<div class="iwiaat__icon" aria-hidden="true">
									<?= theme_acf_image( $icon, 'medium', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
								</div>
							<?php endif; ?>

							<?php if ( $title ) : ?>
								<h3 class="iwiaat__title <?= esc_attr( $title_color ); ?>"><?= wp_kses( $title, $iw_title_tags ); ?></h3>
							<?php endif; ?>

							<?php if ( $text ) : ?>
								<div class="iwiaat__text"><?= wp_kses_post( $text ); ?></div>
							<?php endif; ?>

						<?php endif; ?>

					</article>

				<?php endforeach; ?>

			</div>

		</div>

	</div>

</section>