<?php
/**
 * Layout: layout_title_text
 *
 * XD reference (Über Uns artboard, 1440):
 *   inset  text runs x238 -> 1202, so 98 in from both sides of the 1160
 *          container - the same inset the service areas use
 *   title  Lato Bold 53 #00529E
 *   quote  Lato Bold 16 #FFB500, 32 below the title's baseline
 *
 * The gold passage is authored as a blockquote in the editor, which is what
 * the styling hangs off - see pages note in the SCSS.
 */

$tt_row    = get_row_index();
$tt_title  = get_sub_field( 'title' );
$tt_text   = get_sub_field( 'text' );
$tt_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

if ( ! $tt_title && ! $tt_text ) {
	return;
}
?>

<section
	<?php if ( $tt_anchor ) : ?>id="<?= esc_attr( $tt_anchor ); ?>"<?php endif; ?>
	class="layout-title-text"
>

	<div class="title-text" id="lyt-<?= (int) $tt_row; ?>">

		<div class="title-text__container">

			<div class="title-text__inner group-fade-up">

				<?php if ( $tt_title ) : ?>
					<h2 class="title-text__title fade-up"><?= esc_html( $tt_title ); ?></h2>
				<?php endif; ?>

				<?php if ( $tt_text ) : ?>
					<div class="title-text__text fade-up"><?= wp_kses_post( $tt_text ); ?></div>
				<?php endif; ?>

			</div>

		</div>

	</div>

</section>