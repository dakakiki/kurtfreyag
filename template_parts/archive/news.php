<?php
/**
 * Archive: news
 *
 * XD reference (News artboard, 1440 x 3185):
 *   hero      full page hero, title Lato Bold 53 white at x140, lead 16
 *   years     pills 180 x 45, #EDF6FC, radius 100, 16 apart, at y863
 *   heading   Lato Bold 53 #00529E, left aligned, at y1011
 *   icon      news icon on the right, aligned to the grid's right edge
 *   grid      three columns of 376 x 464 cards, 16 apart
 *   media     376 x 297
 *
 * The XD has no load more control - it shows a full page of nine and a scroll
 * to top button. The control below is an addition, so it follows the hero
 * button styling rather than inventing a new one.
 *
 * @package KurtFreyAG
 */

$news_years   = kfa_get_news_years();
$news_year    = kfa_get_current_news_year();
$news_paged   = max( 1, (int) get_query_var( 'paged' ) );
$news_more    = $news_paged < (int) $GLOBALS['wp_query']->max_num_pages;
$news_next    = get_next_posts_page_link();

/**
 * Hero image for the news archive.
 *
 * There is no ACF row behind an archive, so the image is supplied in code.
 * Returns an attachment ID, or 0 to render the hero without an image.
 *
 * @param int $attachment_id Attachment ID.
 */
$news_hero_image = (int) apply_filters( 'kfa_news_archive_hero_image', 0 );

/** Heading and lead, both overridable without touching the template. */
$news_hero_title = (string) apply_filters( 'kfa_news_archive_title', __( 'News & Beiträge', 'KurtFreyAG' ) );
$news_hero_lead  = (string) apply_filters( 'kfa_news_archive_lead', __( 'Technische Tipps, Unternehmensnews, Innovationen und Wissenswertes rund um Gebäudetechnik.', 'KurtFreyAG' ) );
$news_heading    = (string) apply_filters( 'kfa_news_archive_heading', __( 'Aktuelles von der Kurt Frey AG', 'KurtFreyAG' ) );

/** Decorative icon above the heading. Attachment ID, 0 to omit. */
$news_icon = (int) apply_filters( 'kfa_news_archive_icon', 0 );
?>

<main id="main" class="news-archive">

	<section class="layout-page-hero h-page">

		<div class="page-hero<?= $news_hero_image ? '' : ' page-hero--flat'; ?>">

			<?php if ( $news_hero_image ) : ?>
				<div class="page-hero__image">
					<?= wp_get_attachment_image( $news_hero_image, 'full', false, array(
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'sizes'         => '(max-width: 991px) 100vw, 75vw',
					) ); ?>
				</div>
			<?php endif; ?>

			<div class="page-hero__overlay" aria-hidden="true"></div>

			<div class="page-hero__content">

				<div class="page-hero__wrap group-fade-up">

					<h1 class="fade-up"><?= esc_html( $news_hero_title ); ?></h1>

					<?php if ( $news_hero_lead ) : ?>
						<div class="page-hero__wrap--text fade-up"><p><?= esc_html( $news_hero_lead ); ?></p></div>
					<?php endif; ?>

				</div>

			</div>

		</div>

	</section>

	<div class="news-archive__body">

		<div class="news-archive__container">

			<?php if ( ! empty( $news_years ) ) : ?>

				<nav class="news-archive__years" aria-label="<?php esc_attr_e( 'Nach Jahr filtern', 'KurtFreyAG' ); ?>">

					<a
						class="news-archive__year<?= $news_year ? '' : ' is-active'; ?>"
						href="<?= esc_url( kfa_news_year_url() ); ?>"
						<?= $news_year ? '' : 'aria-current="page"'; ?>
					><?php esc_html_e( 'Alle', 'KurtFreyAG' ); ?></a>

					<?php foreach ( $news_years as $year ) : ?>
						<a
							class="news-archive__year<?= $news_year === $year ? ' is-active' : ''; ?>"
							href="<?= esc_url( kfa_news_year_url( $year ) ); ?>"
							<?= $news_year === $year ? 'aria-current="page"' : ''; ?>
						><?= esc_html( $year ); ?></a>
					<?php endforeach; ?>

				</nav>

			<?php endif; ?>

			<div class="news-archive__head">

				<h2 class="news-archive__title"><?= esc_html( $news_heading ); ?></h2>

				<?php if ( $news_icon ) : ?>
					<div class="news-archive__icon" aria-hidden="true">
						<?= wp_get_attachment_image( $news_icon, 'medium', false, array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
					</div>
				<?php endif; ?>

			</div>

			<?php if ( have_posts() ) : ?>

				<div class="news-archive__grid" data-news-grid>

					<?php while ( have_posts() ) : ?>
						<?php the_post(); ?>
						<?php get_template_part( 'template_parts/news/card', null, array(
							'class'   => 'news-card--archive',
							'heading' => 'h3',
							'sizes'   => '(max-width: 640px) 88vw, (max-width: 991px) 46vw, 376px',
						) ); ?>
					<?php endwhile; ?>

				</div>

				<?php if ( $news_more && $news_next ) : ?>

					<div class="news-archive__more">

						<?php
						/*
						 * A real link to page two, so the archive is fully
						 * browsable without JavaScript. news.js intercepts the
						 * click and appends the cards instead.
						 */
						?>
						<a
							class="news-archive__more-btn b-dark"
							href="<?= esc_url( $news_next ); ?>"
							data-news-more
							data-page="<?= (int) $news_paged; ?>"
							data-year="<?= (int) $news_year; ?>"
						><?php esc_html_e( 'Mehr laden', 'KurtFreyAG' ); ?></a>

						<p class="news-archive__status" role="status" aria-live="polite"></p>

					</div>

				<?php endif; ?>

			<?php else : ?>

				<p class="news-archive__empty">
					<?php esc_html_e( 'Für diesen Zeitraum sind keine Beiträge vorhanden.', 'KurtFreyAG' ); ?>
				</p>

			<?php endif; ?>

		</div>

	</div>

</main>