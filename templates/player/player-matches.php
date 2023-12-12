<?php
/**
 * The Template for displaying Player >> Matches Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/player/player-matches.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.8.3
 *
 * @version       0.14.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'player_id'           => '',
		'current_season_id'   => '',
		'competition_matches' => [],
		'position_code'       => '',
		'series_map'          => [],
		'card_icons'          => [],
		'header'              => true,
	]
);

if ( empty( $data->competition_matches ) ) {
	return;
}

$default_club_logo = anwp_football_leagues()->helper->get_default_club_logo();

// Prepare wrapper classes
$col_span = 'g' === $data->position_code ? 8 : 9;
?>
<div class="player-matches anwp-section anwp-b-wrap">
	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'player__matches__latest_matches', __( 'Latest Matches', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<div class="player-matches__wrapper anwp-grid-table anwp-grid-table--aligned anwp-grid-table--bordered anwp-text-base anwp-border-light"
		style="--player-matches-cols: <?php echo absint( $col_span - 2 ); ?>; --player-matches-sm-cols: <?php echo absint( absint( $col_span - 3 ) ); ?>">

		<div class="anwp-grid-table__th anwp-bg-white anwp-border-light player-matches__date"><?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__date', __( 'Date', 'anwp-football-leagues' ) ) ); ?></div>
		<div class="anwp-grid-table__th anwp-bg-white anwp-grid-table__sm-none"><?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__for', __( 'For', 'anwp-football-leagues' ) ) ); ?></div>
		<div class="anwp-grid-table__th anwp-bg-white anwp-grid-table__sm-none"><?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__against', __( 'Against', 'anwp-football-leagues' ) ) ); ?></div>
		<div class="anwp-grid-table__th anwp-bg-white anwp-grid-table__sm-none anwp-text-center"><?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__home_away', _x( 'H/A', 'Home / Away - short', 'anwp-football-leagues' ) ) ); ?></div>
		<div class="anwp-grid-table__th anwp-bg-white player-matches__th-result"><?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__result', __( 'Result', 'anwp-football-leagues' ) ) ); ?></div>
		<div class="anwp-grid-table__th anwp-bg-white anwp-text-center" data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__minutes', __( 'Minutes', 'anwp-football-leagues' ) ) ); ?>">
			<svg class="anwp-icon--s20 anwp-icon--gray-900">
				<use xlink:href="#icon-watch"></use>
			</svg>
		</div>

		<?php if ( 'g' === $data->position_code ) : ?>
			<div class="anwp-grid-table__th anwp-bg-white anwp-text-center" data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__goals_conceded', __( 'Goals Conceded', 'anwp-football-leagues' ) ) ); ?>">
				<svg class="icon__ball icon__ball--conceded">
					<use xlink:href="#icon-ball"></use>
				</svg>
			</div>
		<?php else : ?>
			<div class="anwp-grid-table__th anwp-bg-white anwp-text-center" data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__goals', __( 'Goals', 'anwp-football-leagues' ) ) ); ?>">
				<svg class="icon__ball anwp-icon--stats-goal">
					<use xlink:href="#icon-ball"></use>
				</svg>
			</div>
			<div class="anwp-grid-table__th anwp-bg-white anwp-text-center" data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__assists', __( 'Assists', 'anwp-football-leagues' ) ) ); ?>">
				<svg class="icon__ball anwp-opacity-50">
					<use xlink:href="#icon-ball"></use>
				</svg>
			</div>
		<?php endif; ?>

		<div class="anwp-grid-table__th anwp-bg-white anwp-text-center" data-toggle=" anwp-tooltip" data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__cards', __( 'Cards', 'anwp-football-leagues' ) ) ); ?>">
			<svg class="icon__card">
				<use xlink:href="#icon-card_yr"></use>
			</svg>
		</div>

		<?php foreach ( $data->competition_matches as $ii => $competition ) : ?>
			<div class="anwp-grid-table__td player-matches__competition anwp-bg-light anwp-text-base anwp-grid-table__sm-mt d-flex flex-nowrap align-items-center">
				<?php if ( $competition['logo'] ) : ?>
					<img loading="lazy" width="30" height="30" class="mr-2 anwp-object-contain anwp-w-30 anwp-h-30" src="<?php echo esc_url( $competition['logo'] ); ?>" alt="<?php echo esc_html( $competition['title'] ); ?>">
				<?php endif; ?>
				<div><?php echo esc_html( $competition['title'] ); ?></div>
			</div>
			<?php
			if ( ! empty( $competition['matches'] ) && is_array( $competition['matches'] ) ) :
				foreach ( $competition['matches'] as $match ) :

					$club_logo  = anwp_football_leagues()->club->get_club_logo_by_id( $match->club_id ) ?: $default_club_logo;
					$club_title = anwp_football_leagues()->club->get_club_title_by_id( $match->club_id );

					// Opponent
					$opponent_id    = $match->club_id === $match->home_club ? $match->away_club : $match->home_club;
					$opponent_title = 'full' === anwp_football_leagues()->customizer->get_value( 'player', 'player_opposite_club_name' ) ? anwp_football_leagues()->club->get_club_title_by_id( $opponent_id ) : anwp_football_leagues()->club->get_club_abbr_by_id( $opponent_id );
					$opponent_logo  = anwp_football_leagues()->club->get_club_logo_by_id( $opponent_id );

					$home_away = $match->club_id === $match->home_club ? esc_html( AnWPFL_Text::get_value( 'player__matches__home', __( 'Home', 'anwp-football-leagues' ) ) ) : esc_html( AnWPFL_Text::get_value( 'player__matches__away', __( 'Away', 'anwp-football-leagues' ) ) );
					$home_club = anwp_football_leagues()->club->get_club( $match->home_club ) ?: $default_club_logo;
					$away_club = anwp_football_leagues()->club->get_club( $match->away_club ) ?: $default_club_logo;

					$result_class = 'anwp-bg-success';
					$result_code  = 'w';

					if ( $match->home_goals === $match->away_goals ) {
						$result_class = 'anwp-bg-warning';
						$result_code  = 'd';
					} elseif ( ( $match->club_id === $match->home_club && $match->home_goals < $match->away_goals ) || ( $match->club_id === $match->away_club && $match->home_goals > $match->away_goals ) ) {
						$result_class = 'anwp-bg-danger';
						$result_code  = 'l';
					}

					// Card Type
					$card_type = intval( $match->card_r ) ? 'r' : ( intval( $match->card_yr ) ? 'yr' : ( intval( $match->card_y ) ? 'y' : '' ) );
					?>
					<div class="anwp-grid-table__td player-matches__date anwp-grid-table__sm-mt" data-fl-game-datetime="<?php echo esc_attr( date_i18n( 'c', strtotime( $match->kickoff ) ) ); ?>">
						<?php if ( '0000-00-00 00:00:00' !== $match->kickoff ) : ?>
							<a class="anwp-link-without-effects anwp-text-nowrap match__date-formatted" href="<?php echo esc_url( $match->link ); ?>">
								<?php echo esc_html( date_i18n( anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M Y', strtotime( $match->kickoff ) ) ); ?>
							</a>
						<?php endif; ?>

						<div class="d-none anwp-grid-table__sm-block player-matches__date-teams">
							<img loading="lazy" width="30" height="30" class="anwp-object-contain mr-3 anwp-w-30 anwp-h-30"
								data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $home_club->title ); ?>"
								src="<?php echo esc_url( $home_club->logo ); ?>" alt="<?php echo esc_attr( $home_club->title ); ?>">
							<img loading="lazy" width="30" height="30" class="anwp-object-contain m-0 ml-auto ml-3 anwp-w-30 anwp-h-30"
								data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $away_club->title ); ?>"
								src="<?php echo esc_url( $away_club->logo ); ?>" alt="<?php echo esc_attr( $away_club->title ); ?>">
						</div>

						<div class="d-none anwp-grid-table__sm-block anwp-text-sm anwp-leading-1 anwp-opacity-80">
							<?php echo esc_attr( $home_away ); ?>
						</div>
					</div>
					<div class="anwp-grid-table__td player-matches__for anwp-box-content anwp-grid-table__sm-none">
						<img loading="lazy" width="30" height="30" class="anwp-object-contain m-0 anwp-w-30 anwp-h-30"
							data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
							src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
					</div>
					<div class="anwp-grid-table__td player-matches__opponent d-flex align-items-center anwp-overflow-x-hidden anwp-grid-table__sm-none">
						<?php if ( $opponent_logo ) : ?>
							<img loading="lazy"  width="30" height="30" class="player-matches__opponent-logo anwp-object-contain m-0 anwp-w-30 anwp-h-30"
								data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $opponent_title ); ?>"
								src="<?php echo esc_url( $opponent_logo ); ?>" alt="<?php echo esc_attr( $opponent_title ); ?>"/>
						<?php endif; ?>
						<span class="player-matches__opponent-title ml-2 anwp-text-sm"><?php echo esc_html( $opponent_title ); ?></span>
					</div>
					<div class="anwp-grid-table__td player-matches__home-away anwp-font-bold anwp-grid-table__sm-none"
						data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $home_away ); ?>">
						<?php echo esc_html( mb_substr( $home_away, 0, 1 ) ); ?>
					</div>
					<div class="anwp-grid-table__td anwp-text-nowrap player-matches__result">
						<span class="anwp-text-white <?php echo esc_attr( $result_class ); ?> d-inline-block anwp-w-40 anwp-text-center mr-2"><?php echo esc_html( mb_strtoupper( $data->series_map[ $result_code ] ) ); ?></span>
						<span class=""><?php echo (int) $match->home_goals; ?>:<?php echo (int) $match->away_goals; ?></span>
					</div>
					<div class="anwp-grid-table__td anwp-text-center player-matches__minutes">
						<?php
						$minutes = $match->time_out - $match->time_in;

						// Fix minutes after half time substitution (1 min correction)
						// @since v0.6.5 (2018-08-17)
						if ( 46 === intval( $match->time_out ) ) {
							$minutes = $match->time_out - $match->time_in - 1;
						} elseif ( 46 === intval( $match->time_in ) ) {
							$minutes = $match->time_out - $match->time_in + 1;
						}

						echo intval( $minutes ) . '′';
						?>
					</div>

					<?php if ( 'g' === $data->position_code ) : ?>
						<div class="player-matches__goals_conceded anwp-grid-table__td anwp-text-center"><?php echo (int) $match->goals_conceded; ?></div>
					<?php else : ?>
						<div class="player-matches__goals anwp-grid-table__td anwp-text-center">
							<?php echo (int) $match->goals; ?>
						</div>
						<div class="player-matches__assists anwp-grid-table__td anwp-text-center">
							<?php echo (int) $match->assist; ?>
						</div>
					<?php endif; ?>

					<div class="anwp-grid-table__td anwp-text-center">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo 'r' === $card_type && intval( $match->card_y ) ? $data->card_icons['y'] : '';

						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $card_type ? $data->card_icons[ $card_type ] : '';
						?>
					</div>
					<?php
				endforeach;
			endif;
		endforeach;
		?>
	</div>
</div>
