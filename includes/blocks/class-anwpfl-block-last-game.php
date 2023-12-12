<?php
/**
 * AnWP Football Leagues :: Block > Last Game
 *
 * @since   0.1.0
 * @package Football_Leagues
 */

class AnWPFL_Block_Last_Game {

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
			AnWP_Football_Leagues::dir( 'gutenberg/blocks/last-game' ),
			[
				'title'           => 'FL Last Game',
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
				'competition_id'  => '',
				'club_id'         => '',
				'season_id'       => '',
				'match_link_text' => '',
				'show_club_name'  => 1,
				'exclude_ids'     => '',
				'include_ids'     => '',
				'max_size'        => '',
				'offset'          => '',
				'transparent_bg'  => '',
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

		$shortcode_attr = [
			'competition_id'  => $attr['competition_id'],
			'club_id'         => $attr['club_id'],
			'season_id'       => $attr['season_id'],
			'match_link_text' => $attr['match_link_text'],
			'show_club_name'  => AnWP_Football_Leagues::string_to_bool( $attr['show_club_name'] ),
			'exclude_ids'     => $attr['exclude_ids'],
			'include_ids'     => $attr['include_ids'],
			'max_size'        => $attr['max_size'],
			'offset'          => $attr['offset'],
			'transparent_bg'  => $attr['transparent_bg'],
		];

		return anwp_football_leagues()->template->shortcode_loader( 'match-last', $shortcode_attr );
	}
}

return new AnWPFL_Block_Last_Game();
