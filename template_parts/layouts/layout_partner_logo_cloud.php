<?php
/**
 * Layout: layout_partner_logo_cloud
 *
 * XD reference (Homepage "Vernetzt und verbunden", 1440 artboard):
 *   frame  1160 wide at x140, white fill, 3px #00519E border, 60px radius
 *   title  Lato Bold 25 #00529E, centred
 *   logos  six across, ~155 x 103, 14 apart, first at x222 and last ending
 *          on x1218 - so the frame has the same 82 of side padding as the
 *          news slider
 *
 * The gallery carries no link or name field, so each logo is a plain image
 * and its alt text comes from the attachment.
 */

$logos_row    = get_row_index();
$logos_title  = get_sub_field( 'title' );
$logos_text   = get_sub_field( 'text' );
$logos_images = get_sub_field( 'images' );
$logos_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

/* An empty frame is worse than no frame. */
if ( empty( $logos_images ) && ! $logos_title && ! $logos_text ) {
	return;
}
?>

<section
	<?php if ( $logos_anchor ) : ?>id="<?= esc_attr( $logos_anchor ); ?>"<?php endif; ?>
	class="layout-partner-logo-cloud"
>

	<div class="logo-cloud" id="lyt-<?= (int) $logos_row; ?>">

		<div class="logo-cloud__container">

			<div class="logo-cloud__frame group-fade-up">

				<?php if ( $logos_title ) : ?>
					<h2 class="logo-cloud__title fade-up"><?= esc_html( $logos_title ); ?></h2>
				<?php endif; ?>

				<?php if ( $logos_text ) : ?>
					<div class="logo-cloud__text fade-up"><?= wp_kses_post( $logos_text ); ?></div>
				<?php endif; ?>

				<?php if ( ! empty( $logos_images ) ) : ?>

					<ul class="logo-cloud__list fade-up">

						<?php foreach ( $logos_images as $logo ) : ?>

							<?php if ( empty( $logo['ID'] ) ) { continue; } ?>

							<li class="logo-cloud__item">
								<?= theme_acf_image( $logo, 'medium', array(
									'loading' => 'lazy',
									'sizes'   => '(max-width: 640px) 44vw, (max-width: 991px) 30vw, 155px',
								) ); ?>
							</li>

						<?php endforeach; ?>

					</ul>

				<?php endif; ?>

			</div>

		</div>

	</div>

</section>