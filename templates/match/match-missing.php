<?php
/**
 * The Template for displaying Match >> Missing Players Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-missing.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.11.4
 *
 * @version       0.14.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'home_club'       => '',
		'match_id'        => '',
		'away_club'       => '',
		'club_home_title' => '',
		'club_away_title' => '',
		'club_home_logo'  => '',
		'club_away_logo'  => '',
		'club_home_link'  => '',
		'club_away_link'  => '',
		'season_id'       => '',
		'header'          => true,
	]
);

$missing_players = anwp_football_leagues()->match->get_game_missed_players( $data->match_id );

if ( empty( $missing_players ) || ! is_array( $missing_players ) ) {
	return;
}

// Prepare squad
$home_squad = anwp_football_leagues()->club->tmpl_prepare_club_squad( $data->home_club, $data->season_id );
$away_squad = anwp_football_leagues()->club->tmpl_prepare_club_squad( $data->away_club, $data->season_id );

$positions = anwp_football_leagues()->data->get_positions_l10n();

$positions_l10n = [
	'g' => anwp_football_leagues()->get_option_value( 'text_abbr_goalkeeper' ) ?: $positions['g'],
	'd' => anwp_football_leagues()->get_option_value( 'text_abbr_defender' ) ?: $positions['d'],
	'm' => anwp_football_leagues()->get_option_value( 'text_abbr_midfielder' ) ?: $positions['m'],
	'f' => anwp_football_leagues()->get_option_value( 'text_abbr_forward' ) ?: $positions['f'],
];

?>
<div class="match-missing anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__missing__missing_players', __( 'Missing Players', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<div class="d-sm-flex match-missing__wrapper">
		<div class="anwp-flex-1 pr-sm-3 match-missing__team-wrapper">
			<?php
			anwp_football_leagues()->load_partial(
				[
					'club_id' => $data->home_club,
					'class'   => 'mb-2',
				],
				'club/club-title'
			);

			/*
			|--------------------------------------------------------------------------
			| Home Club Missing
			|--------------------------------------------------------------------------
			*/
			foreach ( $missing_players as $missing_player ) :

				if ( absint( $missing_player->club ) !== absint( $data->home_club ) ) {
					continue;
				}

				$player_id = absint( $missing_player->player );
				$player    = anwp_football_leagues()->player->get_player( $player_id );

				if ( ! $player ) {
					continue;
				}
				?>
				<div class="match__player-wrapper d-flex flex-wrap align-items-center anwp-fl-border-bottom anwp-border-light anwp-leading-1 anwp-text-base py-1">
					<div class="d-flex align-items-center">
						<div class="match__player-number anwp-bg-light anwp-leading-1-25 mr-2">
							<?php
							$player_number = '';

							if ( isset( $home_squad[ $player_id ] ) && $home_squad[ $player_id ]['number'] ) {
								$player_number = (int) $home_squad[ $player_id ]['number'];
							}

							echo esc_html( $player_number );
							?>
						</div>

						<?php if ( ! empty( $player->nationality ) && is_array( $player->nationality ) ) : ?>
							<div class="match__player-flag mr-2 anwp-leading-1">
								<?php
								foreach ( $player->nationality as $country_code ) :
									anwp_football_leagues()->load_partial(
										[
											'class'        => 'options__flag',
											'size'         => 16,
											'country_code' => $country_code,
										],
										'general/flag'
									);
								endforeach;
								?>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $home_squad[ $player_id ] ) && isset( $positions_l10n[ $home_squad[ $player_id ]['position'] ] ) ) : ?>
							<div class="match__player-position mr-2 anwp-text-nowrap anwp-fl-border anwp-border-light anwp-leading-1-25 anwp-bg-light anwp-text-sm">
								<?php echo esc_html( $positions_l10n[ $home_squad[ $player_id ]['position'] ] ); ?>
							</div>
						<?php endif; ?>

						<div class="match__player-name-wrapper mr-2">
							<a class="anwp-link-without-effects match__player-name" href="<?php echo esc_url( $player->link ); ?>"><?php echo esc_html( $player->name_short ); ?></a>
						</div>
					</div>
					<div class="match-missing__reason ml-auto anwp-text-sm py-1">
						-
						<?php if ( 'suspended' === $missing_player->reason ) : ?>
							<?php echo esc_html( AnWPFL_Text::get_value( 'match__missing__suspended', __( 'Suspended', 'anwp-football-leagues' ) ) ); ?>
							<?php echo $missing_player->comment ? ' - ' : ''; ?>
						<?php elseif ( 'injured' === $missing_player->reason ) : ?>
							<?php echo esc_html( AnWPFL_Text::get_value( 'match__missing__injured', __( 'Injured', 'anwp-football-leagues' ) ) ); ?>
							<?php echo $missing_player->comment ? ' - ' : ''; ?>
						<?php endif; ?>
						<?php echo esc_html( $missing_player->comment ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="anwp-flex-1 pl-sm-3 match-missing__team-wrapper">

			<?php
			anwp_football_leagues()->load_partial(
				[
					'club_id' => $data->away_club,
					'class'   => 'mb-2 mt-4 mt-sm-0',
					'is_home' => false,
				],
				'club/club-title'
			);

			/*
			|--------------------------------------------------------------------------
			| Away Club Missing players
			|--------------------------------------------------------------------------
			*/
			foreach ( $missing_players as $missing_player ) :

				if ( absint( $missing_player->club ) !== absint( $data->away_club ) ) {
					continue;
				}

				$player_id = absint( $missing_player->player );
				$player    = anwp_football_leagues()->player->get_player( $player_id );

				if ( ! $player ) {
					continue;
				}
				?>
				<div class="match__player-wrapper d-flex flex-wrap align-items-center anwp-fl-border-bottom anwp-border-light anwp-leading-1 anwp-text-base py-1">
					<div class="d-flex align-items-center">
						<div class="match__player-number anwp-bg-light anwp-leading-1-25 mr-2">
							<?php
							$player_number = '';

							if ( isset( $away_squad[ $player_id ] ) && $away_squad[ $player_id ]['number'] ) {
								$player_number = (int) $away_squad[ $player_id ]['number'];
							}

							echo esc_html( $player_number );
							?>
						</div>

						<?php if ( ! empty( $player->nationality ) && is_array( $player->nationality ) ) : ?>
							<div class="match__player-flag mr-2 anwp-leading-1">
								<?php
								foreach ( $player->nationality as $country_code ) :
									anwp_football_leagues()->load_partial(
										[
											'class'        => 'options__flag',
											'size'         => 16,
											'country_code' => $country_code,
										],
										'general/flag'
									);
								endforeach;
								?>
							</div>
						<?php endif; ?>


						<?php if ( ! empty( $away_squad[ $player_id ] ) && isset( $positions_l10n[ $away_squad[ $player_id ]['position'] ] ) ) : ?>
							<div class="match__player-position mr-2 anwp-text-nowrap anwp-fl-border anwp-border-light anwp-leading-1-25 anwp-bg-light anwp-text-sm">
								<?php echo esc_html( $positions_l10n[ $away_squad[ $player_id ]['position'] ] ); ?>
							</div>
						<?php endif; ?>

						<div class="match__player-name-wrapper mr-auto ml-1">
							<a class="anwp-link-without-effects match__player-name" href="<?php echo esc_url( $player->link ); ?>"><?php echo esc_html( $player->name_short ); ?></a>
						</div>
					</div>

					<div class="ml-auto anwp-text-sm py-1 match-missing__reason">
						-
						<?php if ( 'suspended' === $missing_player->reason ) : ?>
							<?php echo esc_html( AnWPFL_Text::get_value( 'match__missing__suspended', __( 'Suspended', 'anwp-football-leagues' ) ) ); ?>
							<?php echo $missing_player->comment ? ' - ' : ''; ?>
						<?php elseif ( 'injured' === $missing_player->reason ) : ?>
							<?php echo esc_html( AnWPFL_Text::get_value( 'match__missing__injured', __( 'Injured', 'anwp-football-leagues' ) ) ); ?>
							<?php echo $missing_player->comment ? ' - ' : ''; ?>
						<?php endif; ?>
						<?php echo esc_html( $missing_player->comment ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
