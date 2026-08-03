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

    <?php
        $general_options = getGeneralOptions();
    ?>

    <?php if(!empty($general_options['code_head'])): ?>
        <?= $general_options['code_head']; ?>
    <?php endif; ?>

</head>

<body id="top">

<?php wp_body_open(); ?>

<?php if(!empty($general_options['code_body'])): ?>
    <?= $general_options['code_body']; ?>
<?php endif; ?>

<?php

    $headerElements = getHeaderSettings();

    $logoURL = get_home_url();
    $logoURLtitle = get_bloginfo('name');
    $logoTarget = '_self';
?>

<header>

    <div class="header">

        <div class="header__all">

            <?php if(isset($headerElements['h_logo']) && $headerElements['h_logo'] != ''): ?>
            <div class="header__logo">
                <a href="<?php echo $logoURL; ?>" title="<?php echo $logoURLtitle; ?>" target="<?= $logoTarget; ?>">
                    <img loading="eager" fetchpriority="high" src="<?php echo $headerElements['h_logo']['url']; ?>" width="116px" height="144px" alt="<?php echo $logoURLtitle; ?>">
                </a>
            </div>
            <?php endif; ?>

            <div class="header__nav">
                <?php
                    include 'template_parts/main_menu.php';
                ?>
            </div>

            <?php if(isset($headerElements['h_button']) && $headerElements['h_button'] != '' && isset($headerElements['h_button']['url'])): ?>
            <div class="header__buttons">
                <a
                    class="btn-apply" 
                    href="<?= esc_url($headerElements['h_button']['url']); ?>" 
                    title="<?= esc_attr($headerElements['h_button']['title']); ?>" 
                    target="_self"
                    >

                    <span><?= esc_attr($headerElements['h_button']['title']); ?></span>
                </a>
            </div>
            <?php endif; ?>

            <button
                type="button"
                class="header__hamburger"
                aria-controls="rsp-menu"
                aria-expanded="false"
                aria-label="<?php echo esc_attr__( 'Open menu', 'KurtFreyAG' ); ?>"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 40 21" aria-hidden="true" focusable="false">
                <g id="Group_433" data-name="Group 433" transform="translate(-315 -43)">
                    <line id="Line_31" data-name="Line 31" x2="39" transform="translate(315.5 43.5)" fill="none" stroke="#fff" stroke-linecap="round" stroke-width="1"/>
                    <line id="Line_32" data-name="Line 32" x2="39" transform="translate(315.5 53.5)" fill="none" stroke="#fff" stroke-linecap="round" stroke-width="1"/>
                    <line id="Line_33" data-name="Line 33" x2="39" transform="translate(315.5 63.5)" fill="none" stroke="#fff" stroke-linecap="round" stroke-width="1"/>
                </g>
                </svg>
            </button>

        </div>

    </div>

</header>

<div class="rsp" id="rsp-menu">

    <div class="rsp__elements">

        <div class="rsp__header">

            <?php if (!empty($headerElements['h_logo'])): ?>
            <div class="rsp__logo">

                <a href="<?php echo $logoURL; ?>" title="<?php echo $logoURLtitle; ?>" target="<?= $logoTarget; ?>">
                    <img loading="eager" fetchpriority="high" src="<?php echo $headerElements['h_logo']['url']; ?>" width="116px" height="144px" alt="<?php echo $logoURLtitle; ?>">
                </a>

            </div>
            <?php endif; ?>

            <button
                type="button"
                class="rsp__close"
                aria-label="<?php echo esc_attr__( 'Close menu', 'KurtFreyAG' ); ?>"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="12.413" height="13.413" viewBox="0 0 12.413 13.413" aria-hidden="true" focusable="false">
                <g id="Group_434" data-name="Group 434" transform="translate(-332.794 -790.794)">
                    <line id="Line_72" data-name="Line 72" y1="12" x2="11" transform="translate(333.5 791.5)" fill="none" stroke="#fff" stroke-linecap="round" stroke-width="1"/>
                    <line id="Line_73" data-name="Line 73" x2="11" y2="12" transform="translate(333.5 791.5)" fill="none" stroke="#fff" stroke-linecap="round" stroke-width="1"/>
                </g>
                </svg>
            </button>

        </div>

        <div class="rsp__content">
            <?php
                include 'template_parts/main_menu.php';
            ?>
        </div>

        <div class="rsp__footer">

            <div class="rsp__sn">
                <?php
                    include 'template_parts/sn.php';
                ?>
            </div>

        </div>

    </div>

</div>