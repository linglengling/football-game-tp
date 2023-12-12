<?php
/**
 * The Template for displaying Player >> Missed Games Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/player/player-missed.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.11.4
 *
 * @version       0.14.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'player_id'         => '',
		'current_season_id' => '',
		'position_code'     => '',
		'series_map'        => [],
		'card_icons'        => [],
		'header'            => true,
	]
);

$missed_games = anwp_football_leagues()->match->get_player_missed_games_by_season( $data->player_id, $data->current_season_id );

if ( empty( $missed_games ) ) {
	return;
}

$default_club_logo = anwp_football_leagues()->helper->get_default_club_logo();
?>
<div class="player-missed anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'player__missed__missed_matches', __( 'Missed Matches', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<div class="player-missed__wrapper anwp-grid-table anwp-grid-table--aligned anwp-grid-table--bordered anwp-text-base anwp-border-light anwp-overflow-x-auto">

		<div class="anwp-grid-table__th">
			<?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__date', __( 'Date', 'anwp-football-leagues' ) ) ); ?>
		</div>
		<div class="anwp-grid-table__th anwp-grid-table__sm-none">
			<?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__for', __( 'For', 'anwp-football-leagues' ) ) ); ?>
		</div>
		<div class="anwp-grid-table__th anwp-grid-table__sm-none">
			<?php echo esc_html( AnWPFL_Text::get_value( 'player__matches__against', __( 'Against', 'anwp-football-leagues' ) ) ); ?>
		</div>
		<div class="anwp-grid-table__th">
			<?php echo esc_html( AnWPFL_Text::get_value( 'player__missed__reason', __( 'Reason', 'anwp-football-leagues' ) ) ); ?>
		</div>

		<?php foreach ( $missed_games as $ii => $competition ) : ?>
			<div class="anwp-grid-table__td player-missed__competition anwp-bg-light anwp-text-base d-flex flex-nowrap align-items-center">
				<?php if ( $competition['logo'] ) : ?>
					<img loading="lazy" width="30" height="30" class="mr-2 anwp-object-contain anwp-w-30 anwp-h-30" src="<?php echo esc_url( $competition['logo'] ); ?>" alt="<?php echo esc_html( $competition['title'] ); ?>">
				<?php endif; ?>
				<div><?php echo esc_html( $competition['title'] ); ?></div>
			</div>
			<?php
			if ( ! empty( $competition['matches'] ) && is_array( $competition['matches'] ) ) :
				foreach ( $competition['matches'] as $match ) :

					$home_away = $match->club_id === $match->home_club ? esc_html( AnWPFL_Text::get_value( 'player__matches__home', __( 'Home', 'anwp-football-leagues' ) ) ) : esc_html( AnWPFL_Text::get_value( 'player__matches__away', __( 'Away', 'anwp-football-leagues' ) ) );
					$home_club = anwp_football_leagues()->club->get_club( $match->home_club ) ?: $default_club_logo;
					$away_club = anwp_football_leagues()->club->get_club( $match->away_club ) ?: $default_club_logo;

					$club_logo  = anwp_football_leagues()->club->get_club_logo_by_id( $match->club_id );
					$club_title = anwp_football_leagues()->club->get_club_title_by_id( $match->club_id );
					$match_link = get_permalink( $match->match_id );

					// Opponent
					$opponent_id    = $match->club_id === $match->home_club ? $match->away_club : $match->home_club;
					$opponent_title = 'full' === anwp_football_leagues()->customizer->get_value( 'player', 'player_opposite_club_name' ) ? anwp_football_leagues()->club->get_club_title_by_id( $opponent_id ) : anwp_football_leagues()->club->get_club_abbr_by_id( $opponent_id );
					$opponent_logo  = anwp_football_leagues()->club->get_club_logo_by_id( $opponent_id );
					?>
					<div class="anwp-grid-table__td player-missed__date">
						<?php if ( '0000-00-00 00:00:00' !== $match->kickoff ) : ?>
							<a class="anwp-link-without-effects anwp-text-nowrap match__date-formatted" href="<?php echo esc_url( $match_link ); ?>" data-fl-game-datetime="<?php echo esc_attr( date_i18n( 'c', strtotime( $match->kickoff ) ) ); ?>">
								<?php echo esc_html( date_i18n( anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M Y', strtotime( $match->kickoff ) ) ); ?>
							</a>
						<?php endif; ?>

						<div class="d-none anwp-grid-table__sm-block player-missed__date-teams">
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
					<div class="anwp-grid-table__td player-missed__for anwp-box-content anwp-grid-table__sm-none">
						<?php if ( $club_logo ) : ?>
							<img loading="lazy" width="30" height="30" class="anwp-object-contain m-0 anwp-w-30 anwp-h-30"
								data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $club_title ); ?>"
								src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_attr( $club_title ); ?>">
						<?php else : ?>
							<?php echo esc_html( $club_title ); ?>
						<?php endif; ?>
					</div>
					<div class="anwp-grid-table__td player-missed__opponent d-flex align-items-center anwp-overflow-x-hidden anwp-grid-table__sm-none">
						<?php if ( $opponent_logo ) : ?>
							<img loading="lazy" width="30" height="30" class="player-missed__opponent-logo anwp-object-contain m-0 anwp-w-30 anwp-h-30"
								data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $opponent_title ); ?>"
								src="<?php echo esc_url( $opponent_logo ); ?>" alt="<?php echo esc_attr( $opponent_title ); ?>"/>
						<?php endif; ?>
						<span class="player-missed__opponent-title ml-2 anwp-text-sm"><?php echo esc_html( $opponent_title ); ?></span>
					</div>
					<div class="anwp-grid-table__td player-missed__reason anwp-text-sm">
						<?php if ( 'suspended' === $match->reason ) : ?>
							<?php echo esc_html( AnWPFL_Text::get_value( 'match__missing__suspended', __( 'Suspended', 'anwp-football-leagues' ) ) ); ?>
							<?php echo $match->comment ? ' - ' : ''; ?>
						<?php elseif ( 'injured' === $match->reason ) : ?>
							<?php echo esc_html( AnWPFL_Text::get_value( 'match__missing__injured', __( 'Injured', 'anwp-football-leagues' ) ) ); ?>
							<?php echo $match->comment ? ' - ' : ''; ?>
						<?php endif; ?>
						<?php echo esc_html( $match->comment ); ?>
					</div>
					<?php
				endforeach;
			endif;
		endforeach;
		?>
	</div>
</div>
