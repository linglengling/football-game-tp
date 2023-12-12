<?php
/**
 * AnWP Football Leagues :: Blocks.
 *
 * @since   0.15.1
 * @package AnWP_Football_Leagues
 *
 */

/**
 * AnWP Football Leagues :: Blocks.
 */
class AnWPFL_Blocks {

	/**
	 * Blocks.
	 *
	 * @var array
	 */
	public $blocks = [];

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @param AnWP_Football_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;

		if ( 'no' !== AnWPFL_Options::get_value( 'gutenberg_blocks' ) ) {
			// Register Blocks
			$this->blocks['competition_header'] = AnWP_Football_Leagues::include_file( 'includes/blocks/class-anwpfl-block-competition-header' );
			$this->blocks['next_game']          = AnWP_Football_Leagues::include_file( 'includes/blocks/class-anwpfl-block-next-game' );
			$this->blocks['last_game']          = AnWP_Football_Leagues::include_file( 'includes/blocks/class-anwpfl-block-last-game' );
			$this->blocks['game_countdown']     = AnWP_Football_Leagues::include_file( 'includes/blocks/class-anwpfl-block-game-countdown' );
			$this->blocks['teams']              = AnWP_Football_Leagues::include_file( 'includes/blocks/class-anwpfl-block-teams' );

			// Run Hooks
			$this->hooks();
		}
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'add_block_editor_assets' ] ); // add_editor_style
		add_filter( 'block_categories_all', [ $this, 'add_block_category' ] );
	}


	/**
	 * Add Block Category
	 */
	public function add_block_category( $categories ) {
		return array_merge(
			[
				[
					'slug'  => 'anwp-fl',
					'title' => __( 'Football Leagues', 'football-leagues' ),
				],
			],
			$categories
		);
	}

	/**
	 * Register blocks.
	 */
	public function add_block_editor_assets() {

		$assets = AnWP_Football_Leagues::include_file( 'gutenberg/blocks.asset' );

		wp_enqueue_script(
			'anwp-fl-blocks',
			AnWP_Football_Leagues::url( 'gutenberg/blocks.js' ),
			$assets['dependencies'],
			$assets['version'],
			true
		);

		wp_enqueue_style(
			'anwp-fl-blocks',
			AnWP_Football_Leagues::url( 'gutenberg/blocks.css' ),
			[],
			$assets['version']
		);

		wp_enqueue_style(
			'anwp-fl-blocks-editor',
			AnWP_Football_Leagues::url( 'admin/css/editor-styles.css' ),
			[],
			AnWP_Football_Leagues::VERSION
		);

		anwp_football_leagues()->public_enqueue_scripts();
	}
}
