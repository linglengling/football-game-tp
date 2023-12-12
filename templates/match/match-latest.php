<?php
/**
 * The Template for displaying Match >> Latest Clubs Matches Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-latest.php.
 *
 * @var object $data - Object with args.
 *
 * @author          Andrei Strekozov <anwp.pro>
 * @package         AnWP-Football-Leagues/Templates
 *
 * @version         0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'club_home_logo'  => '',
		'home_club'       => '',
		'club_home_title' => '',
		'club_away_logo'  => '',
		'away_club'       => '',
		'club_away_title' => '',
		'club_home_link'  => '',
		'club_away_link'  => '',
		'kickoff'         => '',
		'header'          => true,
	]
);

// Home Matches
$matches_home = anwp_football_leagues()->template->shortcode_loader(
	'matches',
	[
		'filter_by'     => 'club',
		'filter_values' => $data->home_club,
		'type'          => 'result',
		'limit'         => 5,
		'sort_by_date'  => 'desc',
		'class'         => '',
		'date_to'       => $data->kickoff,
	]
);

// Away Matches
$matches_away = anwp_football_leagues()->template->shortcode_loader(
	'matches',
	[
		'filter_by'     => 'club',
		'filter_values' => $data->away_club,
		'type'          => 'result',
		'limit'         => 5,
		'sort_by_date'  => 'desc',
		'class'         => '',
		'date_to'       => $data->kickoff,
	]
);

if ( empty( $matches_home ) && empty( $matches_away ) ) {
	return;
}
?>
<div class="anwp-section match-latest">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__latest__latest_matches', __( 'Latest Matches', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}

	anwp_football_leagues()->load_partial(
		[
			'club_id'    => $data->home_club,
			'class'      => 'mb-2',
			'extra_html' => anwp_football_leagues()->helper->club_form( $data->home_club, false ),
		],
		'club/club-title'
	);


	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $matches_home;

	anwp_football_leagues()->load_partial(
		[
			'club_id'    => $data->away_club,
			'is_home'    => false,
			'class'      => 'mt-3 mb-2',
			'extra_html' => anwp_football_leagues()->helper->club_form( $data->away_club, false ),
		],
		'club/club-title'
	);

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $matches_away;
	?>
</div>
