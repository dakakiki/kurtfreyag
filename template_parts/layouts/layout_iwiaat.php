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

/*
 * Anchors.
 *
 * Each item gets an id built from its title so the main menu can link
 * straight to it. Titles are a textarea and may carry a <br>, so the markup
 * is stripped before the slug is made - otherwise "Energie mit<br />
 * Verantwortung" would slug the tag along with the words.
 *
 * Two items with the same title would collide, so a repeat gets a counter.
 *
 * The row index is deliberately not part of the id: a menu link has to keep
 * working when the block is moved up or down the page.
 */
$iw_used_anchors = array();

$iw_make_anchor = function ( $title ) use ( &$iw_used_anchors ) {

	$slug = sanitize_title( wp_strip_all_tags( (string) $title ) );

	if ( ! $slug ) {
		$slug = 'item-' . ( count( $iw_used_anchors ) + 1 );
	}

	$slug = 'iwiaat-' . $slug;

	if ( isset( $iw_used_anchors[ $slug ] ) ) {

		$iw_used_anchors[ $slug ]++;

		$slug .= '-' . $iw_used_anchors[ $slug ];
	} else {

		$iw_used_anchors[ $slug ] = 1;
	}

	return $slug;
};
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

					$item_anchor = $iw_make_anchor( $title );
					?>

					<article
						id="<?= esc_attr( $item_anchor ); ?>"
						class="iwiaat__item <?= esc_attr( $is_card ? 'is-card ' . $background : 'is-plain' ); ?> fade-up"
					>

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