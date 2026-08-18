<?php
/**
 * Layout: layout_anchor_cloud
 *
 * XD reference (Über Uns artboard, 1440):
 *   pills  45 tall, #EDF6FC, radius 100, Lato Regular 16 #00529E
 *          180 wide by default and 376 for "Unser Weg zur Energiewende",
 *          so the width follows the label rather than being fixed
 *   row    x140 -> 1300, 16 between them
 *
 * The links are ordinary anchors, so a jump works with no script at all.
 * custom.js picks them up through the [data-anchor-scroll] wrapper and
 * clears the fixed bar - see the note there.
 */

$cloud_row    = get_row_index();
$cloud_links  = get_sub_field( 'links' );
$cloud_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

/* Only rows with somewhere to go - an empty repeater row is not a link. */
$cloud_links = array_filter( (array) $cloud_links, function ( $item ) {
	return ! empty( $item['link']['url'] );
} );

if ( empty( $cloud_links ) ) {
	return;
}
?>

<section
	<?php if ( $cloud_anchor ) : ?>id="<?= esc_attr( $cloud_anchor ); ?>"<?php endif; ?>
	class="layout-anchor-cloud"
>

	<div class="anchor-cloud" id="lyt-<?= (int) $cloud_row; ?>">

		<div class="anchor-cloud__container">

			<?php
			/*
			 * data-anchor-scroll opts these links into the scroll handling in
			 * custom.js, which measures the header and waits for the page to
			 * settle before jumping.
			 */
			?>
			<nav
				class="anchor-cloud__list fade-up"
				data-anchor-scroll
				aria-label="<?php esc_attr_e( 'Sprungmarken', 'KurtFreyAG' ); ?>"
			>

				<?php foreach ( $cloud_links as $item ) : ?>

					<?php
					$link   = $item['link'];
					$label  = ! empty( $item['label'] ) ? $item['label'] : $link['title'];
					$target = ! empty( $link['target'] ) ? $link['target'] : '_self';

					if ( ! $label ) {
						continue;
					}
					?>

					<a
						class="anchor-cloud__pill"
						href="<?= esc_url( $link['url'] ); ?>"
						target="<?= esc_attr( $target ); ?>"
						<?= $target === '_blank' ? 'rel="noopener"' : ''; ?>
					><?= esc_html( $label ); ?></a>

				<?php endforeach; ?>

			</nav>

		</div>

	</div>

</section>