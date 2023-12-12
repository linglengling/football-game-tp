<?php
/**
 * The Template for displaying Club >> Latest Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/club/club-latest.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.8.4
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'club_id'   => '',
		'season_id' => '',
		'header'    => true,
	]
);

$club = get_post( $data->club_id );

/**
 * Hook: anwpfl/tmpl-club/before_latest
 *
 * @since 0.7.5
 *
 * @param WP_Post $club
 * @param integer $season_id
 */
do_action( 'anwpfl/tmpl-club/before_latest', $club, $data->season_id );

if ( ! apply_filters( 'anwpfl/tmpl-club/render_latest', true, $club, $data->season_id ) ) {
	return;
}
?>
<div class="club-latest anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'club__latest__latest_matches', __( 'Latest Matches', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}

	/*
	|--------------------------------------------------------------------
	| Latest Matches
	|--------------------------------------------------------------------
	*/
	$shortcode_loader = [
		'filter_by_clubs' => anwp_football_leagues()->club->get_subteam_ids( $club->ID ),
		'season_id'       => $data->season_id,
		'type'            => 'result',
		'limit'           => 10,
		'sort_by_date'    => 'desc',
		'show_load_more'  => true,
	];

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo anwp_football_leagues()->template->shortcode_loader( 'matches', $shortcode_loader );
	?>
</div>
