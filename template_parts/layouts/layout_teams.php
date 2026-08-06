<?php
/**
 * Layout: layout_teams
 *
 * XD reference (Über Uns "Team", 1440):
 *   band     full width #00529E behind the heading, ending 134 into the
 *            first row of cards
 *   title    Lato Bold 53 white, intro Lato Bold 16 #A7D8F4
 *   group    Lato Bold 25 - white on the band, blue below it
 *   card     278 x 464, #EDF6FC, 3px radius, 16 apart
 *   photo    278 x 277 #E6E6E6, portrait 213 square inside it
 *   name     Lato Bold 20 blue, function and mail Lato Italic 17 blue
 *   hint     98 wide sky strip over the right edge of the row, with a
 *            triangle - the control that moves the slider on
 *
 * The row itself is the member CPT grouped by the teamgruppe taxonomy, so a
 * new group is a new term rather than a code change.
 */

$teams_row    = get_row_index();
$teams_title  = get_sub_field( 'title' );
$teams_text   = get_sub_field( 'text' );
$teams_anchor = sanitize_title( (string) get_sub_field( 'anchor' ) );

/** Post type and taxonomy, filterable so a rename stays a one line change. */
$teams_post_type = (string) apply_filters( 'kfa_teams_post_type', 'mitglied' );
$teams_taxonomy  = (string) apply_filters( 'kfa_teams_taxonomy', 'teamgruppe' );

$teams_groups = get_terms( array(
	'taxonomy'   => $teams_taxonomy,
	'hide_empty' => true,
) );

if ( is_wp_error( $teams_groups ) ) {
	$teams_groups = array();
}

if ( ! $teams_title && ! $teams_text && empty( $teams_groups ) ) {
	return;
}
?>

<section
	<?php if ( $teams_anchor ) : ?>id="<?= esc_attr( $teams_anchor ); ?>"<?php endif; ?>
	class="layout-teams"
>

	<div class="teams" id="lyt-<?= (int) $teams_row; ?>">

		<?php if ( $teams_title || $teams_text ) : ?>

			<div class="teams__head">

				<div class="teams__container">

					<?php if ( $teams_title ) : ?>
						<h2 class="teams__title fade-up"><?= esc_html( $teams_title ); ?></h2>
					<?php endif; ?>

					<?php if ( $teams_text ) : ?>
						<div class="teams__intro fade-up"><?= wp_kses_post( $teams_text ); ?></div>
					<?php endif; ?>

				</div>

			</div>

		<?php endif; ?>

		<?php if ( ! empty( $teams_groups ) ) : ?>

			<div class="teams__container">

				<?php foreach ( $teams_groups as $group ) : ?>

					<?php
					$members = new WP_Query( array(
						'post_type'              => $teams_post_type,
						'post_status'            => 'publish',
						'posts_per_page'         => -1,
						'orderby'                => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
						'ignore_sticky_posts'    => true,
						'no_found_rows'          => true,
						'update_post_term_cache' => false,
						'tax_query'              => array(
							array(
								'taxonomy' => $teams_taxonomy,
								'field'    => 'term_id',
								'terms'    => $group->term_id,
							),
						),
					) );

					if ( ! $members->have_posts() ) {
						wp_reset_postdata();
						continue;
					}
					?>

					<section class="teams__group fade-up">

						<h3 class="teams__group-title"><?= esc_html( $group->name ); ?></h3>

						<div class="teams__row">

							<div class="teams__slides" data-teams-slider>

								<?php while ( $members->have_posts() ) : ?>
									<?php $members->the_post(); ?>

									<?php
									$first = get_field( 't_vorname' );
									$last  = get_field( 't_nachname' );
									$role  = get_field( 't_funktion' );
									$mail  = get_field( 't_email' );

									/* The post title is the fallback when the name fields are empty. */
									$name = trim( (string) $first . ' ' . (string) $last );
									$name = $name !== '' ? $name : get_the_title();
									?>

									<article class="teams__card">

										<div class="teams__photo">
											<?php if ( has_post_thumbnail() ) : ?>
												<?php the_post_thumbnail( 'medium', array(
													'loading' => 'lazy',
													'alt'     => $name,
													'sizes'   => '(max-width: 640px) 80vw, 213px',
												) ); ?>
											<?php endif; ?>
										</div>

										<div class="teams__info">

											<h4 class="teams__name">
												<?php if ( $first || $last ) : ?>
													<span class="teams__first"><?= esc_html( $first ); ?></span>
													<span class="teams__last"><?= esc_html( $last ); ?></span>
												<?php else : ?>
													<?= esc_html( $name ); ?>
												<?php endif; ?>
											</h4>

											<?php if ( $role ) : ?>
												<p class="teams__role"><?= esc_html( $role ); ?></p>
											<?php endif; ?>

											<?php if ( $mail ) : ?>
												<a class="teams__mail" href="<?= esc_url( 'mailto:' . $mail ); ?>">
													<?= antispambot( esc_html( $mail ) ); ?>
												</a>
											<?php endif; ?>

										</div>

									</article>

								<?php endwhile; ?>

							</div>

							<?php
							/*
							 * The hint from the XD, as a real button rather than decoration:
							 * it is the only control on the row, so it has to be reachable.
							 * The triangle points the way the cards travel.
							 */
							?>
							<button
								type="button"
								class="teams__next"
								data-teams-next
								aria-label="<?php esc_attr_e( 'Weitere Mitarbeitende anzeigen', 'KurtFreyAG' ); ?>"
							>
								<img
									class="teams__next-icon"
									src="<?= esc_url( THEME_URI . '/dist/images/slider_arrow.svg' ); ?>"
									width="33"
									height="71"
									alt=""
									aria-hidden="true"
								>
							</button>

						</div>

					</section>

					<?php wp_reset_postdata(); ?>

				<?php endforeach; ?>

			</div>

		<?php endif; ?>

	</div>

</section>