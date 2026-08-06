<?php
/**
 * Layout: layout_contact
 *
 * XD reference (Kontakt artboard, 1440):
 *   columns  contact x140 -> 418, billing x475 -> 753, hours card
 *            x810 -> 1300 - three tracks of 278, 278 and 490 with 57 between
 *   title    Lato Bold 53 #00529E
 *   labels   Lato Bold 16 #00529E, values in ice pills as in the footer
 *   address  Lato Regular 16 #00529E
 *   card     490 x 289, #00529E, 60px radius, 43 of side padding
 *   hours    heading Lato Bold 16 white, days at x853 and times at x1033
 *   note     Lato Italic 16 #A7D8F4
 *
 * The data is the same Theme Settings the footer reads, so a change to the
 * opening hours or an address shows up in both places at once.
 */

$contact_row    = get_row_index();
$contact_title  = get_sub_field( 'title' );
$contact_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

$contact_settings = getFooterSettings();

$contact_info    = $contact_settings['f_contact_info']  ?? array();
$contact_hours   = $contact_settings['f_working_hours'] ?? array();
$contact_billing = $contact_settings['f_billing_info']  ?? '';

$contact_phone   = $contact_info['phone']   ?? '';
$contact_mail    = $contact_info['e-mail']  ?? '';
$contact_address = $contact_info['address'] ?? '';

if ( ! $contact_title && ! $contact_phone && ! $contact_mail && ! $contact_address
	&& empty( $contact_hours['intervals'] ) && ! $contact_billing ) {
	return;
}
?>

<section
	<?php if ( $contact_anchor ) : ?>id="<?= esc_attr( $contact_anchor ); ?>"<?php endif; ?>
	class="layout-contact"
>

	<div class="contact" id="lyt-<?= (int) $contact_row; ?>">

		<div class="contact__container">

			<?php if ( $contact_title ) : ?>
				<h2 class="contact__title fade-up"><?= esc_html( $contact_title ); ?></h2>
			<?php endif; ?>

			<div class="contact__grid group-fade-up">

				<div class="contact__col contact__col--details fade-up">

					<?php if ( $contact_phone ) : ?>
						<p class="contact__line">
							<span class="contact__label"><?php esc_html_e( 'Telefonnummer:', 'KurtFreyAG' ); ?></span>
							<a class="contact__pill" href="<?= esc_url( kfa_tel_href( $contact_phone ) ); ?>"><?= esc_html( $contact_phone ); ?></a>
						</p>
					<?php endif; ?>

					<?php if ( $contact_mail ) : ?>
						<p class="contact__line">
							<span class="contact__label"><?php esc_html_e( 'E-Mail:', 'KurtFreyAG' ); ?></span>
							<a class="contact__pill" href="<?= esc_url( 'mailto:' . $contact_mail ); ?>"><?= antispambot( esc_html( $contact_mail ) ); ?></a>
						</p>
					<?php endif; ?>

					<?php if ( $contact_address ) : ?>
						<div class="contact__address">
							<span class="contact__label contact__label--plain"><?php esc_html_e( 'Adresse:', 'KurtFreyAG' ); ?></span>
							<?= wp_kses_post( $contact_address ); ?>
						</div>
					<?php endif; ?>

				</div>

				<?php if ( $contact_billing ) : ?>
					<div class="contact__col contact__col--billing fade-up">

						<span class="contact__label contact__label--plain"><?php esc_html_e( 'Rechnungsadressen:', 'KurtFreyAG' ); ?></span>

						<div class="contact__billing"><?= wp_kses_post( $contact_billing ); ?></div>

					</div>
				<?php endif; ?>

				<?php if ( ! empty( $contact_hours['intervals'] ) ) : ?>
					<div class="contact__col contact__col--hours fade-up">

						<div class="contact__card">

							<h3 class="contact__card-title">
								<?= esc_html( ! empty( $contact_hours['title'] ) ? $contact_hours['title'] : __( 'Unsere Öffnungszeiten:', 'KurtFreyAG' ) ); ?>
							</h3>

							<dl class="contact__hours">
								<?php foreach ( $contact_hours['intervals'] as $row ) : ?>
									<?php if ( empty( $row['interval'] ) && empty( $row['hours'] ) ) { continue; } ?>
									<dt><?= esc_html( $row['interval'] ); ?></dt>
									<dd><?= esc_html( $row['hours'] ); ?></dd>
								<?php endforeach; ?>
							</dl>

							<?php if ( ! empty( $contact_hours['note'] ) ) : ?>
								<div class="contact__note"><?= wp_kses_post( $contact_hours['note'] ); ?></div>
							<?php endif; ?>

						</div>

					</div>
				<?php endif; ?>

			</div>

		</div>

	</div>

</section>