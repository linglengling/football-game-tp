<?php
/**
 * The Template for displaying Match (slim version).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match--slim.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.6.1
 *
 * @version       0.14.12
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'show_match_datetime' => true,
		'kickoff'             => '',
		'kickoff_c'           => '',
		'kickoff_orig'        => '',
		'match_date'          => '',
		'match_time'          => '',
		'club_links'          => true,
		'home_club'           => '',
		'away_club'           => '',
		'club_home_title'     => '',
		'club_away_title'     => '',
		'club_home_logo'      => '',
		'club_away_logo'      => '',
		'club_home_link'      => '',
		'club_away_link'      => '',
		'match_id'            => '',
		'finished'            => '',
		'home_goals'          => '',
		'away_goals'          => '',
		'extra'               => '',
		'aggtext'             => '',
		'permalink'           => '',
		'competition_logo'    => true,
		'outcome_id'          => '',
		'special_status'      => '',
		'extra_actions_html'  => '',
		'load_footer'         => true,
		'datetime_tz'         => true,
	]
);

$competition = anwp_football_leagues()->competition->get_competition( $data->competition_id );

// Wrapper classes
$render_competition = AnWP_Football_Leagues::string_to_bool( $data->competition_logo );
$render_match_time  = $data->show_match_datetime;
?>
<div class="anwp-fl-game match-list__item p-1 p-sm-2 match-slim anwp-border-light position-relative game-status-<?php echo absint( $data->finished ); ?>"
	<?php if ( AnWP_Football_Leagues::string_to_bool( $data->datetime_tz ) ) : ?>
		data-fl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>"
	<?php endif; ?>
	data-anwp-match="<?php echo intval( $data->match_id ); ?>" data-fl-game-kickoff="<?php echo esc_attr( $data->kickoff_orig ); ?>">

	<div class="match-slim__inner-wrapper d-sm-flex">
		<div class="match-slim__main-meta d-flex anwp-text-xs anwp-flex-none anwp-border-light pr-sm-2 mr-sm-2">
			<?php if ( $render_competition ) : ?>
				<div class="match-slim__competition-wrapper d-flex d-sm-block anwp-order-sm-1 anwp-order-3 ml-auto">
					<div class="match-slim__competition-title d-block d-sm-none mr-2 anwp-leading-1">
						<?php echo esc_html( $competition->league_text ); ?>
					</div>

					<?php if ( ! empty( $competition->logo ) ) : ?>
						<img loading="lazy" class="anwp-object-contain anwp-w-20 anwp-h-20 anwp-w-sm-30 anwp-h-sm-30 match-slim__competition-logo anwp-flex-none mr-0" data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $competition->title ); ?>"
							src="<?php echo esc_url( $competition->logo ); ?>" alt="<?php echo esc_attr( $competition->title ); ?>">
					<?php else : ?>
						<div class="anwp-w-15 anwp-w-sm-30 match-slim__competition-logo">&nbsp;</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $data->kickoff && '0000-00-00 00:00:00' !== $data->kickoff ) : ?>
				<div class="match-slim__date-wrapper d-flex flex-sm-column anwp-flex-none anwp-order-2 align-items-start align-items-sm-end anwp-h-min-20">
					<div class="match-slim__date d-flex d-sm-inline-block mr-1 mr-sm-0">
						<svg class="match-slim__date-icon anwp-icon anwp-icon--feather anwp-icon--em-1-2 d-sm-none mr-1">
							<use xlink:href="#icon-clock-alt"></use>
						</svg>
						<span class="match__date-formatted anwp-leading-1"><?php echo esc_html( $data->match_date ); ?></span>
					</div>

					<?php if ( 'TBD' !== $data->special_status && $data->match_time ) : ?>
						<span class="match-slim__time-separator mr-1 d-sm-none anwp-leading-1">-</span>
						<span class="match-slim__time match__time-formatted anwp-leading-1"><?php echo esc_html( $data->match_time ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="match-slim__inner flex-grow-1 pb-1 pb-sm-0">
			<div class="match-slim__main-content d-flex align-items-center">

				<div class="match-slim__team-wrapper match-slim__team-home anwp-flex-1 d-flex flex-column flex-column-reverse flex-sm-row justify-content-center justify-content-sm-end align-items-center">
					<div class="match-slim__team-home-title anwp-text-sm anwp-text-center anwp-text-sm-right anwp-break-word mr-sm-3 anwp-leading-1">
						<?php echo esc_html( $data->club_home_title ); ?>
					</div>
					<?php if ( $data->club_home_logo ) : ?>
						<img loading="lazy" width="30" height="30" class="anwp-object-contain match-slim__team-home-logo anwp-flex-none mr-sm-3 anwp-w-30 anwp-h-30"
							src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="<?php echo esc_attr( $data->club_home_title ); ?>">
					<?php endif; ?>
				</div>

				<div class="match-slim__scores-wrapper d-flex anwp-text-base mt-n3 mt-sm-0">
					<span class="match-slim__scores-number match-slim__scores-home anwp-fl-game__scores-home mr-1 match-slim__scores-number-status-<?php echo (int) $data->finished; ?>">
						<?php echo (int) $data->finished ? (int) $data->home_goals : '-'; ?>
					</span>
					<span class="match-slim__scores-number match-slim__scores-away anwp-fl-game__scores-away match-slim__scores-number-status-<?php echo (int) $data->finished; ?>">
						<?php echo (int) $data->finished ? (int) $data->away_goals : '-'; ?>
					</span>
				</div>

				<div class="match-slim__team-wrapper match-slim__team-away anwp-flex-1 d-flex flex-column flex-sm-row align-items-center justify-content-center justify-content-sm-start">
					<?php if ( $data->club_away_logo ) : ?>
						<img loading="lazy" width="30" height="30" class="anwp-object-contain match-slim__team-away-logo anwp-flex-none ml-sm-3 anwp-w-30 anwp-h-30"
							src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="<?php echo esc_attr( $data->club_away_title ); ?>">
					<?php endif; ?>
					<div class="match-slim__team-away-title anwp-text-sm anwp-text-center anwp-text-sm-left anwp-break-word ml-sm-3 anwp-leading-1">
						<?php echo esc_html( $data->club_away_title ); ?>
					</div>
				</div>
			</div>

			<?php
			if ( AnWP_Football_Leagues::string_to_bool( $data->load_footer ) ) {
				anwp_football_leagues()->load_partial( $data, 'match/match', 'slim-footer' );
			}
			?>
		</div>
	</div>

	<a class="anwp-link-cover anwp-link-without-effects anwp-cursor-pointer" href="<?php echo esc_url( $data->permalink ); ?>"></a>
</div>
