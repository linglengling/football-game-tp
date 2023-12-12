<?php
/**
 * AnWP Football Leagues :: Player.
 *
 * @since   0.1.0
 * @package AnWP_Football_Leagues
 */

require_once dirname( __FILE__ ) . '/../vendor/cpt-core/CPT_Core.php';

/**
 * AnWP Football Leagues :: Player post type class.
 *
 * @since 0.1.0
 *
 * @see   https://github.com/WebDevStudios/CPT_Core
 */
class AnWPFL_Player extends CPT_Core {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Players options.
	 * [<id>] => <player name>
	 *
	 * @var    array
	 * @since  0.3.0
	 */
	protected static $players = null;

	/**
	 * Constructor.
	 * Register Custom Post Types.
	 *
	 * See documentation in CPT_Core, and in wp-includes/post.php.
	 *
	 * @since  0.1.0
	 * @param  AnWP_Football_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

		$permalink_structure = $plugin->options->get_permalink_structure();
		$permalink_slug      = empty( $permalink_structure['player'] ) ? 'player' : $permalink_structure['player'];

		// Register this cpt.
		parent::__construct(
			[ // array with Singular, Plural, and Registered name
				esc_html__( 'Player', 'anwp-football-leagues' ),
				esc_html__( 'Players', 'anwp-football-leagues' ),
				'anwp_player',
			],
			[
				'supports'            => [
					'title',
					'comments',
				],
				'rewrite'             => [ 'slug' => $permalink_slug ],
				'show_in_menu'        => true,
				'menu_position'       => 34,
				'exclude_from_search' => 'hide' === AnWPFL_Options::get_value( 'display_front_end_search_player' ),
				'menu_icon'           => 'dashicons-groups',
				'public'              => true,
				'labels'              => [
					'all_items'    => esc_html__( 'Players', 'anwp-football-leagues' ),
					'add_new'      => esc_html__( 'Add New Player', 'anwp-football-leagues' ),
					'add_new_item' => esc_html__( 'Add New Player', 'anwp-football-leagues' ),
					'edit_item'    => esc_html__( 'Edit Player', 'anwp-football-leagues' ),
					'new_item'     => esc_html__( 'New Player', 'anwp-football-leagues' ),
					'view_item'    => esc_html__( 'View Player', 'anwp-football-leagues' ),
				],
			]
		);
	}

	/**
	 * Filter CPT title entry placeholder text
	 *
	 * @param  string $title Original placeholder text
	 *
	 * @return string        Modified placeholder text
	 */
	public function title( $title ) {

		$screen = get_current_screen();
		if ( isset( $screen->post_type ) && $screen->post_type === $this->post_type ) {
			return esc_html__( 'Player Name', 'anwp-football-leagues' );
		}

		return $title;
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		// Create CMB2 metabox
		add_action( 'cmb2_admin_init', [ $this, 'init_cmb2_metaboxes' ] );
		add_action( 'cmb2_before_post_form_anwp_player_metabox', [ $this, 'cmb2_before_metabox' ] );
		add_action( 'cmb2_after_post_form_anwp_player_metabox', [ $this, 'cmb2_after_metabox' ] );

		// Add custom filter
		add_action( 'restrict_manage_posts', [ $this, 'custom_admin_filters' ] );
		add_filter( 'pre_get_posts', [ $this, 'handle_custom_filter' ] );

		// Render Custom Content below
		add_action(
			'anwpfl/tmpl-player/after_wrapper',
			function ( $player_id ) {

				$content_below = get_post_meta( $player_id, '_anwpfl_custom_content_below', true );

				if ( trim( $content_below ) ) {
					echo '<div class="anwp-b-wrap mt-4">' . do_shortcode( $content_below ) . '</div>';
				}
			}
		);

		add_action( 'clean_post_cache', [ $this, 'maybe_clean_player_cache' ], 10, 2 );

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );

