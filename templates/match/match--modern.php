<?php
/**
 * The Template for displaying Match (modern version). Used most in widgets.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match--modern.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author           Andrei Strekozov <anwp.pro>
 * @package          AnWP-Football-Leagues/Templates
 * @since            0.7.4
 *
 * @version          0.14.12
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
		'home_club'           => '',
		'away_club'           => '',
		'club_home_abbr'      => '',
		'club_away_abbr'      => '',
		'club_home_logo'      => '',
		'club_away_logo'      => '',
		'club_home_link'      => '',
		'club_away_link'      => '',
		'club_home_title'     => '',
		'match_id'            => '',
		'finished'            => '',
		'home_goals'          => '',
		'away_goals'          => '',
		'extra'               => '',
		'aggtext'             => '',
		'permalink'           => '',
		'special_status'      => '',
		'datetime_tz'         => true,
	]
);
?>

<div class="anwp-fl-game competition__match match-list__item match-list-item match-modern anwp-border-light p-0 position-relative game-status-<?php echo absint( $data->finished ); ?>"
	<?php if ( AnWP_Football_Leagues::string_to_bool( $data->datetime_tz ) ) : ?>
		data-fl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>"
	<?php endif; ?>
	data-anwp-match="<?php echo intval( $data->match_id ); ?>" data-fl-game-kickoff="<?php echo esc_attr( $data->kickoff_orig ); ?>">

	<?php if ( $data->show_match_datetime && '0000-00-00 00:00:00' !== $data->kickoff ) : ?>
		<div class="match-list-item__kickoff match-modern__kickoff anwp-text-xs anwp-text-center px-2 pt-1">
			<span class="match-modern__date match__date-formatted"><?php echo esc_html( $data->match_date ); ?></span>

			<?php if ( 'TBD' !== $data->special_status ) : ?>
				- <span class="match-modern__time match__time-formatted"><?php echo esc_html( $data->match_time ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="d-flex match-modern__inner-wrapper">

		<div class="d-flex flex-column justify-content-around m-1 w-100">

			<div class="match-modern__team-wrapper d-flex align-items-center">

				<?php if ( $data->club_home_logo ) : ?>
					<img loading="lazy" width="25" height="25" class="anwp-object-contain match-modern__team-logo anwp-flex-none mr-2 my-1 anwp-w-25 anwp-h-25"
						src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="<?php echo esc_attr( $data->club_home_title ); ?>">
				<?php endif; ?>

				<div class="match-modern__team flex-grow-1 mr-2 anwp-text-sm">
					<?php echo esc_html( anwp_football_leagues()->customizer->get_value( 'match_list', 'match_modern_team_name' ) ? $data->club_home_title : $data->club_home_abbr ); ?>
				</div>
			</div>

			<div class="match-modern__team-wrapper d-flex align-items-center">

				<?php if ( $data->club_away_logo ) : ?>
					<img loading="lazy" width="25" height="25" class="anwp-object-contain match-modern__team-logo anwp-flex-none mr-2 my-1 anwp-w-25 anwp-h-25"
						src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="<?php echo esc_attr( $data->club_away_title ); ?>">
				<?php endif; ?>

				<div class="match-modern__team flex-grow-1 mr-2 anwp-text-sm">
					<?php echo esc_html( anwp_football_leagues()->customizer->get_value( 'match_list', 'match_modern_team_name' ) ? $data->club_away_title : $data->club_away_abbr ); ?>
				</div>
			</div>
		</div>

		<div class="flex-shrink-1 match-modern__scores d-flex flex-column position-relative m-1">
			<div class="match-list-item__scores-home anwp-fl-game__scores-home match-list-item__scores match-modern__scores-number mt-1 anwp-text-base ml-1 anwp-text-center match-modern__scores-number-status-<?php echo (int) $data->finished; ?>">
				<?php echo (int) $data->finished ? (int) $data->home_goals : '-'; ?>
			</div>

			<div class="match-list-item__scores-away anwp-fl-game__scores-away match-list-item__scores match-modern__scores-number mt-2 anwp-text-base ml-1 anwp-text-center match-modern__scores-number-status-<?php echo (int) $data->finished; ?>">
				<?php echo (int) $data->finished ? (int) $data->away_goals : '-'; ?>
			</div>
		</div>
	</div>

	<?php
	$time_result = '';

	switch ( intval( $data->extra ) ) {
		case 1:
			$time_result = esc_html( AnWPFL_Text::get_value( 'match__match__aet', _x( 'AET', 'Abbr: after extra time', 'anwp-football-leagues' ) ) );
			break;
		case 2:
			$time_result  = esc_html( AnWPFL_Text::get_value( 'match__match__penalties', _x( 'Penalties', 'on penalties', 'anwp-football-leagues' ) ) );
			$time_result .= ' ' . $data->home_goals_p . '-' . $data->away_goals_p;
			break;
	}
	?>
	<?php if ( $time_result || $data->aggtext || in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) : ?>
		<div class="match-modern__time-result-wrapper anwp-text-center anwp-text-xs anwp-opacity-80 anwp-leading-1-25 mb-1">
			<?php
			if ( $time_result ) {
				echo '<span class="match-modern__time-result match-modern__separated">' . esc_html( $time_result ) . '</span>';
			}

			if ( $data->aggtext ) {
				echo '<span class="match-modern__time-result match-modern__separated">' . esc_html( $data->aggtext ) . '</span>';
			}

			if ( in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) {
				echo '<span class="match-modern__time-result match-modern__separated">' . esc_html( anwp_football_leagues()->data->get_value_by_key( $data->special_status, 'special_status' ) ) . '</span>';
			}
			?>
		</div>
	<?php endif; ?>
	<a class="anwp-link-cover anwp-link-without-effects" href="<?php echo esc_url( $data->permalink ); ?>"></a>
</div>
