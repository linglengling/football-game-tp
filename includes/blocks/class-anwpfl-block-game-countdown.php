<?php
/**
 * AnWP Football Leagues :: Block > Game Countdown
 *
 * @since   0.1.0
 * @package Football_Leagues
 */

class AnWPFL_Block_Game_Countdown {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_blocks' ] );
	}

	/**
	 * Register blocks.
	 */
	public function register_blocks() {
		register_block_type(
			AnWP_Football_Leagues::dir( 'gutenberg/blocks/game-countdown' ),
			[
				'title'           => 'FL Game Countdown',
				'render_callback' => [ $this, 'server_side_render' ],
			]
		);
	}

	/**
	 * Register blocks.
	 *
	 * @param array    $attr           the block attributes
	 * @param string   $content        the block content
	 * @param WP_Block $block_instance The instance of the WP_Block class that represents the block being rendered
	 */
	public function server_side_render( $attr, $content, $block_instance ) {

		$attr = wp_parse_args(
			$attr,
			[
				'competition_id' => '',
				'club_id'        => '',
				'season_id'      => '',
				'exclude_ids'    => '',
				'include_ids'    => '',
				'offset'         => '',
				'label_size'     => '',
				'value_size'     => '',
			]
		);

		if ( empty( $attr['competition_id'] ) && empty( $attr['club_id'] ) ) {
			ob_start();
			anwp_football_leagues()->load_partial(
				[
					'no_data_text' => __( 'Please specify Competition or Club ID', 'anwp-football-leagues' ),
				],
				'general/no-data'
			);

			return ob_get_clean();
		}

		$date_from = '';

		if ( function_exists( 'current_datetime' ) && empty( $attr['include_ids'] ) ) {
			$date_from = current_datetime()->format( 'Y-m-d' );
		}

		// Get competition matches
		$games = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended(
			[
				'competition_id' => $attr['competition_id'],
				'season_id'      => $attr['season_id'],
				'show_secondary' => 1,
				'type'           => 'fixture',
				'filter_values'  => $attr['club_id'],
				'filter_by'      => 'club',
				'limit'          => 1,
				'sort_by_date'   => 'asc',
				'exclude_ids'    => $attr['exclude_ids'],
				'include_ids'    => $attr['include_ids'],
				'offset'         => $attr['offset'],
				'date_from'      => $date_from,
			]
		);

		if ( empty( $games ) || empty( $games[0]->match_id ) ) {
			return '';
		}

		$tmpl_data = [
			'kickoff'    => $games[0]->kickoff,
			'kickoff_c'  => date_i18n( 'c', strtotime( $games[0]->kickoff ) ),
			'label_size' => $attr['label_size'],
			'value_size' => $attr['value_size'],
		];

		ob_start();
		anwp_football_leagues()->load_partial( $tmpl_data, 'match/match-countdown', '' );
		return ob_get_clean();
	}
}

return new AnWPFL_Block_Game_Countdown();
