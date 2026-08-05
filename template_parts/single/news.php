<?php
/**
 * Single: news
 *
 * XD reference (News - 2 artboard, 1440 x 3303):
 *   hero      the same page hero as the archive page
 *   back      pill 180 x 45, #EDF6FC, radius 100, right aligned on x1300
 *   title     Lato Bold 53 #00529E at x140
 *   date      Lato Regular 16 #00529E
 *   body      Lato Regular 16 #00529E
 *   image     1161 x 663, 60px radius on every corner
 *   related   blue bar 1160 x 45 radius 3, then three cards and slider dots
 *
 * The hero is pulled from whichever page hosts layout_news_archive, because a
 * post can just as easily be opened from the homepage slider - see
 * kfa_render_news_hero() in inc/news.php.
 *
 * @package KurtFreyAG
 */

$single_id      = get_the_ID();
$single_back    = kfa_news_archive_link();
$single_related = kfa_get_news_query( 9, array( 'post__not_in' => array( $single_id ) ) );

/** Heading on the blue bar above the related posts. */
$single_related_title = (string) apply_filters(
	'kfa_news_related_title',
	__( 'Weitere Beiträge', 'KurtFreyAG' )
);
?>

<?php
/*
 * Wrapped so the shared hero can be forced to full height here without
 * touching layout_page_hero or the archive page's own row - see
 * .single-news__hero in pages/_single_news.scss.
 */
?>
<div class="single-news__hero">
	<?php kfa_render_news_hero(); ?>
</div>

<article <?php post_class( 'single-news' ); ?> id="post-<?php the_ID(); ?>">

	<?php
	/*
	 * The vertical rhythm belongs to the body, not to the article. post_class()
	 * puts single-news on the article itself, so padding there would also push
	 * anything else the article ever holds - the hero included.
	 */
	?>
	<div class="single-news__body">

		<div class="single-news__container">

			<?php
			/*
			 * XD: the pill sits on the same line as the title, its top edge level
			 * with the first line, right aligned on x1300.
			 *
			 * A div, not a <header>: _header.scss styles the bare `header`
			 * element as the fixed site bar, so a semantic header here would be
			 * pulled to the top of the viewport at 100px tall.
			 */
			?>
			<div class="single-news__head group-fade-up">

				<div class="single-news__head-main">

					<h1 class="single-news__title fade-up"><?php the_title(); ?></h1>

					<time class="single-news__date fade-up" datetime="<?= esc_attr( get_the_date( 'c' ) ); ?>">
						<?= esc_html( get_the_date( 'd.m.Y' ) ); ?>
					</time>

				</div>

				<?php if ( $single_back ) : ?>
					<a class="single-news__back-btn fade-up" href="<?= esc_url( $single_back ); ?>">
						<?php esc_html_e( 'Zur Übersicht', 'KurtFreyAG' ); ?>
					</a>
				<?php endif; ?>

			</div>

			<?php if ( get_the_content() ) : ?>
				<div class="single-news__content fade-up">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="single-news__media fade-up">
					<?php the_post_thumbnail( 'full', array(
						'loading' => 'lazy',
						'sizes'   => '(max-width: 991px) 100vw, 1160px',
					) ); ?>

					<?php if ( $caption = get_the_post_thumbnail_caption() ) : ?>
						<figcaption class="single-news__caption"><?= esc_html( $caption ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>

		</div>

	</div>

	<?php if ( $single_related->have_posts() ) : ?>

		<section class="single-news__related">

			<div class="single-news__container">

				<h2 class="single-news__related-title fade-up"><?= esc_html( $single_related_title ); ?></h2>

				<?php
				/*
				 * news__slides is what layout_news_slider.js initialises. The
				 * class carries no styling of its own - the slider's looks are
				 * scoped under .layout-news-slider - so it only wires up Slick.
				 */
				?>
				<div class="single-news__related-slides news__slides fade-up">

					<?php while ( $single_related->have_posts() ) : ?>
						<?php $single_related->the_post(); ?>
						<?php get_template_part( 'template_parts/news/card', null, array(
							'class'   => 'news-card--archive',
							'heading' => 'h3',
							'sizes'   => '(max-width: 640px) 88vw, (max-width: 991px) 46vw, 376px',
						) ); ?>
					<?php endwhile; ?>

				</div>

			</div>

		</section>

		<?php wp_reset_postdata(); ?>

	<?php endif; ?>

</article>