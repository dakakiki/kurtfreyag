<?php
/**
 * Footer
 *
 * Two variants, both taken from the XD source:
 *  - full    : contact block (blue) + menu row + white bottom bar. Used on every
 *              artboard except Kontakt.
 *  - compact : menu row + white bottom bar only. Used where the page itself
 *              already renders the contact data (Kontakt artboard).
 *
 * The variant is chosen automatically: if the page renders
 * layout_contact, the footer drops its own contact block so the same data is
 * not printed twice. Override with the kfa_footer_show_contact filter.
 *
 * @package KurtFreyAG
 */

$footerSettings = getFooterSettings();

$contact = $footerSettings['f_contact_info']   ?? array();
$hours   = $footerSettings['f_working_hours']  ?? array();
$billing = $footerSettings['f_billing_info']   ?? '';
$logo    = $footerSettings['f_logo']['image']  ?? null;
$copy    = $footerSettings['f_copy_text']      ?? '';

$phone   = $contact['phone']   ?? '';
$email   = $contact['e-mail']  ?? '';
$address = $contact['address'] ?? '';

$has_contact_data = ( $phone || $email || $address || ! empty( $hours['intervals'] ) || $billing );

$show_contact = apply_filters(
	'kfa_footer_show_contact',
	/*
	 * The slug is layout_contact. This read layout_contact_details, the name
	 * the block carried while it was still being planned, so the footer never
	 * recognised it and printed the same addresses twice.
	 */
	$has_contact_data && ! in_array( 'layout_contact', kfa_current_layouts(), true )
);
?>

<footer class="footer<?= $show_contact ? '' : ' footer--compact'; ?>" id="kontakt">

	<?php if ( $show_contact ) : ?>

		<div class="footer__main">

			<div class="footer__container">

				<h2 class="footer__title"><?php esc_html_e( 'Kontakt', 'KurtFreyAG' ); ?></h2>

				<div class="footer__grid">

					<div class="footer__col footer__col--contact">

						<?php if ( $phone ) : ?>
							<p class="footer__line">
								<span class="footer__label"><?php esc_html_e( 'Telefonnummer:', 'KurtFreyAG' ); ?></span>
								<a class="footer__pill" href="<?= esc_url( kfa_tel_href( $phone ) ); ?>"><?= esc_html( $phone ); ?></a>
							</p>
						<?php endif; ?>

						<?php if ( $email ) : ?>
							<p class="footer__line">
								<span class="footer__label"><?php esc_html_e( 'E-Mail:', 'KurtFreyAG' ); ?></span>
								<a class="footer__pill" href="<?= esc_url( 'mailto:' . $email ); ?>"><?= antispambot( esc_html( $email ) ); ?></a>
							</p>
						<?php endif; ?>

						<?php if ( $address ) : ?>
							<div class="footer__address">
								<span class="footer__label footer__label--plain"><?php esc_html_e( 'Adresse:', 'KurtFreyAG' ); ?></span>
								<?= wp_kses_post( $address ); ?>
							</div>
						<?php endif; ?>

					</div>

					<div class="footer__col footer__col--blocks">

						<?php if ( ! empty( $hours['intervals'] ) ) : ?>
							<div class="footer__block" data-footer-block>

								<h3 class="footer__block-title">
									<button type="button" class="footer__toggle" aria-expanded="false" aria-controls="footer-hours">
										<span><?= esc_html( ! empty( $hours['title'] ) ? $hours['title'] : __( 'Unsere Öffnungszeiten', 'KurtFreyAG' ) ); ?></span>
										<img class="footer__chevron" src="<?= esc_url( THEME_URI . '/dist/images/ico_footer_arrow.svg' ); ?>" width="23" height="21" alt="" aria-hidden="true">
									</button>
								</h3>

								<div class="footer__panel" id="footer-hours">
									<dl class="footer__hours">
										<?php foreach ( $hours['intervals'] as $row ) : ?>
											<?php if ( empty( $row['interval'] ) && empty( $row['hours'] ) ) { continue; } ?>
											<dt><?= esc_html( $row['interval'] ); ?></dt>
											<dd><?= esc_html( $row['hours'] ); ?></dd>
										<?php endforeach; ?>
									</dl>

									<?php if ( ! empty( $hours['note'] ) ) : ?>
										<div class="footer__note"><?= wp_kses_post( $hours['note'] ); ?></div>
									<?php endif; ?>
								</div>

							</div>
						<?php endif; ?>

						<?php if ( $billing ) : ?>
							<div class="footer__block footer__block--billing" data-footer-block>

								<h3 class="footer__block-title">
									<button type="button" class="footer__toggle" aria-expanded="false" aria-controls="footer-billing">
										<span><?php esc_html_e( 'Rechnungsadressen', 'KurtFreyAG' ); ?></span>
										<img class="footer__chevron" src="<?= esc_url( THEME_URI . '/dist/images/ico_footer_arrow.svg' ); ?>" width="23" height="21" alt="" aria-hidden="true">
									</button>
								</h3>

								<div class="footer__panel" id="footer-billing">
									<?= wp_kses_post( $billing ); ?>
								</div>

							</div>
						<?php endif; ?>

					</div>

				</div>

				<?php if ( ! empty( $logo ) ) : ?>
					<div class="footer__watermark" aria-hidden="true">
						<?= theme_acf_image( $logo, 'medium', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
					</div>
				<?php endif; ?>

			</div>

		</div>

	<?php endif; ?>

	<div class="footer__nav">
		<div class="footer__container">
			<?php get_template_part( 'template_parts/footer_menu' ); ?>
		</div>
	</div>

	<div class="footer__bottom">
		<div class="footer__container">

			<div class="footer__copy">
				<?php if ( $copy ) : ?>
					<?= wp_kses_post( $copy ); ?>
				<?php endif; ?>
			</div>

			<div class="footer__sn">
				<?php get_template_part( 'template_parts/sn' ); ?>
			</div>

		</div>
	</div>

</footer>

<?php wp_footer(); ?>

</body>
</html>