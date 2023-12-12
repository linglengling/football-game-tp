<?php
/**
 * The Template for displaying Match >> Penalty Shootout.
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-penalty_shootout.php.
 *
 * phpcs:disable WordPress.NamingConventions.ValidVariableName
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.6.5
 *
 * @version       0.14.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'home_club' => '',
		'away_club' => '',
		'events'    => [],
		'header'    => true,
	]
);

if ( empty( $data->events['penalty_shootout'] ) ) {
	return '';
}

static $goal_t1 = 0;
static $goal_t2 = 0;

$temp_players = anwp_football_leagues()->match->get_temp_players( $data->match_id );
?>
<div class="match-penalty-shootout anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__penalty_shootout__penalty_shootout', __( 'Penalty Shootout', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}

	foreach ( $data->events['penalty_shootout'] as $e_index => $e ) :

		// Round scores
		if ( $e->club === (int) $data->home_club && 'yes' === $e->scored ) {
			$goal_t1 ++;
		} elseif ( $e->club === (int) $data->away_club && 'yes' === $e->scored ) {
			$goal_t2 ++;
		}
		?>
		<div class="match__event-row py-1 d-flex align-items-center flex-row-reverse flex-sm-row anwp-fl-border-bottom anwp-border-light <?php echo $e_index ? '' : 'anwp-fl-border-top'; ?>">
			<div class="match__event-team-row anwp-flex-1 align-items-center <?php echo $e->club === (int) $data->away_club ? 'd-none d-sm-flex' : 'd-flex'; ?>">

				<?php if ( $e->club === (int) $data->home_club ) : ?>
					<div class="match__event-icon mx-2 anwp-flex-none anwp-leading-1">
						<svg class="icon__ball">
							<use xlink:href="#<?php echo esc_attr( 'yes' === $e->scored ? 'icon-ball_penalty' : 'icon-ball_canceled' ); ?>"></use>
						</svg>
					</div>

					<div class="match__event-content anwp-leading-1-25 anwp-text-base">
						<div class="match__event-type anwp-text-sm anwp-opacity-80 anwp-leading-1">
							<?php
							if ( 'yes' === $e->scored ) {
								echo esc_html( AnWPFL_Text::get_value( 'match__penalty_shootout__scored', _x( 'Scored', 'penalty shootout', 'anwp-football-leagues' ) ) );
							} else {
								echo esc_html( AnWPFL_Text::get_value( 'match__penalty_shootout__missed', _x( 'Missed', 'penalty shootout', 'anwp-football-leagues' ) ) );
							}
							?>
						</div>
						<div class="match__event-player">
							<?php if ( ! empty( $temp_players ) && 'temp__' === mb_substr( $e->player, 0, 6 ) ) : ?>
								<span class="match__event-player-name"><?php echo esc_html( isset( $temp_players[ $e->player ] ) ? $temp_players[ $e->player ]->name : '' ); ?></span>
								<?php
							elseif ( ! empty( $e->player ) ) :
								$player = anwp_football_leagues()->player->get_player( $e->player );
								?>
								<a class="anwp-link-without-effects match__event-player-name" href="<?php echo esc_url( $player->link ); ?>">
									<?php echo esc_html( $player->name_short ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
			<div class="anwp-flex-none">
				<div class="match__event-minute anwp-bg-light anwp-text-lg">
					<?php echo esc_html( $goal_t1 . '-' . $goal_t2 ); ?>
				</div>
			</div>

			<div class="match__event-team-row anwp-flex-1 align-items-center flex-row-reverse flex-sm-row <?php echo $e->club === (int) $data->home_club ? 'd-none d-sm-flex' : 'd-flex'; ?>">

				<?php if ( $e->club === (int) $data->away_club ) : ?>
					<div class="match__event-icon mx-2 anwp-flex-none anwp-leading-1">
						<svg class="icon__ball">
							<use xlink:href="#<?php echo esc_attr( 'yes' === $e->scored ? 'icon-ball_penalty' : 'icon-ball_canceled' ); ?>"></use>
						</svg>
					</div>

					<div class="match__event-content anwp-leading-1-25 anwp-text-base ml-auto ml-sm-0">
						<div class="match__event-type anwp-text-sm anwp-opacity-80 anwp-leading-1">
							<?php
							if ( 'yes' === $e->scored ) {
								echo esc_html( AnWPFL_Text::get_value( 'match__penalty_shootout__scored', _x( 'Scored', 'penalty shootout', 'anwp-football-leagues' ) ) );
							} else {
								echo esc_html( AnWPFL_Text::get_value( 'match__penalty_shootout__missed', _x( 'Missed', 'penalty shootout', 'anwp-football-leagues' ) ) );
							}
							?>
						</div>
						<div class="match__event-player">
							<?php if ( ! empty( $temp_players ) && 'temp__' === mb_substr( $e->player, 0, 6 ) ) : ?>
								<span class="match__event-player-name"><?php echo esc_html( isset( $temp_players[ $e->player ] ) ? $temp_players[ $e->player ]->name : '' ); ?></span>
								<?php
							elseif ( $e->player ) :
								$player = anwp_football_leagues()->player->get_player( $e->player );
								?>
								<a class="anwp-link-without-effects match__event-player-name" href="<?php echo esc_url( $player->link ); ?>">
									<?php echo esc_html( $player->name_short ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
