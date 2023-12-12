<?php
/**
 * The Template for displaying Match >> Cards Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-cards.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.6.1
 *
 * @version       0.14.2
 */

// phpcs:disable WordPress.NamingConventions.ValidVariableName

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'match_id'   => '',
		'home_club'  => '',
		'away_club'  => '',
		'home_goals' => '',
		'away_goals' => '',
		'events'     => [],
		'header'     => true,
	]
);

if ( empty( $data->events['cards'] ) ) {
	return '';
}

$card_options = anwp_football_leagues()->data->cards;
$temp_players = anwp_football_leagues()->match->get_temp_players( $data->match_id );

/**
 * Hook: anwpfl/tmpl-match/cards_before
 *
 * @param object $data Match data
 *
 * @since   0.7.5
 * @version 0.8.0
 */
do_action( 'anwpfl/tmpl-match/cards_before', $data );
?>
<div class="anwp-section match-cards">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__cards__cards', __( 'Cards', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<?php foreach ( $data->events['cards'] as $e_index => $e ) : ?>
		<div class="match__event-row py-1 d-flex align-items-center flex-row-reverse flex-sm-row anwp-fl-border-bottom anwp-border-light <?php echo $e_index ? '' : 'anwp-fl-border-top'; ?>">
			<div class="match__event-team-row anwp-flex-1 align-items-center <?php echo $e->club === (int) $data->away_club ? 'd-none d-sm-flex' : 'd-flex'; ?>">

				<?php if ( $e->club === (int) $data->home_club ) : ?>
					<div class="match__event-icon mx-2 anwp-flex-none anwp-leading-1">
						<svg class="icon__card">
							<use xlink:href="#icon-card_<?php echo esc_attr( $e->card ); ?>"></use>
						</svg>
					</div>

					<div class="match__event-content anwp-leading-1-25 anwp-text-base">
						<div class="match__event-type anwp-text-sm anwp-opacity-80 anwp-leading-1">
							<?php echo esc_html( isset( $card_options[ $e->card ] ) ? $card_options[ $e->card ] : '' ); ?>
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
			<div class="anwp-flex-none">
				<div class="match__event-minute d-flex flex-column anwp-bg-light">
					<div class="anwp-text-lg anwp-leading-1-25 <?php echo esc_attr( intval( $e->minute ) ? '' : 'anwp-hidden' ); ?>"><?php echo (int) $e->minute; ?>'</div>
					<?php if ( (int) $e->minuteAdd ) : ?>
						<div class="match__event-minute-add anwp-text-xs anwp-leading-1 anwp-text-center">+<?php echo (int) $e->minuteAdd; ?></div>
					<?php endif; ?>
				</div>
			</div>
			<div class="match__event-team-row anwp-flex-1 align-items-center flex-row-reverse flex-sm-row <?php echo $e->club === (int) $data->home_club ? 'd-none d-sm-flex' : 'd-flex'; ?>">

				<?php if ( $e->club === (int) $data->away_club ) : ?>
					<div class="match__event-icon mx-2 anwp-flex-none anwp-leading-1">
						<svg class="icon__card">
							<use xlink:href="#icon-card_<?php echo esc_attr( $e->card ); ?>"></use>
						</svg>
					</div>

					<div class="match__event-content anwp-leading-1-25 anwp-text-base ml-auto ml-sm-0">
						<div class="match__event-type anwp-text-sm anwp-opacity-80 anwp-leading-1">
							<?php echo esc_html( isset( $card_options[ $e->card ] ) ? $card_options[ $e->card ] : '' ); ?>
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
