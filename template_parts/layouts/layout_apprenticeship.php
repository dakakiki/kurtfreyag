<?php
/**
 * Layout: layout_apprenticeship
 *
 * XD reference (Jobs artboard, 1440):
 *   card       1160 x 1062, #00529E, 60px radius, text 98 in from its edge
 *   title      Lato Bold 53 white
 *   text       Lato Bold 25 #A7D8F4 headings over Lato Regular 16 white
 *   row        side text x238 -> 700, vacancies card x728 -> 1202
 *   vacancies  white card 474 x 381, 60px radius, its own 98 of side padding
 *   heading    Lato Bold 25 #00529E
 *   position   Lato Bold 16 #00529E over its availability in Lato Regular 16
 */

$app_row    = get_row_index();
$app_title  = get_sub_field( 'title' );
$app_text   = get_sub_field( 'text' );
$app_side   = get_sub_field( 'side_content' );
$app_vac    = get_sub_field( 'vacancies' );
$app_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

$app_side_text = $app_side['side_text'] ?? '';
$app_vac_title = $app_vac['title'] ?? '';
$app_vac_items = $app_vac['vacancie_items'] ?? array();

/* Only rows with a position count - an empty repeater row is not a vacancy. */
$app_vac_items = array_filter( (array) $app_vac_items, function ( $item ) {
	return ! empty( $item['position'] );
} );

if ( ! $app_title && ! $app_text && ! $app_side_text && empty( $app_vac_items ) ) {
	return;
}
?>

<section
	<?php if ( $app_anchor ) : ?>id="<?= esc_attr( $app_anchor ); ?>"<?php endif; ?>
	class="layout-apprenticeship"
>

	<div class="apprenticeship" id="lyt-<?= (int) $app_row; ?>">

		<div class="apprenticeship__container">

			<div class="apprenticeship__card group-fade-up">

				<?php if ( $app_title ) : ?>
					<h2 class="apprenticeship__title fade-up"><?= esc_html( $app_title ); ?></h2>
				<?php endif; ?>

				<?php if ( $app_text ) : ?>
					<div class="apprenticeship__text fade-up"><?= wp_kses_post( $app_text ); ?></div>
				<?php endif; ?>

				<?php if ( $app_side_text || ! empty( $app_vac_items ) ) : ?>

					<div class="apprenticeship__row">

						<?php if ( $app_side_text ) : ?>
							<div class="apprenticeship__side fade-up"><?= wp_kses_post( $app_side_text ); ?></div>
						<?php endif; ?>

						<?php if ( ! empty( $app_vac_items ) ) : ?>

							<div class="apprenticeship__vacancies fade-up">

								<?php if ( $app_vac_title ) : ?>
									<h3 class="apprenticeship__vacancies-title"><?= esc_html( $app_vac_title ); ?></h3>
								<?php endif; ?>

								<ul class="apprenticeship__vacancies-list">

									<?php foreach ( $app_vac_items as $item ) : ?>
										<li class="apprenticeship__vacancy">

											<span class="apprenticeship__position"><?= esc_html( $item['position'] ); ?></span>

											<?php if ( ! empty( $item['availability'] ) ) : ?>
												<span class="apprenticeship__availability"><?= nl2br( esc_html( $item['availability'] ) ); ?></span>
											<?php endif; ?>

										</li>
									<?php endforeach; ?>

								</ul>

							</div>

						<?php endif; ?>

					</div>

				<?php endif; ?>

			</div>

		</div>

	</div>

</section>