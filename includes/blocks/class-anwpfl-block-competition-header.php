<?php
/**
 * AnWP Football Leagues :: Block > Competition Header
 *
 * @since   0.1.0
 * @package Football_Leagues
 */

class AnWPFL_Block_Competition_Header {

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
			AnWP_Football_Leagues::dir( 'gutenberg/blocks/competition-header' ),
			[
				'title'           => 'FL Competition Header',
				'render_callback' => [ $this, 'render_tournament_header' ],
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
	public function render_tournament_header( $attr, $content, $block_instance ) {

		$attr = wp_parse_args(
			$attr,
			[
				'competition_id'  => '',
				'season_selector' => '',
				'title_field'     => '',
				'title_as_link'   => '',
				'title'           => '',
				'transparent_bg'  => '',
			]
		);

		$competition_id = absint( $attr['competition_id'] );

		if ( ! $competition_id ) {
			ob_start();
			anwp_football_leagues()->load_partial(
				[
					'no_data_text' => __( 'Competition ID is not set', 'anwp-football-leagues' ),
				],
				'general/no-data'
			);

			return ob_get_clean();
		}

		if ( 'anwp_competition' !== get_post_type( $competition_id ) ) {
			ob_start();
			anwp_football_leagues()->load_partial(
				[
					'no_data_text' => __( 'Incorrect ID', 'anwp-football-leagues' ),
				],
				'general/no-data'
			);

			return ob_get_clean();
		}

		$competition_shortcode_attr = [
			'id'              => $competition_id,
			'season_selector' => AnWP_Football_Leagues::string_to_bool( $attr['season_selector'] ),
			'title_as_link'   => AnWP_Football_Leagues::string_to_bool( $attr['title_as_link'] ),
			'transparent_bg'  => AnWP_Football_Leagues::string_to_bool( $attr['transparent_bg'] ),
			'title_field'     => $attr['title_field'],
			'title'           => $attr['title'],
		];

		return anwp_football_leagues()->template->shortcode_loader( 'competition_header', $competition_shortcode_attr );
	}
}

return new AnWPFL_Block_Competition_Header();
