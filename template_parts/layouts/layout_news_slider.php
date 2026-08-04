<?php
/**
 * Layout: layout_news_slider
 *
 * XD composition (homepage, 1440 artboard):
 *   frame   1160 wide, 3px #00529E border, 60px radius, no fill
 *   icon    114 x 104, sits on the top border, right edge on the cards
 *   title   Lato Bold 53 #00529E, centred
 *   cards   310 x 464, #EDF6FC, 3px radius, 33px apart, three visible
 *   image   310 x 309, rounded on the top corners only
 *   meta    date Lato Regular 16, title Lato Bold 25, both #00529E
 *
 * The card markup lives in template_parts/news/card.php, and its styles in
 * components/_news-card.scss, because the archive will reuse both.
 *   dots    21px circles, 12px apart, active #00529E, rest #A7D8F4
 *
 * Content comes from the news CPT - the layout row only carries the icon,
 * the heading and the usual style/anchor fields. The posts themselves are
 * fetched by kfa_get_news_slider_query() in inc/news.php.
 */

$news_row    = get_row_index();
$news_icon   = get_sub_field( 'icon' );
$news_title  = get_sub_field( 'title' );
$news_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

/*
 * The query lives in inc/news.php so the archive and any related-posts block
 * can reuse it. Ten posts by default, filterable via kfa_news_slider_count.
 */
$news_query = kfa_get_news_slider_query();

/* Nothing to show: skip the frame entirely rather than print an empty box. */
if ( ! $news_query->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>

<section
	<?php if ( $news_anchor ) : ?>id="<?= esc_attr( $news_anchor ); ?>"<?php endif; ?>
	class="layout-news-slider"
>

	<div class="news" id="lyt-<?= (int) $news_row; ?>">

		<div class="news__frame group-fade-up">

			<?php if ( ! empty( $news_icon ) ) : ?>
				<div class="news__icon" aria-hidden="true">
					<?= theme_acf_image( $news_icon, 'medium', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $news_title ) : ?>
				<h2 class="news__title fade-up"><?= esc_html( $news_title ); ?></h2>
			<?php endif; ?>

			<div class="news__slides fade-up">

				<?php while ( $news_query->have_posts() ) : ?>
					<?php $news_query->the_post(); ?>

					<?php
					get_template_part( 'template_parts/news/card', null, array(
						'class' => 'news__slide',
					) );
					?>

				<?php endwhile; ?>

			</div>

		</div>

	</div>

</section>

<?php wp_reset_postdata(); ?>