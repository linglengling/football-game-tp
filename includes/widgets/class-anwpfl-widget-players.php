<?php
/**
 * AnWP Football Leagues :: Widget >> Players.
 *
 * @since   0.5.1
 * @package AnWP_Football_Leagues
 */

/**
 * AnWP Football Leagues :: Players
 *
 * @since 0.5.1
 */
class AnWPFL_Widget_Players extends AnWPFL_Widget {

	/**
	 * Get unique identifier for this widget.
	 *
	 * @return string
	 */
	protected function get_widget_slug() {
		return 'anwpfl-widget-players';
	}

	/**
	 * Get widget name, displayed in Widgets dashboard.
	 *
	 * @return string
	 */
	protected function get_widget_name() {
		return esc_html__( 'FL Players', 'anwp-football-leagues' );
	}

	/**
	 * Get widget description.
	 *
	 * @return string
	 */
	protected function get_widget_description() {
		return esc_html__( 'Show Players (scorers or assists).', 'anwp-football-leagues' );
	}

	/**
	 * Get widget CSS classes.
	 *
	 * @return string
	 */
	protected function get_widget_css_classes() {
		return 'anwpfl-widget-players';
	}

	/**
	 * Get widget options fields.
	 *
	 * @return array
	 */
	protected function get_widget_fields() {
		return [
			[
				'id'      => 'title',
				'type'    => 'text',
				'label'   => esc_html__( 'Title:', 'anwp-football-leagues' ),
				'default' => '',
			],
			[
				'id'      => 'type',
				'type'    => 'select',
				'label'   => esc_html__( 'Type', 'anwp-football-leagues' ),
				'default' => '',
				'options' => [
					'scorers' => esc_html__( 'Scorers', 'anwp-football-leagues' ),
					'assists' => esc_html__( 'Assists', 'anwp-football-leagues' ),
				],
			],
			[
				'id'      => 'competition_id',
				'type'    => 'competition_id',
				'label'   => esc_html__( 'Competition IDs', 'anwp-football-leagues' ),
				'default' => '',
				'single'  => 'no',
			],
			[
				'id'    => 'join_secondary',
				'type'  => 'checkbox',
				'label' => esc_html__( 'Include stats from secondary stages', 'anwp-football-leagues' ),
			],
			[
				'id'         => 'league_id',
				'type'       => 'select',
				'label'      => esc_html__( 'League', 'anwp-football-leagues' ),
				'show_empty' => esc_html__( '- select league -', 'anwp-football-leagues' ),
				'default'    => '',
				'options_cb' => [ anwp_football_leagues()->league, 'get_league_options' ],
			],
			[
				'id'         => 'season_id',
				'type'       => 'select',
				'label'      => esc_html__( 'Season', 'anwp-football-leagues' ),
				'show_empty' => esc_html__( '- select season -', 'anwp-football-leagues' ),
				'default'    => '',
				'options_cb' => [ anwp_football_leagues()->season, 'get_seasons_options' ],
			],
			[
				'id'     => 'club_id',
				'type'   => 'club_id',
				'label'  => esc_html__( 'Club IDs', 'anwp-football-leagues' ),
				'single' => 'no',
			],
			[
				'id'      => 'limit',
				'type'    => 'number',
				'label'   => esc_html__( 'Players Limit (0 - for all)', 'anwp-football-leagues' ),
				'default' => 10,
			],
			[
				'id'      => 'soft_limit',
				'type'    => 'select',
				'label'   => esc_html__( 'Soft Limit', 'anwp-football-leagues' ),
				'default' => 'yes',
				'options' => [
					'no'  => esc_html__( 'No', 'anwp-football-leagues' ),
					'yes' => esc_html__( 'Yes', 'anwp-football-leagues' ),
				],
			],
			[
				'id'      => 'show_photo',
				'type'    => 'select',
				'label'   => esc_html__( 'Show Photo', 'anwp-football-leagues' ),
				'default' => 'yes',
				'options' => [
					'no'  => esc_html__( 'No', 'anwp-football-leagues' ),
					'yes' => esc_html__( 'Yes', 'anwp-football-leagues' ),
				],
			],
			[
				'id'      => 'penalty_goals',
				'type'    => 'select',
				'label'   => esc_html__( 'Goals (from penalty)', 'anwp-football-leagues' ),
				'default' => '0',
				'options' => [
					'0' => esc_html__( 'Hide', 'anwp-football-leagues' ),
					'1' => esc_html__( 'Show', 'anwp-football-leagues' ),
				],
			],
			[
				'id'      => 'games_played',
				'type'    => 'select',
				'label'   => esc_html__( 'Matches played', 'anwp-football-leagues' ),
				'default' => '0',
				'options' => [
					'0' => esc_html__( 'Hide', 'anwp-football-leagues' ),
					'1' => esc_html__( 'Show', 'anwp-football-leagues' ),
				],
			],
			[
				'id'      => 'games_played_text',
				'type'    => 'text',
				'label'   => esc_html__( 'Text for "Matches played" column', 'anwp-football-leagues' ),
				'default' => esc_html__( 'Played', 'anwp-football-leagues' ),
			],
			[
				'id'      => 'group_by_place',
				'type'    => 'select',
				'label'   => esc_html__( 'Group By Place', 'anwp-football-leagues' ),
				'default' => '0',
				'options' => [
					'0' => esc_html__( 'No', 'anwp-football-leagues' ),
					'1' => esc_html__( 'Yes', 'anwp-football-leagues' ),
				],
			],
			[
				'id'      => 'secondary_sorting',
				'type'    => 'select',
				'label'   => esc_html__( 'Secondary Sorting', 'anwp-football-leagues' ),
				'default' => '',
				'options' => [
					''             => esc_html__( 'Default', 'anwp-football-leagues' ),
					'less_games'   => esc_html__( 'Less Games', 'anwp-football-leagues' ),
					'less_penalty' => esc_html__( 'Less Penalty', 'anwp-football-leagues' ),
				],
			],
		];
	}
}