		add_action( 'load-post.php', [ $this, 'init_metaboxes' ] );
		add_action( 'load-post-new.php', [ $this, 'init_metaboxes' ] );
		add_action( 'save_post', [ $this, 'save_metabox' ], 10, 2 );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}

	/**
	 * Meta box initialization.
	 *
	 * @since  0.2.0 (2018-01-10)
	 */
	public function init_metaboxes() {
		add_action(
			'add_meta_boxes',
			function ( $post_type ) {

				if ( 'anwp_player' === $post_type ) {
					add_meta_box(
						'anwpfl_player_manual_stats',
						esc_html__( 'Manual Statistics', 'anwp-football-leagues' ),
						[ $this, 'render_metabox' ],
						$post_type,
						'normal',
						'high'
					);
				}
			}
		);
	}

	/**
	 * Render Meta Box content for Competition Stages.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @since  0.13.7
	 */
	public function render_metabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'anwp_save_metabox_' . $post->ID, 'anwp_metabox_nonce' );
		?>
		<div class="anwp-b-wrap">
			<div class="p-3">
				<div id="anwpfl-app-player-manual-stats"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @since 0.13.7
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

		$current_screen = get_current_screen();

		if ( in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) && 'anwp_player' === $current_screen->id ) {

			$l10n = [
				'add_new_record'       => esc_html__( 'Add New Record', 'anwp-football-leagues' ),
				'notice_max'           => esc_html__( 'the maximum value is 65535', 'anwp-football-leagues' ),
				'notice_1'             => esc_html__( 'Season and Competition are required fields. The data will not be saved if you do not fill them.', 'anwp-football-leagues' ),
				'notice_2'             => esc_html__( 'You can select an existing Competition or add a text title of a new one without creation.', 'anwp-football-leagues' ),
				'notice_3'             => __( '"Goals Conceded" and "Clean Sheets" - for goalkeepers only', 'anwp-football-leagues' ),
				'competition'          => esc_html__( 'Competition', 'anwp-football-leagues' ),
				'season'               => esc_html__( 'Season', 'anwp-football-leagues' ),
				'played_matches'       => esc_html__( 'Played Matches', 'anwp-football-leagues' ),
				'started'              => esc_html__( 'Started', 'anwp-football-leagues' ),
				'substituted_in'       => esc_html__( 'Substituted In', 'anwp-football-leagues' ),
				'minutes'              => esc_html__( 'Minutes', 'anwp-football-leagues' ),
				'card_y'               => esc_html__( 'Yellow Cards', 'anwp-football-leagues' ),
				'card_yr'              => __( '2d Yellow > Red Cards', 'anwp-football-leagues' ),
				'card_r'               => esc_html__( 'Red Cards', 'anwp-football-leagues' ),
				'goals'                => esc_html__( 'Goals', 'anwp-football-leagues' ),
				'goals_penalty'        => esc_html__( 'Goals from penalty', 'anwp-football-leagues' ),
				'assists'              => esc_html__( 'Assists', 'anwp-football-leagues' ),
				'own_goals'            => esc_html__( 'Own Goals', 'anwp-football-leagues' ),
				'goals_conceded'       => esc_html__( 'Goals Conceded', 'anwp-football-leagues' ),
				'clean_sheets'         => esc_html__( 'Clean Sheets', 'anwp-football-leagues' ),
				'select_season'        => esc_html__( 'select season', 'anwp-football-leagues' ),
				'new_competition'      => esc_html__( 'New Competition', 'anwp-football-leagues' ),
				'existing_competition' => esc_html__( 'Existing Competition', 'anwp-football-leagues' ),
			];

			$app_data = [
				'l10n'              => $l10n,
				'statsData'         => $this->get_manual_stats( get_the_ID() ),
				'seasons_list'      => $this->plugin->season->get_seasons_list(),
				'competitions_list' => anwp_football_leagues()->competition->get_competitions(),
			];

			/**
			 * Modify App data
			 *
			 * @since 0.13.7
			 */
			$app_data = apply_filters( 'anwpfl/player/manual_stats_app_data', $app_data );

			wp_localize_script(
				'anwpfl_admin_vue',
				'anwpPlayerManualData',
				$app_data
			);
		}
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 *
	 * @since  0.13.7
	 * @return bool|int
	 */
	public function save_metabox( $post_id ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['anwp_metabox_nonce'] ) ) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['anwp_metabox_nonce'], 'anwp_save_metabox_' . $post_id ) ) {
			return $post_id;
		}

		// Check post type
		if ( 'anwp_player' !== $_POST['post_type'] ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// check if there was a multisite switch before
		if ( is_multisite() && ms_is_switched() ) {
			return $post_id;
		}

		/* OK, it's safe for us to save the data now. */

		/*
		|--------------------------------------------------------------------
		| Player's Manual Stats
		|--------------------------------------------------------------------
		*/

		if ( ! empty( $_POST['_anwpfl_player_manual_data'] ) ) {
			$this->update_manual_stats( $post_id, json_decode( wp_unslash( $_POST['_anwpfl_player_manual_data'] ) ) );
		}

		/**
		 * Trigger on save player's data.
		 *
		 * @param array $post_id
		 * @param array $_POST
		 *
		 * @since 0.13.7
		 */
		do_action( 'anwpfl/player/on_save', $post_id, $_POST );

		return $post_id;
	}

	/**
	 * Update Player's manual stats
	 *
	 * @since 0.13.7
	 */
	public function update_manual_stats( $player_id, $manual_data ) {

		global $wpdb;

		$stats_table = $wpdb->prefix . 'anwpfl_players_manual_stats';

		// Remove old stats
		$wpdb->delete( $stats_table, [ 'player_id' => $player_id ] );

		$table_fields = [
			'played',
			'started',
			'sub_in',
			'minutes',
			'card_y',
			'card_yr',
			'card_r',
			'goals',
			'goals_penalty',
			'assists',
			'own_goals',
			'goals_conceded',
			'clean_sheets',
		];

		foreach ( $manual_data as $data_row ) {

			if ( empty( $data_row->competition_type ) || ! in_array( $data_row->competition_type, [ 'new', 'id' ], true ) ) {
				continue;
			}

			if ( empty( $data_row->season_id ) || ! absint( $data_row->season_id ) ) {
				continue;
			}

			$data_to_insert = [
				'player_id'        => $player_id,
				'season_id'        => absint( $data_row->season_id ),
				'competition_id'   => '',
				'competition_text' => '',
				'competition_type' => $data_row->competition_type,
			];

			if ( 'new' === $data_row->competition_type ) {
				if ( empty( $data_row->competition_text ) ) {
					continue;
				}

				$data_to_insert['competition_text'] = sanitize_text_field( $data_row->competition_text );
			} else {
				if ( empty( $data_row->competition_id ) || ! absint( $data_row->competition_id ) ) {
					continue;
				}

				$data_to_insert['competition_id'] = absint( $data_row->competition_id );
			}

			foreach ( $table_fields as $table_field ) {
				$data_to_insert[ $table_field ] = isset( $data_row->{$table_field} ) ? absint( $data_row->{$table_field} ) : 0;
			}

			$wpdb->insert( $stats_table, $data_to_insert );
		}
	}

	/**
	 * Update Player's manual stats
	 *
	 * @param int $player_id
	 * @param int $season_id
	 *
	 * @return array
	 * @since 0.13.7
	 */
	public function get_manual_stats( $player_id, $season_id = 0 ) {

		global $wpdb;

		if ( ! absint( $player_id ) ) {
			return [];
		}

		$query  = 'SELECT `season_id`, `competition_id`, `competition_text`, `competition_type`, `played`, `started`, `sub_in`, `minutes`, `card_y`, `card_yr`, `card_r`, `goals`, `goals_penalty`, `assists`, `own_goals`, `goals_conceded`, `clean_sheets` ';
		$query .= "FROM {$wpdb->prefix}anwpfl_players_manual_stats ";

		$query .= $wpdb->prepare( 'WHERE player_id = %d ', $player_id );

		if ( absint( $season_id ) ) {
			$query .= $wpdb->prepare( ' AND season_id = %d ', $season_id );
		}

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $query );

		if ( empty( $rows ) || ! is_array( $rows ) ) {
			return [];
		}

		$table_fields = [
			'played',
			'started',
			'sub_in',
			'minutes',
			'card_y',
			'card_yr',
			'card_r',
			'goals',
			'goals_penalty',
			'assists',
			'own_goals',
			'goals_conceded',
			'clean_sheets',
		];

		foreach ( $rows as $row_index => $row ) {

			$row->id             = $row_index + 1;
			$row->season_id      = absint( $row->season_id );
			$row->competition_id = absint( $row->competition_id ) ?: '';

			foreach ( $table_fields as $table_field ) {
				$row->{$table_field} = absint( $row->{$table_field} ) ?: '';
			}
		}

		return $rows;
	}

	/**
	 * Register REST routes.
	 *
	 * @since 0.12.3
	 */
	public function add_rest_routes() {

		register_rest_route(
			'anwpfl/v1/player',
			'/update-player-current-club/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'update_player_current_club' ],
				'permission_callback' => [ anwp_football_leagues()->helper, 'update_permissions_check' ],
			]
		);

		register_rest_route(
			'anwpfl/v1/player',
			'/add-player-to-squad/',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'add_player_to_squad' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' ); // ToDo check with update_permissions_check
				},
			]
		);

		register_rest_route(
			'anwpfl/v1/player',
			'/get-player-data/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_player_actions_data' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' ); // ToDo check with update_permissions_check
				},
			]
		);
	}

	/**
	 * Get Player Data
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response | WP_Error
	 * @since 0.12.6
	 */
	public function get_player_actions_data( WP_REST_Request $request ) {

		$params    = $request->get_params();
		$player_id = isset( $params['player_id'] ) ? absint( $params['player_id'] ) : '';

		if ( empty( $player_id ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Invalid Player ID', [ 'status' => 400 ] );
		}

		$player_obj  = anwp_football_leagues()->player->get_player( $player_id );
		$player_data = [
			'current_club' => absint( $player_obj->club_id ) ? ( anwp_football_leagues()->club->get_club_title_by_id( $player_obj->club_id ) . ' (ID: ' . $player_obj->club_id . ')' ) : ' - ',
		];

		/**
		 * Modify player data
		 *
		 * @since 0.12.6
		 */
		$player_data = apply_filters( 'anwpfl/player/player_actions_data', $player_data, $params );

		return rest_ensure_response(
			[
				'result'      => true,
				'player_data' => $player_data,
			]
		);
	}

	/**
	 * Update Player Current Club
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response | WP_Error
	 * @since 0.12.3
	 */
	public function update_player_current_club( WP_REST_Request $request ) {

		$params = $request->get_params();

		$player_id = isset( $params['post_id'] ) ? absint( $params['post_id'] ) : '';
		$club_id   = isset( $params['club_id'] ) ? absint( $params['club_id'] ) : '';

		if ( empty( $player_id ) || empty( $club_id ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Invalid Data', [ 'status' => 400 ] );
		}

		$saved_current_club = get_post_meta( $player_id, '_anwpfl_current_club', true );

		if ( (int) $saved_current_club === $club_id ) {
			return rest_ensure_response( [ 'result' => true ] );
		}

		if ( ! update_post_meta( $player_id, '_anwpfl_current_club', $club_id ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Save error', [ 'status' => 400 ] );
		}

		return rest_ensure_response( [ 'result' => true ] );
	}

	/**
	 * Add Player to Squad
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response | WP_Error
	 * @since 0.12.3
	 */
	public function add_player_to_squad( WP_REST_Request $request ) {

		$params = $request->get_params();

		$player_id = isset( $params['player_id'] ) ? absint( $params['player_id'] ) : '';
		$club_id   = isset( $params['club_id'] ) ? absint( $params['club_id'] ) : '';
		$season_id = isset( $params['season_id'] ) ? absint( $params['season_id'] ) : '';

		$season_slug = 's:' . $season_id;
		$club_squad  = json_decode( get_post_meta( $club_id, '_anwpfl_squad', true ) );

		if ( ! $club_squad ) {
			$club_squad = (object) [];
		}

		$squad_players = isset( $club_squad->{$season_slug} ) ? $club_squad->{$season_slug} : [];

		if ( ! empty( wp_list_filter( $squad_players, [ 'id' => $player_id ] ) ) ) {
			return rest_ensure_response( [ 'result' => true ] );
		}

		$player_position = get_post_meta( $player_id, '_anwpfl_position', true );

		$squad_players[] = (object) [
			'id'       => $player_id,
			'position' => $player_position ?: '',
			'number'   => '',
			'status'   => '',
		];

		/*
		|--------------------------------------------------------------------
		| Save Club Squad
		|--------------------------------------------------------------------
		*/
		// Update club slug with new data
		$club_squad->{$season_slug} = $squad_players;

		// Save squad
		if ( ! update_post_meta( $club_id, '_anwpfl_squad', wp_slash( wp_json_encode( $club_squad ) ) ) ) {
			return new WP_Error( 'rest_anwp_fl_error', 'Save error', [ 'status' => 400 ] );
		}

		return rest_ensure_response( [ 'result' => true ] );
	}

	/**
	 * Renders tabs for metabox. Helper HTML before.
	 *
	 * @since 0.9.0
	 */
	public function cmb2_before_metabox() {
		// @formatter:off
		ob_start();
		?>
		<div class="anwp-b-wrap">
			<div class="anwp-metabox-tabs d-sm-flex">
				<div class="anwp-metabox-tabs__controls d-flex flex-sm-column flex-wrap">
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-general-player_metabox">
						<svg class="anwp-icon anwp-icon--octi d-inline-block"><use xlink:href="#icon-gear"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'General', 'anwp-football-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-media-player_metabox">
						<svg class="anwp-icon anwp-icon--octi d-inline-block"><use xlink:href="#icon-device-camera"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Media', 'anwp-football-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-desc-player_metabox">
						<svg class="anwp-icon anwp-icon--octi d-inline-block"><use xlink:href="#icon-note"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Bio', 'anwp-football-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-social-club_metabox">
						<svg class="anwp-icon anwp-icon--octi d-inline-block"><use xlink:href="#icon-repo-forked"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Social', 'anwp-football-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-custom_fields-player_metabox">
						<svg class="anwp-icon anwp-icon--octi d-inline-block"><use xlink:href="#icon-server"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Custom Fields', 'anwp-football-leagues' ); ?></span>
					</div>
					<div class="p-3 anwp-metabox-tabs__control-item" data-target="#anwp-tabs-bottom_content-player_metabox">
						<svg class="anwp-icon anwp-icon--octi d-inline-block"><use xlink:href="#icon-repo-push"></use></svg>
						<span class="d-block"><?php echo esc_html__( 'Bottom Content', 'anwp-football-leagues' ); ?></span>
					</div>
					<?php
					/**
					 * Fires in the bottom of player tabs.
					 * Add new tabs here.
					 *
					 * @since 0.9.0
					 */
					do_action( 'anwpfl/cmb2_tabs_control/player' );
					?>
				</div>
				<div class="anwp-metabox-tabs__content pl-4 pb-4">
		<?php
		echo ob_get_clean(); // WPCS: XSS ok.
		// @formatter:on
	}

	/**
	 * Renders tabs for metabox. Helper HTML after.
	 *
	 * @since 0.9.0
	 */
	public function cmb2_after_metabox() {
		// @formatter:off
		ob_start();
		?>
				</div>
			</div>
		</div>
		<?php
		echo ob_get_clean(); // WPCS: XSS ok.
		// @formatter:on
	}

	/**
	 * Fires before the Filter button on the Posts and Pages list tables.
	 *
	 * The Filter button allows sorting by date and/or category on the
	 * Posts list table, and sorting by date on the Pages list table.
	 *
	 * @since 0.4.1 (2018-02-14)
	 *
	 * @param string $post_type The post type slug.
	 */
	public function custom_admin_filters( $post_type ) {

		if ( 'anwp_player' === $post_type ) {

			$clubs = $this->plugin->club->get_clubs_options();

			$current_club_filter = empty( $_GET['_anwpfl_current_club'] ) ? '' : (int) $_GET['_anwpfl_current_club']; // WPCS: CSRF ok.
			ob_start();
			?>

			<select name='_anwpfl_current_club' id='anwp_club_filter' class='postform'>
				<option value=''>All Clubs</option>
				<?php foreach ( $clubs as $club_id => $club_title ) : ?>
					<option value="<?php echo esc_attr( $club_id ); ?>" <?php selected( $club_id, $current_club_filter ); ?>>
						<?php echo esc_html( $club_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php
			echo ob_get_clean(); // WPCS: XSS ok.
		}
	}

	/**
	 * Handle custom filter.
	 *
	 * @param WP_Query $query
	 *
	 * @since 0.4.1 (2018-02-14)
	 */
	public function handle_custom_filter( $query ) {
		global $post_type, $pagenow;

		// Check main query in admin
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'edit.php' === $pagenow && 'anwp_player' === $post_type && ! empty( $_GET['_anwpfl_current_club'] ) ) { // WPCS: CSRF ok.
			$query->set(
				'meta_query',
				[
					[
						'key'     => '_anwpfl_current_club',
						'value'   => (int) $_GET['_anwpfl_current_club'], // WPCS: CSRF ok.
						'compare' => '=',
					],
				]
			);
		}
	}

	/**
	 * Create CMB2 metaboxes
	 *
	 * @since 0.2.0 (2018-01-05)
	 */
	public function init_cmb2_metaboxes() {

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_anwpfl_';

		/**
		 * Initiate the metabox
		 */
		$cmb = new_cmb2_box(
			[
				'id'           => 'anwp_player_metabox',
				'title'        => esc_html__( 'Player Info', 'anwp-football-leagues' ),
				'object_types' => [ 'anwp_player' ],
				'context'      => 'normal',
				'priority'     => 'high',
				'classes'      => 'anwp-b-wrap',
				'show_names'   => true,
			]
		);

		// Short Name
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Short Name', 'anwp-football-leagues' ),
				'id'         => $prefix . 'short_name',
				'type'       => 'text',
				'before_row' => '<div id="anwp-tabs-general-player_metabox" class="anwp-metabox-tabs__content-item">',
			]
		);

		// Full Name
		$cmb->add_field(
			[
				'name' => esc_html__( 'Full Name', 'anwp-football-leagues' ),
				'id'   => $prefix . 'full_name',
				'type' => 'text',
			]
		);

		// Weight
		$cmb->add_field(
			[
				'name' => esc_html__( 'Weight (kg)', 'anwp-football-leagues' ),
				'id'   => $prefix . 'weight',
				'type' => 'text',
			]
		);

		// Height
		$cmb->add_field(
			[
				'name' => esc_html__( 'Height (cm)', 'anwp-football-leagues' ),
				'id'   => $prefix . 'height',
				'type' => 'text',
			]
		);

		$cmb->add_field(
			[
				'name'             => esc_html__( 'Position', 'anwp-football-leagues' ),
				'id'               => $prefix . 'position',
				'type'             => 'select',
				'show_option_none' => esc_html__( '- not selected -', 'anwp-football-leagues' ),
				'options_cb'       => [ $this->plugin->data, 'get_positions' ],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Current Club', 'anwp-football-leagues' ),
				'id'         => $prefix . 'current_club',
				'options_cb' => [ $this->plugin->club, 'get_clubs_options' ],
				'type'       => 'anwp_fl_select',
				'attributes' => [
					'placeholder' => esc_html__( '- not selected -', 'anwp-football-leagues' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'National Team', 'anwp-football-leagues' ),
				'id'         => $prefix . 'national_team',
				'options_cb' => [ $this->plugin->club, 'get_national_team_options' ],
				'type'       => 'anwp_fl_select',
				'attributes' => [
					'placeholder' => esc_html__( '- not selected -', 'anwp-football-leagues' ),
				],
			]
		);

		// Place of Birth
		$cmb->add_field(
			[
				'name' => esc_html__( 'Place of Birth', 'anwp-football-leagues' ),
				'id'   => $prefix . 'place_of_birth',
				'type' => 'text',
			]
		);

		// Place of Birth
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Country of Birth', 'anwp-football-leagues' ),
				'id'         => $prefix . 'country_of_birth',
				'type'       => 'anwp_fl_select',
				'options_cb' => [ $this->plugin->data, 'cb_get_countries' ],
			]
		);

		// Date of Birth
		$cmb->add_field(
			[
				'name'        => esc_html__( 'Date of Birth', 'anwp-football-leagues' ),
				'id'          => $prefix . 'date_of_birth',
				'type'        => 'text_date',
				'date_format' => 'Y-m-d',
			]
		);

		// Date of death
		$cmb->add_field(
			[
				'name'        => esc_html__( 'Date of death', 'anwp-football-leagues' ),
				'id'          => $prefix . 'date_of_death',
				'type'        => 'text_date',
				'date_format' => 'Y-m-d',
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Nationality', 'anwp-football-leagues' ),
				'id'         => $prefix . 'nationality',
				'type'       => 'anwp_fl_multiselect',
				'options_cb' => [ $this->plugin->data, 'cb_get_countries' ],
			]
		);

		$cmb->add_field(
			[
				'name'        => esc_html__( 'External ID', 'anwp-football-leagues' ),
				'id'          => $prefix . 'player_external_id',
				'type'        => 'text',
				'description' => esc_html__( 'Used on Data Import', 'anwp-football-leagues' ),
				'after_row'   => '</div>',
			]
		);

		$cmb->add_field(
			[
				'name'            => esc_html__( 'Description', 'anwp-football-leagues' ),
				'id'              => $prefix . 'description',
				'type'            => 'wysiwyg',
				'options'         => [
					'wpautop'       => true,
					'media_buttons' => true,
					'textarea_name' => 'anwp_player_description_input',
					'textarea_rows' => 10,
					'teeny'         => false, // output the minimal editor config used in Press This
					'dfw'           => false, // replace the default fullscreen with DFW (needs specific css)
					'tinymce'       => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
					'quicktags'     => true, // load Quicktags, can be used to pass settings directly to Quicktags using an array()
				],
				'show_names'      => false,
				'sanitization_cb' => false,
				'before_row'      => '<div id="anwp-tabs-desc-player_metabox" class="anwp-metabox-tabs__content-item d-none">',
				'after_row'       => '</div>',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Media
		|--------------------------------------------------------------------------
		*/

		// Photo
		$cmb->add_field(
			[
				'name'         => esc_html__( 'Photo', 'anwp-football-leagues' ),
				'id'           => $prefix . 'photo',
				'before_row'   => '<div id="anwp-tabs-media-player_metabox" class="anwp-metabox-tabs__content-item d-none">',
				'type'         => 'file',
				'options'      => [
					'url' => false, // Hide the text input for the url
				],
				// query_args are passed to wp.media's library query.
				'query_args'   => [
					'type' => 'image',
				],
				'preview_size' => 'medium', // Image size to use when previewing in the admin.
			]
		);

		// Photo
		$cmb->add_field(
			[
				'name'         => esc_html__( 'Gallery', 'anwp-football-leagues' ),
				'id'           => $prefix . 'gallery',
				'type'         => 'file_list',
				'options'      => [
					'url' => false, // Hide the text input for the url
				],
				// query_args are passed to wp.media's library query.
				'query_args'   => [
					'type' => 'image',
				],
				'preview_size' => 'medium', // Image size to use when previewing in the admin.
			]
		);

		// Notes
		$cmb->add_field(
			[
				'name'      => esc_html__( 'Text below gallery', 'anwp-football-leagues' ),
				'id'        => $prefix . 'gallery_notes',
				'type'      => 'textarea_small',
				'after_row' => '</div>',
			]
		);

		/*
		|--------------------------------------------------------------------
		| Social Tab
		|--------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Twitter', 'anwp-football-leagues' ),
				'id'         => $prefix . 'twitter',
				'before_row' => '<div id="anwp-tabs-social-club_metabox" class="anwp-metabox-tabs__content-item d-none">',
				'type'       => 'text_url',
				'protocols'  => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Facebook', 'anwp-football-leagues' ),
				'id'        => $prefix . 'facebook',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'YouTube', 'anwp-football-leagues' ),
				'id'        => $prefix . 'youtube',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'LinkedIn', 'anwp-football-leagues' ),
				'id'        => $prefix . 'linkedin',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'TikTok', 'anwp-football-leagues' ),
				'id'        => $prefix . 'tiktok',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'VKontakte', 'anwp-football-leagues' ),
				'id'        => $prefix . 'vk',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Instagram', 'anwp-football-leagues' ),
				'id'        => $prefix . 'instagram',
				'type'      => 'text_url',
				'protocols' => [ 'http', 'https' ],
				'after_row' => '</div>',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Custom Fields Metabox
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Title', 'anwp-football-leagues' ) . ' #1',
				'id'         => $prefix . 'custom_title_1',
				'before_row' => '<div id="anwp-tabs-custom_fields-player_metabox" class="anwp-metabox-tabs__content-item d-none">',
				'type'       => 'text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Value', 'anwp-football-leagues' ) . ' #1',
				'id'   => $prefix . 'custom_value_1',
				'type' => 'text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Title', 'anwp-football-leagues' ) . ' #2',
				'id'   => $prefix . 'custom_title_2',
				'type' => 'text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Value', 'anwp-football-leagues' ) . ' #2',
				'id'   => $prefix . 'custom_value_2',
				'type' => 'text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Title', 'anwp-football-leagues' ) . ' #3',
				'id'   => $prefix . 'custom_title_3',
				'type' => 'text',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Value', 'anwp-football-leagues' ) . ' #3',
				'id'   => $prefix . 'custom_value_3',
				'type' => 'text',
			]
		);

		// Dynamic Custom Fields
		$cmb->add_field(
			[
				'name'        => esc_html__( 'Dynamic Custom Fields', 'anwp-football-leagues' ),
				'id'          => $prefix . 'custom_fields',
				'type'        => 'anwp_fl_custom_fields',
				'option_slug' => 'player_custom_fields',
				'after_row'   => '</div>',
				'before_row'  => '<hr>',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Bottom Content
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Content', 'anwp-football-leagues' ),
				'id'         => $prefix . 'custom_content_below',
				'type'       => 'wysiwyg',
				'options'    => [
					'wpautop'       => true,
					'media_buttons' => true, // show insert/upload button(s)
					'textarea_name' => 'anwp_custom_content_below',
					'textarea_rows' => 5,
					'teeny'         => false, // output the minimal editor config used in Press This
					'dfw'           => false, // replace the default fullscreen with DFW (needs specific css)
					'tinymce'       => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
					'quicktags'     => true, // load Quicktags, can be used to pass settings directly to Quicktags using an array()
				],
				'show_names' => false,
				'before_row' => '<div id="anwp-tabs-bottom_content-player_metabox" class="anwp-metabox-tabs__content-item d-none">',
				'after_row'  => '</div>',
			]
		);

		/**
		 * Adds extra fields to the metabox.
		 *
		 * @since 0.9.0
		 */
		$extra_fields = apply_filters( 'anwpfl/cmb2_tabs_content/player', [] );

		if ( ! empty( $extra_fields ) && is_array( $extra_fields ) ) {
			foreach ( $extra_fields as $field ) {
				$cmb->add_field( $field );
			}
		}
	}

	/**
	 * Helper function, returns player photos.
	 *
	 * @since 0.13.3
	 * @return array $output_data - Array of clubs data (id => logo)
	 */
	public function get_player_photo_map() {

		static $output = null;

		if ( null === $output ) {

			$cache_key = 'FL-PLAYER-PHOTO-MAP';

			if ( anwp_football_leagues()->cache->get( $cache_key ) ) {
				$output = anwp_football_leagues()->cache->get( $cache_key );

				return $output;
			}

			$output = [];

			global $wpdb;

			$rows = $wpdb->get_results(
				"
				SELECT p.ID, pm1.meta_value as photo
				FROM $wpdb->posts p
				LEFT JOIN $wpdb->postmeta pm1 ON ( pm1.post_id = p.ID AND pm1.meta_key = '_anwpfl_photo' )
				WHERE p.post_status = 'publish' AND p.post_type = 'anwp_player' AND pm1.meta_value != ''
				"
			);

			if ( empty( $rows ) ) {
				return [];
			}

			foreach ( $rows as $row ) {
				$output[ $row->ID ] = $row->photo;
			}

			/*
			|--------------------------------------------------------------------
			| Save transient
			|--------------------------------------------------------------------
			*/
			if ( ! empty( $output ) ) {
				anwp_football_leagues()->cache->set( $cache_key, $output );
			}
		}

		return $output;
	}

	/**
	 * Get all players from DB.
	 *
	 * @return array
	 */
	private function get_all_players() {

		$cache_key = 'FL-PLAYERS-LIST';

		if ( anwp_football_leagues()->cache->get( $cache_key ) ) {
			return anwp_football_leagues()->cache->get( $cache_key );
		}

		global $wpdb;

		$all_players = $wpdb->get_results(
			"
			SELECT p.ID id, p.post_title pt,
				MAX( CASE WHEN pm.meta_key = '_anwpfl_short_name' THEN pm.meta_value ELSE '' END) as sn,
				MAX( CASE WHEN pm.meta_key = '_anwpfl_position' THEN pm.meta_value ELSE '' END) as pos,
				MAX( CASE WHEN pm.meta_key = '_anwpfl_nationality' THEN pm.meta_value ELSE '' END) as nat,
				MAX( CASE WHEN pm.meta_key = '_anwpfl_date_of_birth' THEN pm.meta_value ELSE '' END) as dob,
				MAX( CASE WHEN pm.meta_key = '_anwpfl_current_club' THEN pm.meta_value ELSE '' END) as t_id,
				MAX( CASE WHEN pm.meta_key = '_anwpfl_photo' THEN pm.meta_value ELSE '' END) as photo
			FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta pm ON ( pm.post_id = p.ID )
			WHERE p.post_status = 'publish' AND p.post_type = 'anwp_player'
			GROUP BY p.ID
			ORDER BY p.post_title
			",
			OBJECT_K
		);

		if ( empty( $all_players ) ) {
			return [];
		}

		foreach ( $all_players as $player ) {
			if ( $player->nat ) {
				$countries = maybe_unserialize( $player->nat );

				if ( ! empty( $countries ) && is_array( $countries ) ) {
					$player->nat = implode( ',', $countries );
				}
			}
		}

		anwp_football_leagues()->cache->set( $cache_key, $all_players );

		return $all_players;
	}

	/**
	 * Method returns players with id and title.
	 * Used in admin Squad assigning.
	 *
	 * @param array $squad_position_map
	 *
	 * @return array
	 * @since 0.2.0 (2018-01-11)
	 */
	public function get_players_list( $squad_position_map = [] ) {

		$all_players = $this->get_all_players();

		if ( empty( $all_players ) ) {
			return [];
		}

		$players_prepared = [];

		// Remove array keys
		$all_players = array_values( $all_players );

		// Add photos
		$player_photos = $this->get_player_photo_map();

		foreach ( $all_players as $player ) {

			$player_prepared = (object) [
				'id'         => absint( $player->id ),
				'name'       => $player->pt,
				'short_name' => $player->pt,
				'club_id'    => absint( $player->t_id ),
				'position'   => $player->pos,
				'country'    => '',
				'country2'   => '',
				'birthdate'  => empty( $player->dob ) ? '' : date_i18n( 'M j, Y', strtotime( $player->dob ) ),
				'photo'      => empty( $player_photos[ $player->id ] ) ? '' : $player_photos[ $player->id ],
			];

			if ( $player->nat ) {
				$countries = explode( ',', $player->nat );

				if ( ! empty( $countries[0] ) ) {
					$player_prepared->country = mb_strtolower( $countries[0] );
				}

				if ( ! empty( $countries[1] ) ) {
					$player_prepared->country2 = mb_strtolower( $countries[1] );
				}
			}

			if ( ! empty( $squad_position_map[ $player->id ] ) && $squad_position_map[ $player->id ] !== $player_prepared->position ) {
				$player_prepared->position = $squad_position_map[ $player->id ];
			}

			$players_prepared[] = $player_prepared;
		}

		return $players_prepared;
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @param array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 */
	public function sortable_columns( $sortable_columns ) {

		return array_merge( $sortable_columns, [ 'player_id' => 'ID' ] );
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @since  0.1.0
	 *
	 * @param  array $columns Array of registered column names/labels.
	 * @return array          Modified array.
	 */
	public function columns( $columns ) {

		// Add new columns
		$new_columns = [
			'anwpfl_player_position'     => esc_html__( 'Position', 'anwp-football-leagues' ),
			'anwpfl_player_current_club' => esc_html__( 'Current Club', 'anwp-football-leagues' ),
			'anwpfl_player_birthdate'    => esc_html__( 'Date of Birth', 'anwp-football-leagues' ),
			'player_id'                  => esc_html__( 'ID', 'anwp-football-leagues' ),
		];

		// Merge old and new columns
		$columns = array_merge( $new_columns, $columns );

		// Change columns order
		$new_columns_order = [
			'cb',
			'title',
			'anwpfl_player_position',
			'anwpfl_player_current_club',
			'anwpfl_player_birthdate',
			'comments',
			'date',
			'player_id',
		];

		$new_columns = [];

		foreach ( $new_columns_order as $c ) {

			if ( isset( $columns[ $c ] ) ) {
				$new_columns[ $c ] = $columns[ $c ];
			}
		}

		return $new_columns;
	}

	/**
	 * Handles admin column display. Hooked in via CPT_Core.
	 *
	 * @since  0.1.0
	 *
	 * @param array   $column   Column currently being rendered.
	 * @param integer $post_id  ID of post to display column for.
	 */
	public function columns_display( $column, $post_id ) {
		switch ( $column ) {

			case 'anwpfl_player_position':
				$position         = sanitize_key( get_post_meta( $post_id, '_anwpfl_position', true ) );
				$position_options = $this->plugin->data->get_positions();

				if ( ! empty( $position_options[ $position ] ) ) {
					echo esc_html( $position_options[ $position ] );
				}

				break;

			case 'anwpfl_player_current_club':
				$club_id       = (int) get_post_meta( $post_id, '_anwpfl_current_club', true );
				$clubs_options = $this->plugin->club->get_clubs_options();

				if ( ! empty( $clubs_options[ $club_id ] ) ) {
					echo esc_html( $clubs_options[ $club_id ] );
				}

				break;

			case 'anwpfl_player_birthdate':
				$birth_date = get_post_meta( $post_id, '_anwpfl_date_of_birth', true );

				echo $birth_date ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $birth_date ) ) ) : '';
				break;

			case 'player_id':
				echo (int) $post_id;
				break;
		}
	}

	/**
	 * Helper template function, returns the latest matches for selected player
	 *
	 * @param $player_id
	 * @param $season_id
	 *
	 * @since 0.5.0 (2018-03-10)
	 * @return array
	 */
	public function tmpl_get_latest_matches( $player_id, $season_id ) {

		/*
		|--------------------------------------------------------------------
		| Try to get from cache
		|--------------------------------------------------------------------
		*/
		$cache_key = 'FL-PLAYER_tmpl_get_latest_matches__' . md5( maybe_serialize( $player_id . '-' . $season_id ) );

		if ( anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' ) ) {
			return anwp_football_leagues()->cache->get( $cache_key, 'anwp_match' );
		}

		global $wpdb;

		if ( ! absint( $season_id ) && 'all' !== $season_id ) {
			return [];
		}

		if ( 'all' === $season_id ) {
			$matches = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT p.*, m.kickoff, m.main_stage_id, m.home_club, m.away_club, m.home_goals, m.away_goals
				FROM {$wpdb->prefix}anwpfl_players AS p
				INNER JOIN {$wpdb->prefix}anwpfl_matches AS m ON m.match_id = p.match_id
				WHERE p.player_id = %d
				ORDER BY m.kickoff DESC
				",
					$player_id
				)
			);
		} else {
			$matches = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT p.*, m.kickoff, m.main_stage_id, m.home_club, m.away_club, m.home_goals, m.away_goals
				FROM {$wpdb->prefix}anwpfl_players AS p
				INNER JOIN {$wpdb->prefix}anwpfl_matches AS m ON m.match_id = p.match_id
				WHERE p.player_id = %d
					AND p.season_id = %d
				ORDER BY m.kickoff DESC
				",
					$player_id,
					$season_id
				)
			);
		}

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $matches ) ) {
			anwp_football_leagues()->cache->set( $cache_key, $matches, 'anwp_match' );
		}

		return $matches;
	}

	/**
	 * Helper template function. Returns prepared data.
	 *
	 * @param array $matches - Data array with latest matches
	 *
	 * @since 0.5.0 (2018-03-11)
	 * @return array
	 */
	public function tmpl_prepare_competition_matches( $matches ) {

		$data = [];

		if ( empty( $matches ) ) {
			return $data;
		}

		// Set game links
		$game_ids  = wp_list_pluck( $matches, 'match_id' );
		$links_map = $this->plugin->helper->get_permalinks_by_ids( $game_ids, 'anwp_match' );

		$player_yr_card_count = AnWPFL_Options::get_value( 'player_yr_card_count', 'yyr' );

		// Get competition ids from matches
		$competition_ids = [];
		foreach ( $matches as $match ) {
			$competition_ids[] = intval( $match->main_stage_id ) ? (int) $match->main_stage_id : $match->competition_id;
		}

		// Get competition data
		$competitions = get_posts(
			[
				'numberposts'      => - 1,
				'post_type'        => 'anwp_competition',
				'suppress_filters' => false,
				'post_status'      => [ 'publish', 'stage_secondary' ],
				'include'          => $competition_ids,
				'orderby'          => 'post__in',
			]
		);

		/** @var WP_Post $competition */
		foreach ( $competitions as $competition ) {

			if ( 'secondary' !== get_post_meta( $competition->ID, '_anwpfl_multistage', true ) ) {
				$data[ $competition->ID ] = [
					'title'   => $competition->post_title,
					'id'      => $competition->ID,
					'matches' => [],
					'totals'  => array_fill_keys( [ 'started', 'sub_in', 'minutes', 'card_y', 'card_yr', 'card_r', 'goals', 'assist', 'goals_own', 'goals_penalty', 'goals_conceded', 'clean_sheets' ], 0 ),
					'logo'    => get_post_meta( $competition->ID, '_anwpfl_logo', true ),
					'order'   => (int) get_post_meta( $competition->ID, '_anwpfl_competition_order', true ),
				];
			}
		}

		// Add matches to competitions
		foreach ( $matches as $match ) {

			$competition_index = ( isset( $data[ $match->main_stage_id ] ) && (int) $data[ $match->main_stage_id ] )
				? (int) $match->main_stage_id
				: (int) $match->competition_id;

			if ( isset( $data[ $competition_index ] ) ) {

				// Add link
				$match->link = isset( $links_map[ $match->match_id ] ) ? $links_map[ $match->match_id ] : '';

				// Output Game
				$data[ $competition_index ]['matches'][] = $match;

				// Calculate totals
				$data[ $competition_index ]['totals']['started']        += (int) in_array( $match->appearance, [ '1', '2' ], true );
				$data[ $competition_index ]['totals']['sub_in']         += (int) in_array( $match->appearance, [ '3', '4' ], true );
				$data[ $competition_index ]['totals']['minutes']        += (int) $match->time_out - (int) $match->time_in;
				$data[ $competition_index ]['totals']['card_y']         += ( 'yr' === $player_yr_card_count && $match->card_yr > 0 ? 0 : (int) $match->card_y );
				$data[ $competition_index ]['totals']['card_yr']        += (int) $match->card_yr;
				$data[ $competition_index ]['totals']['card_r']         += (int) $match->card_r;
				$data[ $competition_index ]['totals']['goals']          += (int) $match->goals;
				$data[ $competition_index ]['totals']['assist']         += (int) $match->assist;
				$data[ $competition_index ]['totals']['goals_own']      += (int) $match->goals_own;
				$data[ $competition_index ]['totals']['goals_penalty']  += (int) $match->goals_penalty;
				$data[ $competition_index ]['totals']['goals_conceded'] += (int) $match->goals_conceded;

				if ( '1' === $match->appearance && 0 === (int) $match->goals_conceded ) {
					$data[ $competition_index ]['totals']['clean_sheets'] ++;
				}

				// Fix minutes after half time substitution (1 min correction)
				// @since v0.6.5 (2018-08-17)
				if ( 46 === intval( $match->time_out ) ) {
					$data[ $competition_index ]['totals']['minutes'] = $data[ $competition_index ]['totals']['minutes'] - 1;
				} elseif ( 46 === intval( $match->time_in ) ) {
					$data[ $competition_index ]['totals']['minutes'] = $data[ $competition_index ]['totals']['minutes'] + 1;
				}
			}
		}

		usort(
			$data,
			function ( $a, $b ) {
				return $a['order'] - $b['order'];
			}
		);

		return $data;
	}

	/**
	 * Get players.
	 *
	 * @param object $options
	 *
	 * @since 0.5.1 (2018-03-22)
	 * @return array|null|object
	 */
	public function tmpl_get_players_by_type( $options ) {

		global $wpdb;

		$options = (object) wp_parse_args(
			$options,
			[
				'competition_id'    => '',
				'join_secondary'    => 0,
				'season_id'         => '',
				'league_id'         => '',
				'club_id'           => '',
				'type'              => 'scorers',
				'limit'             => 0,
				'soft_limit'        => '',
				'soft_limit_qty'    => '',
				'hide_zero'         => 0,
				'penalty_goals'     => 0,
				'games_played'      => 0,
				'secondary_sorting' => '',
			]
		);

		if ( 'assists' === $options->type ) {
			$select_extra = [ 'SUM( assist ) as countable' ];
		} else {

			// Prepare select by type (default for "scorers")
			$select_extra = [ 'SUM( goals ) as countable' ];

			if ( $options->penalty_goals || 'less_penalty' === $options->secondary_sorting ) {
				$select_extra[] = 'SUM( goals_penalty ) as penalty';
			}
		}

		if ( $options->games_played || 'less_games' === $options->secondary_sorting ) {
			$select_extra[] = 'SUM(CASE WHEN (appearance > 0) THEN 1 ELSE 0 END) as played';
		}

		$select_extra = implode( ', ', $select_extra );

		$query = "
		SELECT player_id, {$select_extra}, GROUP_CONCAT(DISTINCT club_id) as clubs
		FROM {$wpdb->prefix}anwpfl_players
		WHERE 1=1
		";

		/*
		|--------------------------------------------------------------------
		| WHERE filter by competition
		|--------------------------------------------------------------------
		*/
		// Get competition to filter
		if ( absint( $options->join_secondary ) && ! empty( $options->competition_id ) ) {
			$competition_ids = wp_parse_id_list( $options->competition_id );
			$format          = implode( ', ', array_fill( 0, count( $competition_ids ), '%d' ) );

			$query .= $wpdb->prepare( " AND ( competition_id IN ({$format}) OR main_stage_id IN ({$format}) ) ", array_merge( $competition_ids, $competition_ids ) ); // phpcs:ignore
		} elseif ( ! empty( $options->competition_id ) ) {
			$competition_ids = wp_parse_id_list( $options->competition_id );
			$format          = implode( ', ', array_fill( 0, count( $competition_ids ), '%d' ) );

			$query .= $wpdb->prepare( " AND competition_id IN ({$format}) ", $competition_ids ); // phpcs:ignore
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by season
		|--------------------------------------------------------------------
		*/
		if ( (int) $options->season_id && '' === $options->competition_id ) {
			$query .= $wpdb->prepare( ' AND season_id = %d ', $options->season_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by league
		|--------------------------------------------------------------------
		*/
		if ( (int) $options->league_id && '' === $options->competition_id ) {
			$query .= $wpdb->prepare( ' AND league_id = %d ', $options->league_id );
		}

		/*
		|--------------------------------------------------------------------
		| WHERE filter by club
		|--------------------------------------------------------------------
		*/
		if ( (int) $options->club_id ) {
			$clubs  = wp_parse_id_list( $options->club_id );
			$format = implode( ', ', array_fill( 0, count( $clubs ), '%d' ) );

			$query .= $wpdb->prepare( " AND club_id IN ({$format}) ", $clubs ); // phpcs:ignore
		}

		/*
		|--------------------------------------------------------------------
		| WHERE official only
		|--------------------------------------------------------------------
		*/
		$query .= ' AND competition_status != "friendly" ';

		/*
		|--------------------------------------------------------------------
		| Handle players Type
		|--------------------------------------------------------------------
		*/
		$query .= ' GROUP BY player_id';

		/*
		|--------------------------------------------------------------------
		| Hide Zeroes
		|--------------------------------------------------------------------
		*/
		if ( absint( $options->soft_limit_qty ) && AnWP_Football_Leagues::string_to_bool( $options->hide_zero ) ) {
			$query .= $wpdb->prepare( ' HAVING countable >= %d AND countable != 0 ', $options->soft_limit_qty );
		} elseif ( absint( $options->soft_limit_qty ) ) {
			$query .= $wpdb->prepare( ' HAVING countable >= %d ', $options->soft_limit_qty );
		} elseif ( AnWP_Football_Leagues::string_to_bool( $options->hide_zero ) ) {
			$query .= ' HAVING countable > 0 ';
		}

		/*
		|--------------------------------------------------------------------
		| Order
		|--------------------------------------------------------------------
		*/
		if ( 'less_games' === $options->secondary_sorting ) {
			$query .= ' ORDER BY countable DESC, played ASC ';
		} elseif ( 'less_penalty' === $options->secondary_sorting && 'assists' !== $options->type ) {
			$query .= ' ORDER BY countable DESC, penalty ASC ';
		} else {
			$query .= ' ORDER BY countable DESC';
		}

		/*
		|--------------------------------------------------------------------
		| LIMIT clause
		|--------------------------------------------------------------------
		*/
		if ( absint( $options->limit ) ) {

			$query .= $wpdb->prepare( ' LIMIT %d', $options->limit );

			if ( AnWP_Football_Leagues::string_to_bool( $options->soft_limit ) ) {
				$soft_limit_qty = $wpdb->get_row( $query, OBJECT, ( $options->limit - 1 ) ); // phpcs:ignore WordPress.DB.PreparedSQL

				if ( ! empty( $soft_limit_qty ) && isset( $soft_limit_qty->countable ) ) {
					$options->limit          = 0;
					$options->soft_limit     = 0;
					$options->soft_limit_qty = $soft_limit_qty->countable;

					return $this->tmpl_get_players_by_type( $options );
				}
			}
		}

		return $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Get players and teams Cards.
	 *
	 * @param object $options
	 *
	 * @since 0.7.3 (2018-09-23)
	 * @return array|null|object
	 */
	public function tmpl_get_players_cards( $options ) {

		global $wpdb;

		$player_yr_card_count = AnWPFL_Options::get_value( 'player_yr_card_count', 'yyr' );

		$options = (object) wp_parse_args(
			$options,
			[
				'competition_id' => '',
				'join_secondary' => 0,
				'season_id'      => '',
				'league_id'      => '',
				'club_id'        => '',
				'type'           => 'players',
				'limit'          => 0,
				'soft_limit'     => '',
				'points_r'       => '5',
				'points_yr'      => '2',
				'sort_by_point'  => '',
				'hide_zero'      => 0,
			]
		);

		// Prepare countable field
		if ( 'yr' === $player_yr_card_count && 'clubs' !== $options->type ) {
			$countable = ' SUM(CASE WHEN card_yr > 0 THEN 0 ELSE card_y END) as cards_y, SUM( card_yr ) as cards_yr, SUM( card_r ) as cards_r, SUM( card_r * ' . (int) $options->points_r . ' + card_yr * ' . (int) $options->points_yr . ' + ( CASE WHEN card_yr > 0 THEN 0 ELSE card_y END ) ) as countable ';
		} else {
			$countable = ' SUM( card_y ) as cards_y, SUM( card_yr ) as cards_yr, SUM( card_r ) as cards_r, SUM( card_r * ' . (int) $options->points_r . ' + card_yr * ' . (int) $options->points_yr . ' + card_y * 1 ) as countable ';
		}

		if ( 'clubs' === $options->type ) {
			$query = "SELECT club_id, {$countable}";
		} else {
			$query = "SELECT player_id, {$countable}, GROUP_CONCAT(DISTINCT club_id) as clubs";
		}

		$query .= "
		FROM {$wpdb->prefix}anwpfl_players
		WHERE 1=1
		";

		/**==================
		 * WHERE filter by competition
		 *================ */
		// Get competition to filter
		if ( anwp_football_leagues()->helper->string_to_bool( $options->join_secondary ) && '' !== $options->competition_id ) {
			$query .= $wpdb->prepare( ' AND (competition_id = %d OR main_stage_id = %d) ', $options->competition_id, $options->competition_id );
		} elseif ( '' !== $options->competition_id ) {
			$query .= $wpdb->prepare( ' AND competition_id = %d ', $options->competition_id );
		}

		/**==================
		 * WHERE filter by season
		 *================ */
		if ( (int) $options->season_id && '' === $options->competition_id ) {
			$query .= $wpdb->prepare( ' AND season_id = %d ', $options->season_id );
		}

		/**==================
		 * WHERE filter by league
		 *================ */
		if ( (int) $options->league_id && '' === $options->competition_id ) {
			$query .= $wpdb->prepare( ' AND league_id = %d ', $options->league_id );
		}

		/**==================
		 * WHERE filter by club
		 *================ */
		if ( (int) $options->club_id ) {
			$query .= $wpdb->prepare( ' AND club_id = %d ', $options->club_id );
		}

		/**==================
		 * WHERE not null
		 *================ */
		if ( anwp_football_leagues()->helper->string_to_bool( $options->hide_zero ) ) {
			$query .= ' AND ( card_y > 0 OR card_yr > 0 OR card_r > 0 )';
		}

		/**==================
		 * WHERE official only
		 *================ */
		$query .= ' AND competition_status != "friendly" ';

		/**==================
		 * Handle players Type
		 *================ */
		$query .= ( 'clubs' === $options->type ) ? ' GROUP BY club_id' : ' GROUP BY player_id';

		/**==================
		 * Ordering
		 *================ */
		$query .= ' ORDER BY countable ' . ( $options->sort_by_point ? 'ASC' : 'DESC' );

		/**==================
		 * LIMIT clause
		 *================ */
		if ( (int) $options->limit && '' === $options->soft_limit ) {
			$query .= $wpdb->prepare( ' LIMIT %d', $options->limit );
		}

		$items = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $items;
	}

	/**
	 * Limit number of fetch players.
	 *
	 * @param array  $players
	 * @param int    $limit
	 * @param string $soft_limit Yes or empty
	 *
	 * @since 0.5.1 (2018-03-22)
	 * @return array
	 */
	public function tmpl_limit_players( $players, $limit, $soft_limit ) {

		$limit = absint( $limit );

		// Return data if limit is not set or number of players is too small
		if ( 0 === $limit || ( $limit > 0 && $limit > count( $players ) ) ) {
			return $players;
		}

		if ( $limit > 0 && ! anwp_football_leagues()->helper->string_to_bool( $soft_limit ) ) {
			return array_slice( $players, 0, $limit );
		}

		if ( anwp_football_leagues()->helper->string_to_bool( $soft_limit ) ) {
			$countable = $players[ $limit - 1 ]->countable;

			$new_players = [];
			foreach ( $players as $player ) {
				if ( $player->countable < $countable ) {
					break;
				}

				$new_players[] = $player;
			}

			return $new_players;
		}

		return $players;
	}

	/**
	 * Filter goalkeepers player_ids from squad match data.
	 *
	 * @param string $line_up Comma separated list of players
	 * @param string $subs    Comma separated list of players
	 *
	 * @return array
	 * @since 0.8.1
	 */
	public function filter_goalkeepers_from_squad( $line_up, $subs ) {

		$ids = [];

		if ( $line_up ) {
			$ids = array_merge( $ids, explode( ',', $line_up ) );
		}

		if ( $subs ) {
			$ids = array_merge( $ids, explode( ',', $subs ) );
		}

		$ids = array_values( array_map( 'intval', $ids ) );

		if ( empty( $ids ) ) {
			return [];
		}

		return get_posts(
			[
				'fields'     => 'ids',
				'include'    => $ids,
				'post_type'  => 'anwp_player',
				'meta_key'   => '_anwpfl_position',
				'meta_value' => 'g',
			]
		);
	}

	/**
	 * Get player data.
	 *
	 * @param $player_id
	 *
	 * @return object - Data object.
	 * @since 0.8.5
	 */
	public function get_player( $player_id ) {

		$player_id = absint( $player_id );

		$defaults = (object) [
			'name'        => '',
			'nationality' => [],
			'club_id'     => '',
			'photo'       => '',
			'id'          => '',
			'name_short'  => '',
			'link'        => '',
			'position'    => '',
			'birth_date'  => '',
		];

		if ( ! $player_id ) {
			return $defaults;
		}

		// Get player data
		$data = wp_cache_get( 'player-' . $player_id, 'anwp_fl' );

		if ( is_object( $data ) ) {
			return $data;
		}

		if ( false === $this->set_player_cache( get_post( $player_id ) ) ) {
			return $defaults;
		}

		return wp_cache_get( 'player-' . $player_id, 'anwp_fl' );
	}

	/**
	 * Set players cache.
	 *
	 * @param array $ids
	 *
	 * @since 0.8.5
	 */
	public function set_players_cache( $ids ) {

		if ( empty( $ids ) || ! is_array( $ids ) ) {
			return;
		}

		$ids = wp_parse_id_list( $ids );

		$ids_to_cache = [];

		// Check already cached
		foreach ( $ids as $id ) {
			if ( ! wp_cache_get( 'player-' . $id, 'anwp_fl' ) ) {
				$ids_to_cache[] = $id;
			}
		}

		$players = get_posts(
			[
				'include'                => $ids,
				'post_type'              => 'anwp_player',
				'update_post_term_cache' => false,
			]
		);

		foreach ( $players as $player ) {
			$this->set_player_cache( $player );
		}
	}

	/**
	 * Populate player cache.
	 *
	 * @param WP_Post $player
	 *
	 * @return bool
	 * @since 0.8.5
	 */
	public function set_player_cache( $player ) {

		if ( ! $player instanceof WP_Post ) {
			return false;
		}

		$data = (object) [
			'name'        => $player->post_title,
			'nationality' => maybe_unserialize( $player->_anwpfl_nationality ),
			'club_id'     => $player->_anwpfl_current_club,
			'photo'       => $player->_anwpfl_photo,
			'id'          => $player->ID,
			'name_short'  => ( $this->use_short_name() && $player->_anwpfl_short_name ) ? $player->_anwpfl_short_name : $player->post_title,
			'link'        => get_permalink( $player ),
			'position'    => $player->_anwpfl_position,
			'birth_date'  => $player->_anwpfl_date_of_birth,
		];

		return wp_cache_set( 'player-' . $player->ID, $data, 'anwp_fl' );
	}

	/**
	 * Fetches players ids from match data and cache them.
	 *
	 * @param array $match_data
	 *
	 * @since 0.8.5
	 */
	public function prepare_match_players_cache( $match_data ) {

		$ids = [];

		// Parse events
		if ( ! empty( $match_data['events'] ) && is_array( $match_data['events'] ) ) {
			foreach ( $match_data['events'] as $event_group_slug => $event_group ) {

				if ( 'players' === $event_group_slug ) {
					continue;
				}

				if ( ! empty( $event_group ) && is_array( $event_group ) ) {
					foreach ( $event_group as $event ) {
						if ( ! empty( $event->player ) ) {
							$ids[] = $event->player;
						}

						if ( ! empty( $event->assistant ) ) {
							$ids[] = $event->assistant;
						}

						if ( ! empty( $event->playerOut ) ) { // phpcs:ignore
							$ids[] = $event->playerOut; // phpcs:ignore
						}
					}
				}
			}
		}

		// Parse lineups and substitutes
		$fields = [ 'line_up_home', 'line_up_away', 'subs_home', 'subs_away' ];

		foreach ( $fields as $field ) {
			if ( ! empty( $match_data[ $field ] ) ) {
				$ids = array_merge( $ids, wp_parse_id_list( $match_data[ $field ] ) );
			}
		}

		$ids = wp_parse_id_list( $ids );

		if ( ! empty( $ids ) ) {
			$this->set_players_cache( $ids );
		}
	}

	/**
	 * Fetches players ids from squad and cache them.
	 *
	 * @param array $squad - Array of squad items (objects)
	 *
	 * @since 0.8.5
	 */
	public function prepare_squad_players_cache( $squad ) {

		$ids = [];

		// Parse events
		if ( ! empty( $squad ) && is_array( $squad ) ) {
			$ids = wp_list_pluck( $squad, 'id' );
		}

		if ( ! empty( $ids ) ) {
			$this->set_players_cache( $ids );
		}
	}

	/**
	 * Checks if use of short name allowed.
	 *
	 * @return bool
	 * @since 0.8.5
	 */
	public function use_short_name() {

		static $use_player_short_name = null;

		if ( null === $use_player_short_name ) {
			/**
			 * Filter: anwpfl/player/use_short_name
			 *
			 * @since 0.8.1
			 *
			 * @param int $competition_post_id Post ID
			 */
			$use_player_short_name = apply_filters( 'anwpfl/player/use_short_name', true );
		}

		return $use_player_short_name;
	}

	/**
	 * Fires immediately after the post's cache is cleaned.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 *
	 * @since 0.8.5
	 */
	public function maybe_clean_player_cache( $post_id, $post ) {
		if ( 'anwp_player' === $post->post_type ) {
			$this->reset_player_cache( $post_id );
		}
	}

	/**
	 * Clears player cache.
	 *
	 * @param int $player_id
	 *
	 * @since 0.8.5
	 */
	public function reset_player_cache( $player_id ) {
		wp_cache_delete( 'player-' . $player_id, 'anwp_fl' );
	}

	/**
	 * Helper function, returns player with id and name.
	 *
	 * @since 0.10.8
	 * @return array $output_data
	 */
	public function get_player_options() {

		static $options = null;

		if ( null === $options ) {

			$options = [];

			$posts = get_posts(
				[
					'numberposts' => - 1,
					'post_type'   => 'anwp_player',
				]
			);

			/** @var  $p WP_Post */
			foreach ( $posts as $p ) {
				$options[ $p->ID ] = $p->post_title;
			}

			asort( $options );
		}

		return $options;
	}

	/**
	 * Get player translated position
	 *
	 * @param $player_id
	 * @param $position_code
	 *
	 * @return string
	 * @since 0.10.19
	 */
	public function get_translated_position( $player_id, $position_code = '' ) {

		if ( empty( $position_code ) ) {
			$position_code = get_post_meta( $player_id, '_anwpfl_position', true );
		}

		$position = anwp_football_leagues()->data->get_value_by_key( $position_code, 'position' );

		// Check position translation
		$translated_position = '';

		switch ( $position_code ) {
			case 'g':
				$translated_position = anwp_football_leagues()->get_option_value( 'text_single_goalkeeper' );
				break;
			case 'd':
				$translated_position = anwp_football_leagues()->get_option_value( 'text_single_defender' );
				break;
			case 'm':
				$translated_position = anwp_football_leagues()->get_option_value( 'text_single_midfielder' );
				break;
			case 'f':
				$translated_position = anwp_football_leagues()->get_option_value( 'text_single_forward' );
				break;
		}

		return $translated_position ?: $position;
	}

	/**
	 * Get players and staff with upcoming birthdays.
	 *
	 * @param $options
	 *
	 * @return array
	 * @since 0.10.19
	 */
	public function get_birthdays( $options ) {

		$cur_date = apply_filters( 'anwpfl/config/localize_date_arg', true ) ? date_i18n( 'Y-m-d' ) : date( 'Y-m-d' );

		/*
		|--------------------------------------------------------------------
		| Try to get from cache
		|--------------------------------------------------------------------
		*/
		$cache_key = 'FL-PLAYER_get_birthdays__' . $cur_date . '-' . md5( maybe_serialize( $options ) );

		if ( false !== anwp_football_leagues()->cache->get( $cache_key, 'anwp_player', false ) ) {
			return anwp_football_leagues()->cache->get( $cache_key, 'anwp_player' );
		}

		// Load data in default way
		global $wpdb;

		$options = (object) wp_parse_args(
			$options,
			[
				'club_id'     => '',
				'type'        => 'players',
				'days_before' => 5,
				'days_after'  => 3,
			]
		);

		$hide_dead_players = apply_filters( 'anwpfl/player/birthdays_hide_dead', true );

		$query = "
		SELECT p.ID, pm2.meta_value current_club, pm1.meta_value date_of_birth, p.post_title player_name, p.post_type, DATE_FORMAT( pm1.meta_value, '%m-%d' ) meta_date_short
		FROM $wpdb->posts p
		LEFT JOIN $wpdb->postmeta pm1 ON ( pm1.post_id = p.ID AND pm1.meta_key = '_anwpfl_date_of_birth' )
		LEFT JOIN $wpdb->postmeta pm3 ON ( pm3.post_id = p.ID AND pm3.meta_key = '_anwpfl_date_of_death' )
		LEFT JOIN $wpdb->postmeta pm2 ON ( pm2.post_id = p.ID AND pm2.meta_key = '_anwpfl_current_club' )
		WHERE p.post_status = 'publish' AND pm1.meta_value IS NOT NULL AND pm1.meta_value != ''
		";

		/**==================
		 * Show dead
		 *================ */
		if ( $hide_dead_players ) {
			$query .= ' AND pm3.meta_value IS NULL ';
		}

		/**==================
		 * WHERE filter by club_id
		 *================ */
		if ( absint( $options->club_id ) ) {
			$clubs  = wp_parse_id_list( $options->club_id );
			$format = implode( ', ', array_fill( 0, count( $clubs ), '%d' ) );

			$query .= $wpdb->prepare( " AND pm2.meta_value IN ({$format}) ", $clubs ); // phpcs:ignore
		}

		/**==================
		 * WHERE filter by type
		 *================ */
		if ( 'all' === $options->type ) {
			$query .= ' AND ( p.post_type = "anwp_player" OR p.post_type = "anwp_staff" )';
		} elseif ( 'staff' === $options->type ) {
			$query .= ' AND p.post_type = "anwp_staff"';
		} else {
			$query .= ' AND p.post_type = "anwp_player"';
		}

		/**==================
		 * WHERE filter by date
		 *================ */
		$query .= $wpdb->prepare( ' AND pm1.meta_value >= DATE_SUB( DATE_SUB( %s, INTERVAL YEAR( %s ) - YEAR( pm1.meta_value ) YEAR ), INTERVAL %d DAY )', $cur_date, $cur_date, $options->days_before );
		$query .= $wpdb->prepare( ' AND pm1.meta_value <= DATE_ADD( DATE_SUB( %s, INTERVAL YEAR( %s ) - YEAR( pm1.meta_value ) YEAR ), INTERVAL %d DAY )', $cur_date, $cur_date, $options->days_after );

		$query .= ' GROUP BY p.ID';
		$query .= ' ORDER BY meta_date_short';

		/**==================
		 * Bump Query
		 *================ */
		$players = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		// Populate Object Cache
		$ids = wp_list_pluck( $players, 'ID' );

		if ( ! empty( $ids ) && is_array( $ids ) ) {

			$positions_map = $this->get_positions_map();
			$photo_map     = $this->get_player_photo_map();
			$staff_list    = [];

			if ( 'staff' === $options->type || 'all' === $options->type ) {
				foreach ( anwp_football_leagues()->staff->get_staff_list() as $staff_person ) {
					$staff_list[ $staff_person->id ] = $staff_person;
				}
			}

			// Add extra data to players
			foreach ( $players as $player ) {
				if ( 'anwp_staff' === $player->post_type ) {
					$player->photo = isset( $staff_list[ $player->ID ]->photo ) ? $staff_list[ $player->ID ]->photo : '';
				} else {
					$player->photo = isset( $photo_map[ $player->ID ] ) ? $photo_map[ $player->ID ] : '';
				}

				$player->position = isset( $positions_map[ $player->ID ] ) ? $positions_map[ $player->ID ] : '';
			}
		}

		/*
		|--------------------------------------------------------------------
		| Save transient
		|--------------------------------------------------------------------
		*/
		if ( is_array( $players ) ) {
			anwp_football_leagues()->cache->set( $cache_key, $players, 'anwp_player' );
		}

		return $players;
	}

	/**
	 * Get player and staff position map.
	 *
	 * @since 0.13.3
	 * @return array $output
	 */
	public function get_positions_map() {

		static $output = null;

		if ( null === $output ) {
			$output = [];

			global $wpdb;

			$rows = $wpdb->get_results(
				"
					SELECT post_id, meta_value
					FROM $wpdb->postmeta
					WHERE ( meta_key = '_anwpfl_job_title' OR meta_key = '_anwpfl_position' ) AND meta_value != ''
				"
			);

			if ( empty( $rows ) ) {
				return [];
			}

			foreach ( $rows as $row ) {
				$output[ $row->post_id ] = $row->meta_value;
			}
		}

		return $output;
	}

	/**
	 * Get Post ID by External id
	 *
	 * @param $external_id
	 *
	 * @return string|null
	 * @since 0.12.0
	 */
	public function get_player_id_by_external_id( $external_id ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_anwpfl_player_external_id' AND meta_value = %s
				",
				$external_id
			)
		);
	}

	/**
	 * Get Player data
	 *
	 * @param int    $player_id Player ID
	 * @param string $position
	 *
	 * @return (object) [ // <pre>
	 *        'id'              => int,
	 *        'name'            => (string),
	 *        'short_name'      => (string),
	 *        'club_id'         => int,
	 *        'country'         => (string),
	 *        'country2'        => (string),
	 *        'position_code'   => (string),
	 *        'position'        => (string),
	 *        'birthdate'       => (string),
	 *        'photo'           => (string),
	 * ]|bool
	 * @since 0.13.7
	 */
	public function get_player_obj( $player_id, $position = '' ) {

		static $players_cache = null;

		if ( null === $players_cache ) {
			$players_cache = $this->get_all_players();
		}

		if ( empty( $players_cache ) || ! absint( $player_id ) ) {
			return false;
		}

		static $output_data = [];

		if ( isset( $output_data[ $player_id ] ) ) {
			return $output_data[ $player_id ];
		}

		$player_cached_obj = isset( $players_cache[ $player_id ] ) ? $players_cache[ $player_id ] : false;

		if ( empty( $player_cached_obj ) ) {
			return false;
		}

		$player_photos = $this->get_player_photo_map();

		$output_data[ $player_id ] = (object) [
			'id'            => absint( $player_cached_obj->id ),
			'name'          => $player_cached_obj->pt,
			'short_name'    => $player_cached_obj->sn,
			'club_id'       => absint( $player_cached_obj->t_id ),
			'country'       => '',
			'country2'      => '',
			'position_code' => $position ?: $player_cached_obj->pos,
			'position'      => anwp_football_leagues()->player->get_translated_position( $player_cached_obj->id, $position ?: $player_cached_obj->pos ),
			'birthdate'     => empty( $player_cached_obj->dob ) ? '' : date_i18n( 'M j, Y', strtotime( $player_cached_obj->dob ) ),
			'photo'         => empty( $player_photos[ $player_cached_obj->id ] ) ? '' : $player_photos[ $player_cached_obj->id ],
		];

		if ( $player_cached_obj->nat ) {
			$countries = explode( ',', $player_cached_obj->nat );

			if ( ! empty( $countries[0] ) ) {
				$output_data[ $player_id ]->country = mb_strtolower( $countries[0] );
			}

			if ( ! empty( $countries[1] ) ) {
				$output_data[ $player_id ]->country2 = mb_strtolower( $countries[1] );
			}
		}

		return $output_data[ $player_id ];
	}
}
