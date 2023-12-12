<?php
/**
 * The Template for displaying Match (Card A).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match--card-a.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.8.0
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
		'club_titles'         => true,
		'home_club'           => '',
		'away_club'           => '',
		'club_home_abbr'      => '',
		'club_away_abbr'      => '',
		'club_home_logo'      => '',
		'club_away_logo'      => '',
		'club_home_link'      => '',
		'club_away_link'      => '',
		'match_id'            => '',
		'finished'            => '',
		'home_goals'          => '',
		'away_goals'          => '',
		'permalink'           => '',
		'special_status'      => '',
		'datetime_tz'         => true,
	]
);

$text_stage = $data->stage_title ?: '';
$text_round = anwp_football_leagues()->competition->tmpl_get_matchweek_round_text( $data->match_week, $data->competition_id );
?>
<div class="anwp-fl-game match-card match-card--a py-1 px-2 d-flex flex-column position-relative anwp-w-min-200 anwp-w-200 game-status-<?php echo absint( $data->finished ); ?>"
	<?php if ( AnWP_Football_Leagues::string_to_bool( $data->datetime_tz ) ) : ?>
		data-fl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>" data-fl-date-format="v2"
	<?php endif; ?>
	data-anwp-match="<?php echo intval( $data->match_id ); ?>" data-fl-game-kickoff="<?php echo esc_attr( $data->kickoff_orig ); ?>">

	<div class="match-card__header anwp-text-center anwp-text-xs">
		<div class="match-card__header-item anwp-text-truncate anwp-text-center">
			<?php echo esc_html( anwp_football_leagues()->competition->get_competition( $data->competition_id )->title ); ?>
		</div>
		<div class="match-card__header-item anwp-text-truncate anwp-text-center"><?php echo esc_html( $text_stage ); ?></div>

		<?php if ( $text_stage !== $text_round ) : ?>
			<div class="match-card__header-item anwp-text-truncate anwp-text-center"><?php echo esc_html( $text_round ); ?></div>
		<?php endif; ?>
	</div>

	<div class="d-flex my-1">

		<div class="anwp-flex-1 anwp-text-center anwp-min-width-0 match-card__club-wrapper">
			<?php if ( $data->club_home_logo ) : ?>
				<img loading="lazy" width="45" height="45" class="match-card__club-logo anwp-object-contain match-simple__team-logo anwp-flex-none mx-1 anwp-w-45 anwp-h-45"
					src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="<?php echo esc_attr( $data->club_home_title ); ?>">
			<?php endif; ?>

			<?php if ( AnWP_Football_Leagues::string_to_bool( $data->club_titles ) ) : ?>
				<div class="match-card__club-title anwp-text-xs mt-1">
					<?php echo esc_html( $data->club_home_abbr ); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="anwp-flex-none anwp-text-center d-flex align-items-start match-card__scores pt-2 anwp-leading-1-25 anwp-text-2xl">
			<span class="d-inline-block ml-1 match-card__score anwp-fl-game__scores-home"><?php echo (int) $data->finished ? (int) $data->home_goals : '-'; ?></span>
			<span>:</span>
			<span class="d-inline-block mr-1 match-card__score anwp-fl-game__scores-away"><?php echo (int) $data->finished ? (int) $data->away_goals : '-'; ?></span>
		</div>

		<div class="anwp-flex-1 anwp-text-center anwp-min-width-0 match-card__club-wrapper">
			<?php if ( $data->club_away_logo ) : ?>
				<img loading="lazy" width="45" height="45" class="match-card__club-logo anwp-object-contain match-simple__team-logo anwp-flex-none mx-1 anwp-w-45 anwp-h-45"
					src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="<?php echo esc_attr( $data->club_away_title ); ?>">
			<?php endif; ?>

			<?php if ( AnWP_Football_Leagues::string_to_bool( $data->club_titles ) ) : ?>
				<div class="match-card__club-title anwp-text-xs mt-1">
					<?php echo esc_html( $data->club_away_abbr ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $data->show_match_datetime && '0000-00-00 00:00:00' !== $data->kickoff ) : ?>
		<div class="match-card__footer anwp-bg-light anwp-text-center mt-auto d-flex justify-content-center anwp-text-xs anwp-leading-1-25">
			<?php if ( in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) : ?>
				<span class="match-card__time">
				<?php echo esc_html( anwp_football_leagues()->data->get_value_by_key( $data->special_status, 'special_status' ) ); ?>
				</span>
			<?php else : ?>
				<span class="match-card__date match__date-formatted"><?php echo esc_html( date_i18n( 'j M', strtotime( $data->kickoff ) ) ); ?></span>
				<?php if ( 'TBD' !== $data->special_status ) : ?>
					<span class="mx-1">-</span>
					<span class="match-card__time match__time-formatted"><?php echo esc_html( $data->match_time ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<a class="anwp-link-cover anwp-link-without-effects" href="<?php echo esc_url( $data->permalink ); ?>"></a>
</div>
