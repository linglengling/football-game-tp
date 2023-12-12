<?php
/**
 * The Template for displaying Match >> Goals Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-missed_penalties.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.6.1
 *
 * @version       0.14.0
 */

// phpcs:disable WordPress.NamingConventions.ValidVariableName

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

if ( empty( $data->events['missed_penalty'] ) ) {
	return '';
}
?>
<div class="anwp-section match-missed-penalties">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__missed_penalties__missed_penalties', __( 'Missed penalties', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<?php foreach ( $data->events['missed_penalty'] as $e_index => $e ) : ?>
		<div class="match__event-team-row match__event-row py-1 d-flex align-items-center flex-row-reverse flex-sm-row anwp-fl-border-bottom anwp-border-light <?php echo $e_index ? '' : 'anwp-fl-border-top'; ?>">
			<div class="anwp-flex-1 align-items-center <?php echo $e->club === (int) $data->away_club ? 'd-none d-sm-flex' : 'd-flex'; ?>">
				<?php if ( $e->club === (int) $data->home_club ) : ?>
					<div class="match__event-icon mx-2 anwp-flex-none anwp-leading-1">
						<svg class="icon__ball">
							<use xlink:href="#icon-ball_canceled"></use>
						</svg>
					</div>

					<div class="match__event-content anwp-leading-1-25 anwp-text-base">
						<div class="match__event-type anwp-text-sm anwp-opacity-80 anwp-leading-1">
							<?php echo esc_html_x( 'Missed Penalty', 'match event', 'anwp-football-leagues' ); ?>
						</div>
						<div class="match__event-player">
							<?php
							if ( ! empty( $e->player ) ) :
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
				<div class="match__event-minute d-flex justify-content-center align-items-end anwp-bg-light anwp-text-lg">
					<span class="d-inline-block <?php echo esc_attr( intval( $e->minute ) ? '' : 'anwp-hidden' ); ?>"><?php echo (int) $e->minute; ?>'</span>
					<?php if ( (int) $e->minuteAdd ) : ?>
						<span class="d-inline-block match__event-minute-add pb-1 anwp-text-sm">+<?php echo (int) $e->minuteAdd; ?></span>
					<?php endif; ?>
				</div>
			</div>
			<div class="match__event-team-row anwp-flex-1 align-items-center flex-row-reverse flex-sm-row <?php echo $e->club === (int) $data->home_club ? 'd-none d-sm-flex' : 'd-flex'; ?>">

				<?php if ( $e->club === (int) $data->away_club ) : ?>
					<div class="match__event-icon mx-2 anwp-flex-none anwp-leading-1">
						<svg class="icon__ball">
							<use xlink:href="#icon-ball_canceled"></use>
						</svg>
					</div>

					<div class="match__event-content anwp-leading-1-25 anwp-text-base ml-auto ml-sm-0">
						<div class="match__event-type anwp-text-sm anwp-opacity-80 anwp-leading-1">
							<?php echo esc_html_x( 'Missed Penalty', 'match event', 'anwp-football-leagues' ); ?>
						</div>
						<div class="match__event-player">
							<?php
							if ( $e->player ) :
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
