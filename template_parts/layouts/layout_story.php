<?php
/**
 * Layout: layout_story
 *
 * XD reference (Über Uns "Geschichte", 1440):
 *   card    1160 wide, #00529E, 60px radius
 *   title   Lato Bold 53 white, 98 in from the card edge
 *   grid    connector 82 | text | image 393, all 16 apart, inside the same
 *           98 padding - the image ends on x1202
 *   year    Lato Bold 53 #A7D8F4
 *   text    Lato Regular 16 white
 *   image   393 wide, 3px radius
 *
 * The connector is a rounded rectangle with a white 3px stroke whose right
 * half the XD covers with a blue rectangle, leaving the curve and the
 * vertical line. Here it is drawn as a left and top border with a rounded
 * top left corner, which needs no mask.
 */

$story_row    = get_row_index();
$story_title  = get_sub_field( 'title' );
$story_items  = get_sub_field( 'timeline_items' );
$story_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

if ( ! $story_title && empty( $story_items ) ) {
	return;
}
?>

<section
	<?php if ( $story_anchor ) : ?>id="<?= esc_attr( $story_anchor ); ?>"<?php endif; ?>
	class="layout-story"
>

	<div class="story" id="lyt-<?= (int) $story_row; ?>">

		<div class="story__container">

			<div class="story__card group-fade-up">

				<?php if ( $story_title ) : ?>
					<h2 class="story__title fade-up"><?= esc_html( $story_title ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $story_items ) ) : ?>

					<ol class="story__list">

						<?php foreach ( $story_items as $item ) : ?>

							<?php
							$year  = $item['title'] ?? '';
							$text  = $item['text'] ?? '';
							$image = $item['image'] ?? null;

							if ( ! $year && ! $text && empty( $image ) ) {
								continue;
							}
							?>

							<li class="story__item fade-up">

								<?php
								/*
								 * Decorative: the line and its curve carry no meaning that
								 * the ordered list does not already convey.
								 */
								?>
								<span class="story__connector" aria-hidden="true"></span>

								<div class="story__body">

									<?php if ( $year ) : ?>
										<h3 class="story__year"><?= esc_html( $year ); ?></h3>
									<?php endif; ?>

									<?php if ( $text ) : ?>
										<div class="story__text"><?= wp_kses_post( $text ); ?></div>
									<?php endif; ?>

								</div>

								<div class="story__media">
									<?php if ( ! empty( $image ) ) : ?>
										<?= theme_acf_image( $image, 'medium_large', array(
											'loading' => 'lazy',
											'sizes'   => '(max-width: 991px) 100vw, 393px',
										) ); ?>
									<?php endif; ?>
								</div>

							</li>

						<?php endforeach; ?>

					</ol>

				<?php endif; ?>

			</div>

		</div>

	</div>

</section>