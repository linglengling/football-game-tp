<?php
/**
 * The Template for displaying player content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/content-player.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.3.0
 * @version       0.14.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$player_id = get_the_ID();

// Get Season ID
if ( ( empty( $_GET['season'] ) && 'yes' === AnWPFL_Options::get_value( 'all_season_default' ) ) || ( ! empty( $_GET['season'] ) && 'all' === $_GET['season'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	$current_season_id = 'all';
} else {
	$current_season_id = anwp_football_leagues()->helper->get_season_id_maybe( $_GET, anwp_football_leagues()->get_active_player_season( $player_id ) ); // phpcs:ignore WordPress.Security.NonceVerification
}

/*
|--------------------------------------------------------------------------
| Prepare player data for sections
|--------------------------------------------------------------------------
*/
$position_code = get_post_meta( $player_id, '_anwpfl_position', true );

// Card icons
$card_icons = [
	'y'  => '<svg class="icon__card m-0"><use xlink:href="#icon-card_y"></use></svg>',
	'r'  => '<svg class="icon__card m-0"><use xlink:href="#icon-card_r"></use></svg>',
	'yr' => '<svg class="icon__card m-0"><use xlink:href="#icon-card_yr"></use></svg>',
];

$series_map = anwp_football_leagues()->data->get_series();

// Get season matches
$season_matches      = anwp_football_leagues()->player->tmpl_get_latest_matches( $player_id, $current_season_id );
$competition_matches = anwp_football_leagues()->player->tmpl_prepare_competition_matches( $season_matches );

$player_data = [
	'player_id'           => $player_id,
	'current_season_id'   => $current_season_id,
	'competition_matches' => $competition_matches,
	'card_icons'          => $card_icons,
	'series_map'          => $series_map,
	'position_code'       => $position_code,
	'club_id'             => (int) get_post_meta( $player_id, '_anwpfl_current_club', true ),
];

$player_data['club_title'] = anwp_football_leagues()->club->get_club_title_by_id( $player_data['club_id'] );
$player_data['club_link']  = anwp_football_leagues()->club->get_club_link_by_id( $player_data['club_id'] );

/**
 * Hook: anwpfl/tmpl-player/before_wrapper
 *
 * @since 0.7.5
 *
 * @param int $player_id
 */
do_action( 'anwpfl/tmpl-player/before_wrapper', $player_id );
?>
<div class="anwp-b-wrap player player__inner player-id-<?php echo (int) $player_id; ?>">
	<?php

	$player_sections = [
		'header',
		'description',
		'stats',
		'matches',
		'missed',
		'gallery',
	];

	/**
	 * Filter: anwpfl/tmpl-player/sections
	 *
	 * @since 0.8.3
	 *
	 * @param array $player_sections
	 * @param array $data
	 * @param int   $player_id
	 */
	$player_sections = apply_filters( 'anwpfl/tmpl-player/sections', $player_sections, $player_data, $player_id );

	foreach ( $player_sections as $section ) {
		anwp_football_leagues()->load_partial( $player_data, 'player/player-' . sanitize_key( $section ) );
	}
	?>
</div>
<?php
/**
 * Hook: anwpfl/tmpl-player/after_wrapper
 *
 * @since 0.7.5
 *
 * @param int $player_id
 */
do_action( 'anwpfl/tmpl-player/after_wrapper', $player_id );
