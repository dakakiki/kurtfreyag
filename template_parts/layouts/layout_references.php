<?php
/**
 * Layout: layout_references
 *
 * XD reference (Referenzen/Projekte, 1440):
 *   filter  pills 180 x 45, #EDF6FC, 100 radius, 16 apart, above the title
 *   title   Lato Bold 53 #00529E at x140
 *   grid    three columns of 376 x 339 cards, 16 apart
 *   card    picture with the project name in Lato Bold 25 white over it
 *   hover   the card turns #00529E and shows the Referenzen fields in
 *           Lato Regular 16 white, 49 in from the card edge
 *
 * Projects come from the referenz CPT, grouped by referenzgruppe. Helpers are
 * in inc/reference.php.
 */

$ref_row    = get_row_index();
$ref_title  = get_sub_field( 'title' );
$ref_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

$ref_terms = kfa_get_reference_terms();
$ref_term  = kfa_get_current_reference_term();
$ref_page  = kfa_get_current_reference_page();

/*
 * Server side the list is cumulative: page two renders eighteen projects, not
 * the second nine, so the load more link is literally true without JavaScript.
 */
$ref_query = kfa_get_reference_initial_query( $ref_page, $ref_term );
$ref_more  = (int) $ref_query->found_posts > KFA_REF_PER_PAGE * $ref_page;

$ref_active_slug = '';

if ( $ref_term ) {
	$ref_active_term = get_term( $ref_term, kfa_ref_taxonomy() );
	$ref_active_slug = ( $ref_active_term && ! is_wp_error( $ref_active_term ) ) ? $ref_active_term->slug : '';
}
?>

<section
	<?php if ( $ref_anchor ) : ?>id="<?= esc_attr( $ref_anchor ); ?>"<?php endif; ?>
	class="layout-references"
>

	<?php
	/*
	 * The AJAX config sits on the block rather than on one control, because
	 * both the filter and the load more button need it - and because a data
	 * attribute does not care what hook priority anything ran at.
	 */
	?>
	<div
		class="references"
		id="lyt-<?= (int) $ref_row; ?>"
		data-references-block
		data-ajax-url="<?= esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
		data-action="<?= esc_attr( KFA_REF_AJAX_ACTION ); ?>"
		data-nonce="<?= esc_attr( wp_create_nonce( KFA_REF_AJAX_ACTION ) ); ?>"
		data-term="<?= esc_attr( $ref_active_slug ); ?>"
		data-page="<?= (int) $ref_page; ?>"
	>

		<div class="references__container">

			<?php if ( ! empty( $ref_terms ) ) : ?>

				<nav class="references__filter fade-up" aria-label="<?php esc_attr_e( 'Nach Bereich filtern', 'KurtFreyAG' ); ?>">

					<button
						type="button"
						class="references__pill<?= $ref_active_slug ? '' : ' is-active'; ?>"
						data-reference-term=""
						aria-pressed="<?= $ref_active_slug ? 'false' : 'true'; ?>"
					><?php esc_html_e( 'Alle', 'KurtFreyAG' ); ?></button>

					<?php foreach ( $ref_terms as $term ) : ?>
						<button
							type="button"
							class="references__pill<?= $ref_active_slug === $term->slug ? ' is-active' : ''; ?>"
							data-reference-term="<?= esc_attr( $term->slug ); ?>"
							aria-pressed="<?= $ref_active_slug === $term->slug ? 'true' : 'false'; ?>"
						><?= esc_html( $term->name ); ?></button>
					<?php endforeach; ?>

				</nav>

				<div class="references__select">

					<label class="references__select-label" for="references-select-<?= (int) $ref_row; ?>">
						<?php esc_html_e( 'Bereich auswählen', 'KurtFreyAG' ); ?>
					</label>

					<select id="references-select-<?= (int) $ref_row; ?>" data-reference-select>

						<option value=""><?php esc_html_e( 'Alle', 'KurtFreyAG' ); ?></option>

						<?php foreach ( $ref_terms as $term ) : ?>
							<option value="<?= esc_attr( $term->slug ); ?>" <?php selected( $ref_active_slug, $term->slug ); ?>>
								<?= esc_html( $term->name ); ?>
							</option>
						<?php endforeach; ?>

					</select>

				</div>

			<?php endif; ?>

			<?php if ( $ref_title ) : ?>
				<h2 class="references__title fade-up"><?= esc_html( $ref_title ); ?></h2>
			<?php endif; ?>

			<div class="references__grid" data-references-grid data-gsap-cards>

				<?php if ( $ref_query->post_count ) : ?>
					<?php while ( $ref_query->have_posts() ) : ?>
						<?php $ref_query->the_post(); ?>
						<?php get_template_part( 'template_parts/reference/card' ); ?>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php endif; ?>

			</div>

			<p class="references__empty" data-references-empty <?= $ref_query->post_count ? 'hidden' : ''; ?>>
				<?php esc_html_e( 'Für diesen Bereich sind keine Projekte vorhanden.', 'KurtFreyAG' ); ?>
			</p>

			<div class="references__more" data-references-more-wrap <?= $ref_more ? '' : 'hidden'; ?>>

				<button
					type="button"
					class="references__more-btn"
					data-references-more
					data-label-more="<?php esc_attr_e( 'Mehr anzeigen', 'KurtFreyAG' ); ?>"
					data-label-loading="<?php esc_attr_e( 'Wird geladen …', 'KurtFreyAG' ); ?>"
					data-label-error="<?php esc_attr_e( 'Laden fehlgeschlagen. Bitte erneut versuchen.', 'KurtFreyAG' ); ?>"
				><?php esc_html_e( 'Mehr anzeigen', 'KurtFreyAG' ); ?></button>

			</div>

			<p class="references__status" role="status" aria-live="polite"></p>

		</div>

	</div>

</section>