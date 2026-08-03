<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#404-not-found
 *
 * @package KurtFreyAG
 * @subpackage KurtFreyAG
 * @since 1.0.0
 */

get_header();

?>

<main>

    <section class="error-404 not-found">

        <div class="spacer"></div>

        <div class="page-header-wrap">
            <div class="page-header">
                <h1><?=  __('Page not found!','KurtFreyAG'); ?></h1>
            </div>
        </div>

        <div class="page-wrap">
            <div class="page-content">
                <div class="content">
                    <?= __('Please try again.','KurtFreyAG'); ?>
                </div>
            </div>
        </div>

    </section>

</main>

<?php
get_footer();
?>