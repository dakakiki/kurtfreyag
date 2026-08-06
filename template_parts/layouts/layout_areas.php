<?php
/**
 * Layout: layout_areas
 *
 * XD reference (Leistungen artboard, 1440):
 *   grid     content column 882 at x140, nav column 180 at x1120 - 98 apart
 *   head     white card, 3px #00519E border, 60px radius, padding 64 / 98
 *   title    Lato Bold 53 #00529E, lead Lato Regular 16
 *   options  ice bars 882 x 81, 3px #00529E border, adjacent bars overlap by
 *            3 so the borders collapse into one line
 *   arrow    27 x 24, on the right of each bar
 *   symbol   ~180 wide, centred on the card's top border, 114 in from the
 *            card's right edge
 *   image    882 wide, rounded 60 on the bottom corners only
 *   nav      pills 180 x 45, #EDF6FC, radius 100, 18 apart
 *
 * The three pieces stack into one continuous card: white head on top, ice
 * option bars in the middle, image closing the bottom.
 *
 * Content comes from the bereich CPT: post title, featured image, and the
 * Bereich field group (symbol, ber_text, ber_optionen_mit_beschreibung).
 */

$areas_row    = get_row_index();
$areas_title  = get_sub_field( 'title' );
$areas_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

/**
 * Which post type holds the areas.
 *
 * Filterable because the Bereich field group is still bound to `post` in the
 * ACF JSON - see the note in the handover.
 */
$areas_post_type = (string) apply_filters( 'kfa_areas_post_type', 'bereich' );

$areas_query = new WP_Query( array(
	'post_type'              => $areas_post_type,
	'post_status'            => 'publish',
	'posts_per_page'         => -1,

	/* menu_order first so the six areas can be ordered in the admin. */
	'orderby'                => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	'ignore_sticky_posts'    => true,
	'no_found_rows'          => true,
	'update_post_term_cache' => false,
) );

if ( ! $areas_query->have_posts() ) {
	wp_reset_postdata();
	return;
}

/* Collected in the loop below so the nav can be printed before the content. */
$areas_items = array();

while ( $areas_query->have_posts() ) {

	$areas_query->the_post();

	$areas_items[] = array(
		'id'      => get_the_ID(),
		'title'   => get_the_title(),
		'anchor'  => 'bereich-' . sanitize_title( get_post_field( 'post_name' ) ),
		'image'   => get_post_thumbnail_id(),
		'symbol'  => get_field( 'symbol' ),
		'text'    => get_field( 'ber_text' ),
		'options' => get_field( 'ber_optionen_mit_beschreibung' ),
	);
}

wp_reset_postdata();

$areas_icon = THEME_URI . '/dist/images/ico_area_arrow.svg';
?>

<section
	<?php if ( $areas_anchor ) : ?>id="<?= esc_attr( $areas_anchor ); ?>"<?php endif; ?>
	class="layout-areas"
>

	<div class="areas" id="lyt-<?= (int) $areas_row; ?>">

		<div class="areas__container">

			<?php if ( $areas_title ) : ?>
				<h2 class="areas__title fade-up"><?= esc_html( $areas_title ); ?></h2>
			<?php endif; ?>

			<?php
			/*
			 * Below the breakpoint the six pills would eat most of the screen,
			 * so the same choices are offered as a select under the heading.
			 * Both are in the DOM and CSS shows one of them.
			 */
			?>
			<div class="areas__select">

				<label class="areas__select-label" for="areas-select-<?= (int) $areas_row; ?>">
					<?php esc_html_e( 'Bereich auswählen', 'KurtFreyAG' ); ?>
				</label>

				<select id="areas-select-<?= (int) $areas_row; ?>" data-areas-select>

					<option value=""><?php esc_html_e( 'Bereich auswählen', 'KurtFreyAG' ); ?></option>

					<?php foreach ( $areas_items as $item ) : ?>
						<option value="#<?= esc_attr( $item['anchor'] ); ?>"><?= esc_html( $item['title'] ); ?></option>
					<?php endforeach; ?>

				</select>

			</div>

			<div class="areas__grid">

				<div class="areas__list">

					<?php foreach ( $areas_items as $item ) : ?>

						<article class="areas__item fade-up" id="<?= esc_attr( $item['anchor'] ); ?>">

							<div class="areas__head">

								<?php if ( ! empty( $item['symbol'] ) ) : ?>
									<div class="areas__symbol" aria-hidden="true">
										<?= theme_acf_image( $item['symbol'], 'medium', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
									</div>
								<?php endif; ?>

								<h3 class="areas__item-title"><?= esc_html( $item['title'] ); ?></h3>

								<?php if ( ! empty( $item['text'] ) ) : ?>
									<div class="areas__text"><?= wp_kses_post( $item['text'] ); ?></div>
								<?php endif; ?>

							</div>

							<?php if ( ! empty( $item['options'] ) ) : ?>

								<div class="areas__options">

									<?php foreach ( $item['options'] as $option ) : ?>

										<?php if ( empty( $option['titel'] ) ) { continue; } ?>

										<?php
										/*
										 * <details> rather than a scripted accordion: closed by
										 * default, opens on the title, keyboard and screen reader
										 * behaviour for free, and it still works if no script runs.
										 */
										?>
										<details class="areas__option">

											<summary class="areas__option-head">

												<span class="areas__option-title"><?= esc_html( $option['titel'] ); ?></span>

												<img
													class="areas__option-arrow"
													src="<?= esc_url( $areas_icon ); ?>"
													width="27"
													height="24"
													alt=""
													aria-hidden="true"
												>

											</summary>

											<?php if ( ! empty( $option['beschreibung'] ) ) : ?>
												<?php
												/*
												 * The padding sits on the inner element so the outer
												 * one can be animated from a height of zero without
												 * its padding showing while it is closed.
												 */
												?>
												<div class="areas__option-body">
													<div class="areas__option-inner"><?= wp_kses_post( $option['beschreibung'] ); ?></div>
												</div>
											<?php endif; ?>

										</details>

									<?php endforeach; ?>

								</div>

							<?php endif; ?>

							<?php if ( $item['image'] ) : ?>
								<div class="areas__media">
									<?= wp_get_attachment_image( $item['image'], 'large', false, array(
										'loading' => 'lazy',
										'sizes'   => '(max-width: 991px) 100vw, 882px',
									) ); ?>
								</div>
							<?php endif; ?>

						</article>

					<?php endforeach; ?>

				</div>

				<?php
				/*
				 * Plain anchor links, so the jump works without JavaScript.
				 * Smoothing and the offset under the fixed bar are CSS.
				 */
				?>
				<nav class="areas__nav" aria-label="<?php esc_attr_e( 'Bereiche', 'KurtFreyAG' ); ?>">

					<ul class="areas__nav-list">

						<?php foreach ( $areas_items as $item ) : ?>
							<li>
								<a class="areas__nav-link" href="#<?= esc_attr( $item['anchor'] ); ?>">
									<?= esc_html( $item['title'] ); ?>
								</a>
							</li>
						<?php endforeach; ?>

					</ul>

				</nav>

			</div>

		</div>

	</div>

</section>