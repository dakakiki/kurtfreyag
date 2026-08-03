<!DOCTYPE html>

<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">

	<script>
		/*
		 * Marks the document as script-enabled so the scroll animations can start
		 * hidden without risking permanently invisible content. If the animation
		 * bundle never runs, the flag is dropped and everything is shown.
		 */
		(function () {
			var root = document.documentElement;

			root.classList.add('js');

			window.setTimeout(function () {
				if (!window.__animationsReady) {
					root.classList.remove('js');
				}
			}, 3000);
		})();
	</script>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php wp_head(); ?>

	<?php $general_options = getGeneralOptions(); ?>

	<?php if ( ! empty( $general_options['code_head'] ) ) : ?>
		<?= $general_options['code_head']; ?>
	<?php endif; ?>

</head>

<body id="top" <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php if ( ! empty( $general_options['code_body'] ) ) : ?>
	<?= $general_options['code_body']; ?>
<?php endif; ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Zum Inhalt springen', 'KurtFreyAG' ); ?></a>

<?php
	$headerElements = getHeaderSettings();

	$logo         = $headerElements['h_logo'] ?? null;
	$headerButton = $headerElements['h_button'] ?? null;

	$logoURL      = home_url( '/' );
	$logoURLtitle = get_bloginfo( 'name' );
?>

<header>

	<div class="header">

		<div class="header__all">

			<?php if ( ! empty( $logo ) ) : ?>
				<div class="header__logo">
					<a href="<?= esc_url( $logoURL ); ?>" title="<?= esc_attr( $logoURLtitle ); ?>">
						<?= theme_acf_image( $logo, 'medium', array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'alt'           => $logoURLtitle,
						) ); ?>
					</a>
				</div>
			<?php endif; ?>

			<nav class="header__nav" aria-label="<?php esc_attr_e( 'Hauptnavigation', 'KurtFreyAG' ); ?>">
				<?php get_template_part( 'template_parts/main_menu' ); ?>
			</nav>

			<?php if ( ! empty( $headerButton['url'] ) ) : ?>
				<div class="header__buttons">
					<a
						class="btn-apply"
						href="<?= esc_url( $headerButton['url'] ); ?>"
						target="<?= esc_attr( ! empty( $headerButton['target'] ) ? $headerButton['target'] : '_self' ); ?>"
						<?= ( ! empty( $headerButton['target'] ) && $headerButton['target'] === '_blank' ) ? 'rel="noopener"' : ''; ?>
					>
						<span><?= esc_html( $headerButton['title'] ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<button
				type="button"
				class="header__hamburger"
				aria-controls="rsp-menu"
				aria-expanded="false"
				aria-label="<?php esc_attr_e( 'Menü öffnen', 'KurtFreyAG' ); ?>"
			>
				<svg xmlns="http://www.w3.org/2000/svg" width="30" height="17" viewBox="0 0 40 21" aria-hidden="true" focusable="false">
					<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1">
						<line x1="0.5" y1="0.5" x2="39.5" y2="0.5"/>
						<line x1="0.5" y1="10.5" x2="39.5" y2="10.5"/>
						<line x1="0.5" y1="20.5" x2="39.5" y2="20.5"/>
					</g>
				</svg>
			</button>

		</div>

	</div>

</header>

<div class="rsp" id="rsp-menu">

	<div class="rsp__elements">

		<div class="rsp__header">

			<?php if ( ! empty( $logo ) ) : ?>
				<div class="rsp__logo">
					<a href="<?= esc_url( $logoURL ); ?>" title="<?= esc_attr( $logoURLtitle ); ?>">
						<?= theme_acf_image( $logo, 'medium', array(
							'loading' => 'lazy',
							'alt'     => $logoURLtitle,
						) ); ?>
					</a>
				</div>
			<?php endif; ?>

			<button
				type="button"
				class="rsp__close"
				aria-label="<?php esc_attr_e( 'Menü schliessen', 'KurtFreyAG' ); ?>"
			>
				<svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 12.413 13.413" aria-hidden="true" focusable="false">
					<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1">
						<line y1="12" x2="11" transform="translate(0.7 0.7)"/>
						<line x2="11" y2="12" transform="translate(0.7 0.7)"/>
					</g>
				</svg>
			</button>

		</div>

		<div class="rsp__content">
			<?php get_template_part( 'template_parts/main_menu' ); ?>
		</div>

		<div class="rsp__footer">

			<?php if ( ! empty( $headerButton['url'] ) ) : ?>
				<a
					class="rsp__cta"
					href="<?= esc_url( $headerButton['url'] ); ?>"
					target="<?= esc_attr( ! empty( $headerButton['target'] ) ? $headerButton['target'] : '_self' ); ?>"
				>
					<?= esc_html( $headerButton['title'] ); ?>
				</a>
			<?php endif; ?>

			<div class="rsp__sn">
				<?php get_template_part( 'template_parts/sn' ); ?>
			</div>

		</div>

	</div>

</div>