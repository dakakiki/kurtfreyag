<?php
/**
 * The template for Page
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package KurtFreyAG
 * @subpackage KurtFreyAG
 * @since 1.0.0
 */

get_header();

$headerIMG_page = '';
?>

<main>
 
 <?php while (have_posts()): the_post(); ?>


    <?php

        $p_id = get_the_ID();

        $isPageHero = isPageHeroActive($p_id);
        $isLayout = isLayoutAnyActive($p_id);

        $headerIMG_page = get_the_post_thumbnail_url();
    ?>

    <section id="post-<?php the_ID(); ?>">

        <?php if ($isPageHero == 0): ?>

            <div class="spacer"></div>

            <div class="page-header-wrap">

                <div class="page-header">

                    <h1><?php echo get_the_title(); ?></h1>

                </div>

            </div>

        <?php else: ?>

            <!-- <div class="spacer"></div> -->

        <?php endif; ?>


        <?php if ($headerIMG_page != ''): ?>

            <div class="head-image" style="background-image: url('<?php echo $headerIMG_page;?>');"></div>

        <?php endif; ?>

         <?php if (!empty(get_the_content())): ?>
                
             <div class="page-wrap">

                <div class="page-content">

                    <div class="content">

                        <?php the_content(); ?>

                    </div>

                 </div>

             </div>

         <?php endif; ?>

         <?php
             if( have_rows('layouts') ):

                 while ( have_rows('layouts') ) : the_row();

                    include 'template_parts/layouts/'.get_row_layout().'.php';


                 endwhile;

             endif;
         ?>

    </section>

 <?php endwhile; ?>

 <?php wp_reset_postdata(); ?>

</main>

<?php get_footer(); ?>
