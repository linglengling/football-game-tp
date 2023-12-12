<?php
/**
 * The Template for displaying Referee >> Finished Games Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/referee/referee-finished.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.11.14
 *
 * @version       0.14.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'staff_id'  => '',
		'season_id' => '',
		'header'    => true,
	]
);

if ( empty( $data->staff_id ) ) {
	return;
}

$games_options = [
	'referee_id'   => $data->staff_id,
	'season_id'    => $data->season_id,
	'type'         => 'result',
	'sort_by_date' => 'desc',
];

$games_referee        = anwp_football_leagues()->referee->get_referee_games( $games_options );
$games_assistant      = anwp_football_leagues()->referee->get_referee_games( $games_options, '', 'assistant' );
$games_referee_fourth = anwp_football_leagues()->referee->get_referee_games( $games_options, '', 'referee_fourth' );
$games_additional     = anwp_football_leagues()->referee->get_referee_games( $games_options, '', 'additional' );
?>
<div class="referee-finished anwp-section anwp-b-wrap">
	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'referee__finished__finished_matches', __( 'Finished matches', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}

	if ( empty( $games_referee ) && empty( $games_assistant ) && empty( $games_referee_fourth ) && empty( $games_additional ) ) {
		anwp_football_leagues()->load_partial(
			[
				'no_data_text' => AnWPFL_Text::get_value( 'referee__finished__no_data', __( 'no data', 'anwp-football-leagues' ) ),
			],
			'general/no-data'
		);
	}

	if ( ! empty( $games_referee ) ) :

		/*
		|--------------------------------------------------------------------
		| Subheader - Referee
		|--------------------------------------------------------------------
		*/
		anwp_football_leagues()->load_partial(
			[
				'text'  => AnWPFL_Text::get_value( 'match__referees__referee', __( 'Referee', 'anwp-football-leagues' ) ),
				'class' => 'mb-2 referee-finished__header',
			],
			'general/subheader'
		);

		/*
		|--------------------------------------------------------------------
		| Referee Games
		|--------------------------------------------------------------------
		*/
		$cards_by_game = anwp_football_leagues()->referee->get_cards_game_by_players( $games_referee );

		foreach ( $games_referee as $list_match ) :
			$data = anwp_football_leagues()->match->prepare_match_data_to_render( $list_match );

			$data['competition_logo']   = 1;
			$data['extra_actions_html'] = anwp_football_leagues()->referee->get_cards_game_html(
				$list_match,
				isset( $cards_by_game[ $list_match->match_id ] ) ? $cards_by_game[ $list_match->match_id ] : []
			);

			anwp_football_leagues()->load_partial( $data, 'match/match', 'slim' );

		endforeach;
	endif;

	if ( ! empty( $games_assistant ) ) :
		/*
		|--------------------------------------------------------------------
		| Subheader - Assistant Referee
		|--------------------------------------------------------------------
		*/
		anwp_football_leagues()->load_partial(
			[
				'text'  => AnWPFL_Text::get_value( 'match__referees__assistant', __( 'Assistant Referee', 'anwp-football-leagues' ) ),
				'class' => 'mb-2 mt-2 referee-finished__header',
			],
			'general/subheader'
		);

		/*
		|--------------------------------------------------------------------
		| Assistant Referee Games
		|--------------------------------------------------------------------
		*/
		foreach ( $games_assistant as $list_match ) :
			$data = anwp_football_leagues()->match->prepare_match_data_to_render( $list_match );

			$data['competition_logo'] = 1;
			anwp_football_leagues()->load_partial( $data, 'match/match', 'slim' );

		endforeach;
	endif;

	if ( ! empty( $games_referee_fourth ) ) :
		/*
		|--------------------------------------------------------------------
		| Subheader - Fourth official
		|--------------------------------------------------------------------
		*/
		anwp_football_leagues()->load_partial(
			[
				'text'  => AnWPFL_Text::get_value( 'match__referees__fourth_official', __( 'Fourth official', 'anwp-football-leagues' ) ),
				'class' => 'mb-2 mt-2 referee-finished__header',
			],
			'general/subheader'
		);

		/*
		|--------------------------------------------------------------------
		| Assistant Referee Games
		|--------------------------------------------------------------------
		*/
		foreach ( $games_referee_fourth as $list_match ) :
			$data = anwp_football_leagues()->match->prepare_match_data_to_render( $list_match );

			$data['competition_logo'] = 1;
			anwp_football_leagues()->load_partial( $data, 'match/match', 'slim' );

		endforeach;
	endif;

	if ( ! empty( $games_additional ) ) :

		$games_additional_grouped = anwp_football_leagues()->referee->get_additional_referee_grouped( $games_additional, $games_options['referee_id'] );

		foreach ( $games_additional_grouped as $additional_referee_job => $games_additional_group ) :
			anwp_football_leagues()->load_partial(
				[
					'text'  => $additional_referee_job,
					'class' => 'mb-2 mt-2 referee-finished__header',
				],
				'general/subheader'
			);

			/*
			|--------------------------------------------------------------------
			| Assistant Referee Games
			|--------------------------------------------------------------------
			*/
			foreach ( $games_additional_group as $list_match ) :
				$data = anwp_football_leagues()->match->prepare_match_data_to_render( $list_match );

				$data['competition_logo'] = 1;
				anwp_football_leagues()->load_partial( $data, 'match/match', 'slim' );

			endforeach;
		endforeach;
	endif;
	?>
</div>
