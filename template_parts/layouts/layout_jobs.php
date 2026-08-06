<?php
/**
 * Layout: layout_jobs
 *
 * XD reference (Jobs artboard, 1440):
 *   columns  vacancies x140 -> 810 (670), side text x924 -> 1300 (376),
 *            114 between them
 *   heading  blue pill 670 x 94, fully rounded, Lato Bold 25 white, centred
 *   vacancy  ice pill 670 wide, 60px radius, 100 tall on one line and 134 on
 *            two, 21 apart
 *   position Lato Bold 25 #00529E, centred
 *   link     "Stellenbeschrieb", Lato Bold 16 #00529E, under the position
 *   side     Lato Bold 25 headings with Lato Regular 16 copy beneath
 *
 * job_description is a file field, so the whole pill opens the PDF - that
 * document is the only thing a vacancy links to.
 */

$jobs_row    = get_row_index();
$jobs_title  = get_sub_field( 'title' );
$jobs_list   = get_sub_field( 'jobs' );
$jobs_side   = get_sub_field( 'side_text' );
$jobs_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

if ( ! $jobs_title && empty( $jobs_list ) && ! $jobs_side ) {
	return;
}
?>

<section
	<?php if ( $jobs_anchor ) : ?>id="<?= esc_attr( $jobs_anchor ); ?>"<?php endif; ?>
	class="layout-jobs"
>

	<div class="jobs" id="lyt-<?= (int) $jobs_row; ?>">

		<div class="jobs__container">

			<?php
			/*
			 * The heading is a grid cell of its own rather than the first thing
			 * inside the list column, which is what lets the side text start
			 * level with the first vacancy - as it does in the XD.
			 */
			?>
			<div class="jobs__grid group-fade-up">

				<?php if ( $jobs_title ) : ?>
					<h2 class="jobs__title fade-up"><?= esc_html( $jobs_title ); ?></h2>
				<?php endif; ?>

				<div class="jobs__col jobs__col--list">

					<?php
					/* Only rows with a position count - an empty repeater row is not a vacancy. */
					$jobs_open = array_filter( (array) $jobs_list, function ( $job ) {
						return ! empty( $job['job_position'] );
					} );
					?>

					<?php if ( ! empty( $jobs_open ) ) : ?>

						<ul class="jobs__list fade-up">

							<?php foreach ( $jobs_open as $job ) : ?>

								<?php
								$position = $job['job_position'] ?? '';
								$file     = $job['job_description'] ?? null;

								if ( ! $position ) {
									continue;
								}

								$file_url = $file['url'] ?? '';
								?>

								<li class="jobs__item">

									<?php if ( $file_url ) : ?>

										<?php
										/*
										 * The document opens in a new tab: it is a download
										 * leaving the site, and a visitor reading the list
										 * should not lose their place in it.
										 */
										?>
										<a
											class="jobs__pill"
											href="<?= esc_url( $file_url ); ?>"
											target="_blank"
											rel="noopener"
										>
											<span class="jobs__position"><?= esc_html( $position ); ?></span>
											<span class="jobs__link"><?php esc_html_e( 'Stellenbeschrieb', 'KurtFreyAG' ); ?></span>
										</a>

									<?php else : ?>

										<div class="jobs__pill jobs__pill--static">
											<span class="jobs__position"><?= esc_html( $position ); ?></span>
										</div>

									<?php endif; ?>

								</li>

							<?php endforeach; ?>

						</ul>

					<?php else : ?>

						<?php
						/*
						 * Saying so beats an empty column: a visitor who came for
						 * the vacancies gets an answer rather than a blank space.
						 */
						?>
						<p class="jobs__empty fade-up">
							<?php esc_html_e( 'Zurzeit sind keine Stellen ausgeschrieben. Initiativbewerbungen sind jederzeit willkommen.', 'KurtFreyAG' ); ?>
						</p>

					<?php endif; ?>

				</div>

				<?php if ( $jobs_side ) : ?>
					<div class="jobs__col jobs__col--side fade-up">
						<div class="jobs__side"><?= wp_kses_post( $jobs_side ); ?></div>
					</div>
				<?php endif; ?>

			</div>

		</div>

	</div>

</section>