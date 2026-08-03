<?php
/**
 * The template for Single
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package KurtFreyAG
 * @subpackage KurtFreyAG
 * @since 1.0.0
 */

get_header();
?>

<main>
 
 <?php while (have_posts()): the_post(); ?>

    <?php

        $p_id = get_the_ID();

        $isPageHero = isPageHeroActive($p_id);
        $isLayout = isLayoutAnyActive($p_id);


        $thumb_id = get_post_thumbnail_id($p_id);
        $headerIMG_page = wp_get_attachment_image_url($thumb_id, 'full');

        $post_type = get_post_type();
    ?>

        <?php
    ?>

    <section id="post-<?php the_ID(); ?>">

        <?php

        switch ($post_type) {
            
            default:
                include 'template_parts/single/default.php';
                break;
        }

        ?>

     </section>

 <?php endwhile; ?>

 <?php wp_reset_postdata(); ?>

</main>

<?php get_footer(); ?>
