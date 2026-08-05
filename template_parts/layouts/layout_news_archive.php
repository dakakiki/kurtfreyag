<?php
/**
 * Layout: layout_news_archive
 *
 * XD reference (News artboard, 1440 x 3185):
 *   years    pills 180 x 45, #EDF6FC, radius 100, 16 apart, at y863
 *   heading  Lato Bold 53 #00529E, left aligned, baseline at y1011
 *   icon     to the right, aligned with the grid's right edge
 *   grid     three columns of 376 x 464 cards, 16 apart
 *   media    376 x 297
 *
 * The page hero above this block is its own layout row, so nothing here
 * renders a header. Posts come from kfa_* helpers in inc/news.php.
 *
 * The XD has no load more control - it shows one full page of nine and a
 * scroll to top button. The control below is an addition, so it follows the
 * hero button styling rather than inventing a new one.
 *
 * Animation: the cards carry fade-up individually rather than sitting in a
 * group-fade-up grid. initFadeUpAnimations() drops any single whose ancestor
 * carries the group class, so grouped cards appended by AJAX would be marked
 * hidden by the CSS and never animated back in.
 */

$archive_row    = get_row_index();
$archive_title  = get_sub_field( 'title' );
$archive_icon   = get_sub_field( 'icon' );
$archive_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

$archive_years = kfa_get_news_years();
$archive_year  = kfa_get_current_news_year();
$archive_page  = kfa_get_current_news_page();

/*
 * Server side the list is cumulative: page two renders eighteen posts, not
 * the second nine. That way the load more link is literally true without
 * JavaScript, and a crawler following it sees everything above it too.
 */
$archive_query = kfa_get_news_archive_initial_query( $archive_page, $archive_year );

$archive_shown = KFA_NEWS_ARCHIVE_PER_PAGE * $archive_page;
$archive_more  = (int) $archive_query->found_posts > $archive_shown;
?>

<section
	<?php if ( $archive_anchor ) : ?>id="<?= esc_attr( $archive_anchor ); ?>"<?php endif; ?>
	class="layout-news-archive"
>

	<?php
	/*
	 * The AJAX config lives on the block rather than on one control, because
	 * both the year filter and the load more button need it - and because a
	 * data attribute does not care what hook priority anything ran at.
	 */
	?>
	<div
		class="news-archive"
		id="lyt-<?= (int) $archive_row; ?>"
		data-news-block
		data-ajax-url="<?= esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
		data-action="<?= esc_attr( KFA_NEWS_AJAX_ACTION ); ?>"
		data-nonce="<?= esc_attr( wp_create_nonce( KFA_NEWS_AJAX_ACTION ) ); ?>"
		data-year="<?= (int) $archive_year; ?>"
		data-page="<?= (int) $archive_page; ?>"
	>

		<div class="news-archive__container">

			<?php if ( ! empty( $archive_years ) ) : ?>

				<nav class="news-archive__years fade-up" aria-label="<?php esc_attr_e( 'Nach Jahr filtern', 'KurtFreyAG' ); ?>">

					<button
						type="button"
						class="news-archive__year<?= $archive_year ? '' : ' is-active'; ?>"
						data-news-year-btn
						data-year="0"
						aria-pressed="<?= $archive_year ? 'false' : 'true'; ?>"
					><?php esc_html_e( 'Alle', 'KurtFreyAG' ); ?></button>

					<?php foreach ( $archive_years as $year ) : ?>
						<button
							type="button"
							class="news-archive__year<?= $archive_year === $year ? ' is-active' : ''; ?>"
							data-news-year-btn
							data-year="<?= (int) $year; ?>"
							aria-pressed="<?= $archive_year === $year ? 'true' : 'false'; ?>"
						><?= esc_html( $year ); ?></button>
					<?php endforeach; ?>

				</nav>

				<?php
				/*
				 * Below 991 the pills would wrap into three or four rows, so
				 * the same choices are offered as a select. Both are in the
				 * DOM and CSS shows one of them - a native select is the
				 * right control on a phone and needs no custom keyboard work.
				 */
				?>
				<div class="news-archive__years-select fade-up">

					<label class="news-archive__years-label" for="news-year-<?= (int) $archive_row; ?>">
						<?php esc_html_e( 'Nach Jahr filtern', 'KurtFreyAG' ); ?>
					</label>

					<select id="news-year-<?= (int) $archive_row; ?>" data-news-year-select>

						<option value="0" <?php selected( $archive_year, 0 ); ?>><?php esc_html_e( 'Alle', 'KurtFreyAG' ); ?></option>

						<?php foreach ( $archive_years as $year ) : ?>
							<option value="<?= (int) $year; ?>" <?php selected( $archive_year, $year ); ?>><?= esc_html( $year ); ?></option>
						<?php endforeach; ?>

					</select>

				</div>

			<?php endif; ?>

			<?php if ( $archive_title || $archive_icon ) : ?>

				<div class="news-archive__head group-fade-up">

					<?php if ( $archive_title ) : ?>
						<h2 class="news-archive__title fade-up"><?= esc_html( $archive_title ); ?></h2>
					<?php endif; ?>

					<?php if ( ! empty( $archive_icon ) ) : ?>
						<div class="news-archive__icon fade-up" aria-hidden="true">
							<?= theme_acf_image( $archive_icon, 'medium', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
						</div>
					<?php endif; ?>

				</div>

			<?php endif; ?>

			<div class="news-archive__grid" data-news-grid>

				<?php if ( $archive_query->post_count ) : ?>
					<?php while ( $archive_query->have_posts() ) : ?>
						<?php $archive_query->the_post(); ?>
						<?php get_template_part( 'template_parts/news/card', null, array(
							'class'   => 'news-card--archive fade-up',
							'heading' => 'h3',
							'sizes'   => '(max-width: 640px) 88vw, (max-width: 991px) 46vw, 376px',
						) ); ?>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php endif; ?>

			</div>

			<p class="news-archive__empty" data-news-empty <?= $archive_query->post_count ? 'hidden' : ''; ?>>
				<?php esc_html_e( 'Für diesen Zeitraum sind keine Beiträge vorhanden.', 'KurtFreyAG' ); ?>
			</p>

			<div class="news-archive__more fade-up" data-news-more-wrap <?= $archive_more ? '' : 'hidden'; ?>>

				<button
					type="button"
					class="news-archive__more-btn"
					data-news-more
					data-label-more="<?php esc_attr_e( 'Mehr laden', 'KurtFreyAG' ); ?>"
					data-label-loading="<?php esc_attr_e( 'Wird geladen …', 'KurtFreyAG' ); ?>"
					data-label-error="<?php esc_attr_e( 'Laden fehlgeschlagen. Bitte erneut versuchen.', 'KurtFreyAG' ); ?>"
				><?php esc_html_e( 'Mehr laden', 'KurtFreyAG' ); ?></button>

			</div>

			<p class="news-archive__status" role="status" aria-live="polite"></p>

		</div>

	</div>

</section>