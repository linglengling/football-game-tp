<?php
/**
 * The Template for displaying Stadium >> Latest Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/stadium/stadium-latest.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.14.0
 *
 * @version       0.14.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'stadium_id' => '',
		'season_id'  => '',
		'header'     => true,
	]
);

if ( ! intval( $data->stadium_id ) ) {
	return;
}

$matches_args = [
	'stadium_id'   => $data->stadium_id,
	'type'         => 'result',
	'sort_by_date' => 'desc',
	'season_id'    => $data->season_id,
];

$shortcode_html = anwp_football_leagues()->template->shortcode_loader( 'matches', $matches_args );

if ( empty( $shortcode_html ) ) {
	return;
}
?>
<div class="stadium-latest anwp-section">

	<?php

	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'stadium__content__latest_matches', __( 'Latest Matches', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $shortcode_html;
	?>
</div>
