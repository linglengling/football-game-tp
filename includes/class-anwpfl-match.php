<?php
/**
 * AnWP Football Leagues :: Match.
 *
 * @since   0.1.0
 * @package AnWP_Football_Leagues
 */

require_once dirname( __FILE__ ) . '/../vendor/cpt-core/CPT_Core.php';

/**
 * AnWP Football Leagues :: Match post type class.
 *
 * phpcs:disable WordPress.NamingConventions
 *
 * @since 0.1.0
 *
 * @see   https://github.com/WebDevStudios/CPT_Core
 */
class AnWPFL_Match extends CPT_Core {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

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
		$permalink_slug      = empty( $permalink_structure['match'] ) ? 'match' : $permalink_structure['match'];

		// Register this cpt.
		parent::__construct(
			[ // array with Singular, Plural, and Registered name
				esc_html__( 'Match', 'anwp-football-leagues' ),
				esc_html__( 'Matches', 'anwp-football-leagues' ),
				'anwp_match',
			],
			[
				'supports'            => [
					'comments',
				],
				'rewrite'             => [ 'slug' => $permalink_slug ],
				'show_in_menu'        => true,
				'menu_position'       => 36,
				'menu_icon'           => $plugin::SVG_VS,
				'public'              => true,
				'exclude_from_search' => 'hide' === AnWPFL_Options::get_value( 'display_front_end_search_match' ),
				'labels'              => [
					'all_items'    => esc_html__( 'Matches', 'anwp-football-leagues' ),
					'add_new'      => esc_html__( 'Add New Match', 'anwp-football-leagues' ),
					'add_new_item' => esc_html__( 'Add New Match', 'anwp-football-leagues' ),
					'edit_item'    => esc_html__( 'Edit Match', 'anwp-football-leagues' ),
					'new_item'     => esc_html__( 'New Match', 'anwp-football-leagues' ),
					'view_item'    => esc_html__( 'View Match', 'anwp-football-leagues' ),
				],
			]
		);
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		add_action( 'load-post.php', [ $this, 'init_metaboxes' ] );
		add_action( 'load-post-new.php', [ $this, 'init_metaboxes' ] );
		add_action( 'save_post', [ $this, 'save_metabox' ], 10, 2 );

		// Create CMB2 metabox
		add_action( 'cmb2_admin_init', [ $this, 'init_cmb2_metaboxes' ] );

		// Remove stats on post delete
		add_action( 'before_delete_post', [ $this, 'on_match_delete' ] );
		add_action( 'trashed_post', [ $this, 'on_match_delete' ] );

		add_action( 'untrashed_post', [ $this, 'on_match_untrashed' ] );
		add_action( 'admin_notices', [ $this, 'on_match_untrashed_notices' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		add_action( 'manage_anwp_match_posts_custom_column', [ $this, 'match_columns_display' ], 10, 2 );

		// Admin Table filters
		add_filter( 'disable_months_dropdown', [ $this, 'disable_months_dropdown' ], 10, 2 );
		add_action( 'restrict_manage_posts', [ $this, 'add_more_filters' ] );
		add_filter( 'pre_get_posts', [ $this, 'handle_custom_filter' ] );

		// Render Custom Content below
		add_action(
			'anwpfl/tmpl-match/after_wrapper',
			function ( $match ) {

				$content_below = get_post_meta( $match->ID, '_anwpfl_custom_content_below', true );

				if ( trim( $content_below ) ) {
					echo '<div class="anwp-b-wrap mt-4">' . do_shortcode( $content_below ) . '</div>';
				}
			}
		);

		// Modify quick actions & handle action request
		add_filter( 'post_row_actions', [ $this, 'modify_quick_actions' ], 10, 2 );

		add_filter(
			'get_user_option_screen_layout_anwp_match',
			function () {
				return 1;
			}
		);

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
	}

	public function add_rest_routes() {
		register_rest_route(
			'anwpfl/v1',
			'/games/load_more_games/',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'load_more_games' ],
				'permission_callback' => function () {
					return true;
				},
			]
		);
	}

	/**
	 * Filters the array of row action links on the Pages list table.
	 *
	 * @param array $actions
	 * @param WP_Post $post
	 *
	 * @return array
	 * @since 0.10.0
	 */
	public function modify_quick_actions( $actions, $post ) {

		if ( 'anwp_match' === $post->post_type && current_user_can( 'edit_post', $post->ID ) ) {

			// Create edit link
			$edit_link = admin_url( 'post.php?post=' . $post->ID . '&action=edit&setup-match-header=yes' );

			$actions['edit-match-header'] = '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit structure', 'anwp-football-leagues' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Prepare untrashed notices.
	 *
	 * @since 0.5.3
	 */
	public function on_match_untrashed() {

		$notice_text = esc_html__( 'Please re-save untrashed match to update statistics.', 'anwp-football-leagues' );
		set_transient( 'anwp-admin-match-untrashed', $notice_text, 5 );
	}

	/**
	 * Rendering untrashed notices.
	 *
	 * @since 0.5.3
	 */
	public function on_match_untrashed_notices() {
		if ( get_transient( 'anwp-admin-match-untrashed' ) ) :
			?>
			<div class="notice notice-info is-dismissible anwp-visible-after-header">
				<p><?php echo esc_html( get_transient( 'anwp-admin-match-untrashed' ) ); ?></p>
			</div>
			<?php
			delete_transient( 'anwp-admin-match-untrashed' );
		endif;
	}

	/**
	 * Fires before the Filter button on the Posts and Pages list tables.
	 *
	 * The Filter button allows sorting by date and/or category on the
	 * Posts list table, and sorting by date on the Pages list table.
	 *
	 * @param string $post_type The post type slug.
	 */
	public function add_more_filters( $post_type ) {

		if ( 'anwp_match' === $post_type ) {

			ob_start();

			/*
			|--------------------------------------------------------------------
			| Filter By Game State
			|--------------------------------------------------------------------
			*/
			// phpcs:ignore WordPress.Security.NonceVerification
			$current_status = empty( $_GET['_anwpfl_current_status'] ) ? '' : sanitize_text_field( $_GET['_anwpfl_current_status'] );
			?>
			<select name='_anwpfl_current_status' id='anwp_status_filter' class='postform'>
				<option value=''><?php echo esc_html__( 'Status', 'anwp-football-leagues' ); ?></option>
				<option value="result" <?php selected( 'result', $current_status ); ?>>- <?php echo esc_html__( 'Result', 'anwp-football-leagues' ); ?></option>
				<option value="fixture" <?php selected( 'fixture', $current_status ); ?>>- <?php echo esc_html__( 'Fixture', 'anwp-football-leagues' ); ?></option>
			</select>
			<?php
			/*
			|--------------------------------------------------------------------
			| Filter By Competition Id
			|--------------------------------------------------------------------
			*/
			// phpcs:ignore WordPress.Security.NonceVerification
			$current_competition_filter = empty( $_GET['_anwpfl_current_competition'] ) ? '' : (int) $_GET['_anwpfl_current_competition'];
			?>
			<input class="postform anwp-g-float-left anwp-g-admin-list-input anwp-w-120" name="_anwpfl_current_competition" type="text" value="<?php echo esc_attr( $current_competition_filter ); ?>"
				placeholder="<?php echo esc_attr__( 'Competition ID', 'anwp-football-leagues' ); ?>"/>

			<button type="button" class="button anwp-fl-selector anwp-fl-selector--visible anwp-mr-2 postform anwp-g-float-left"
				style="display: none;" data-context="competition" data-single="yes">
				<span class="dashicons dashicons-search"></span>
			</button>
			<?php
			/*
			|--------------------------------------------------------------------
			| Filter By League
			|--------------------------------------------------------------------
			*/
			$leagues = get_terms(
				[
					'taxonomy'   => 'anwp_league',
					'hide_empty' => false,
				]
			);

			if ( ! is_wp_error( $leagues ) && ! empty( $leagues ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification
				$current_league_filter = empty( $_GET['_anwpfl_current_league'] ) ? '' : (int) $_GET['_anwpfl_current_league'];
				?>

				<select name='_anwpfl_current_league' id='anwp_league_filter' class='postform'>
					<option value=''><?php echo esc_html__( 'All Leagues', 'anwp-football-leagues' ); ?></option>
					<?php foreach ( $leagues as $league ) : ?>
						<option value="<?php echo esc_attr( $league->term_id ); ?>" <?php selected( $league->term_id, $current_league_filter ); ?>>
							<?php echo esc_html( $league->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php
			}

			// Seasons dropdown
			$seasons = get_terms(
				[
					'taxonomy'   => 'anwp_season',
					'hide_empty' => false,
				]
			);

			if ( ! is_wp_error( $seasons ) && ! empty( $seasons ) ) {

				$seasons = wp_list_sort( $seasons, 'name', 'DESC' );

				// phpcs:ignore WordPress.Security.NonceVerification
				$current_season_filter = empty( $_GET['_anwpfl_current_season'] ) ? '' : (int) $_GET['_anwpfl_current_season'];
				?>

				<select name='_anwpfl_current_season' id='anwp_season_filter' class='postform'>
					<option value=''><?php echo esc_html__( 'All Seasons', 'anwp-football-leagues' ); ?></option>
					<?php foreach ( $seasons as $season ) : ?>
						<option value="<?php echo esc_attr( $season->term_id ); ?>" <?php selected( $season->term_id, $current_season_filter ); ?>>
							<?php echo esc_html( $season->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php
			}

			// phpcs:ignore WordPress.Security.NonceVerification
			$current_club_filter = empty( $_GET['_anwpfl_current_club'] ) ? '' : (int) $_GET['_anwpfl_current_club'];
			?>
			<input class='postform anwp-g-float-left anwp-g-admin-list-input anwp-w-120' name='_anwpfl_current_club' type='text' value="<?php echo esc_attr( $current_club_filter ); ?>"
				placeholder="<?php echo esc_attr__( 'Club ID', 'anwp-football-leagues' ); ?>"/>

			<button type='button' class='button anwp-fl-selector anwp-fl-selector--visible anwp-mr-2 postform anwp-g-float-left'
				style='display: none;' data-context='club' data-single='yes'>
				<span class='dashicons dashicons-search'></span>
			</button>
			<?php

			/*
			|--------------------------------------------------------------------
			| Date From/To
			|--------------------------------------------------------------------
			*/
			// phpcs:ignore WordPress.Security.NonceVerification
			$date_from = empty( $_GET['_anwpfl_date_from'] ) ? '' : sanitize_text_field( $_GET['_anwpfl_date_from'] );
			// phpcs:ignore WordPress.Security.NonceVerification
			$date_to = empty( $_GET['_anwpfl_date_to'] ) ? '' : sanitize_text_field( $_GET['_anwpfl_date_to'] );
			?>
			<input type="text" class="postform anwp-g-float-left anwp-g-admin-list-input" name="_anwpfl_date_from"
				placeholder="<?php echo esc_attr__( 'Date From', 'anwp-football-leagues' ); ?>" value="<?php echo esc_attr( $date_from ); ?>"/>
			<input type="text" class="postform anwp-g-float-left anwp-g-admin-list-input" name="_anwpfl_date_to"
				placeholder="<?php echo esc_attr__( 'Date To', 'anwp-football-leagues' ); ?>" value="<?php echo esc_attr( $date_to ); ?>"/>
			<?php

			// MatchWeek Options
			$matchweeks = $this->plugin->match->get_matchweek_options();

			// phpcs:ignore WordPress.Security.NonceVerification
			$current_matchweek = empty( $_GET['_anwpfl_current_matchweek'] ) ? '' : (int) $_GET['_anwpfl_current_matchweek'];
			if ( ! empty( $matchweeks ) ) {
				?>
				<select name='_anwpfl_current_matchweek' id='anwp_matchweek_filter' class='postform'>
					<option value=''><?php echo esc_html__( 'All Matchweeks', 'anwp-football-leagues' ); ?></option>
					<?php foreach ( $matchweeks as $matchweek ) : ?>
						<option value="<?php echo esc_attr( $matchweek ); ?>" <?php selected( $matchweek, $current_matchweek ); ?>>
							<?php echo esc_html( $matchweek ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		}
	}

	/**
	 * Handle custom filter.
	 *
	 * @param WP_Query $query
	 */
	public function handle_custom_filter( $query ) {
		global $post_type, $pagenow;

		// Check main query in admin
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'edit.php' !== $pagenow || 'anwp_match' !== $post_type ) {
			return;
		}

		$sub_query = [];

		/*
		|--------------------------------------------------------------------
		| Filter By Club
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_club = empty( $_GET['_anwpfl_current_club'] ) ? '' : intval( $_GET['_anwpfl_current_club'] );

		if ( $filter_by_club ) {
			$sub_query[] =
				[
					'relation' => 'OR',
					[
						'key'   => '_anwpfl_club_home',
						'value' => $filter_by_club,
					],
					[
						'key'   => '_anwpfl_club_away',
						'value' => $filter_by_club,
					],
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By Competition
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_competition = empty( $_GET['_anwpfl_current_competition'] ) ? '' : intval( $_GET['_anwpfl_current_competition'] );

		if ( $filter_by_competition ) {
			$sub_query[] =
				[
					'key'   => '_anwpfl_competition',
					'value' => $filter_by_competition,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By Season
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_season = empty( $_GET['_anwpfl_current_season'] ) ? '' : intval( $_GET['_anwpfl_current_season'] );

		if ( $filter_by_season ) {
			$sub_query[] =
				[
					'key'   => '_anwpfl_season',
					'value' => $filter_by_season,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By Date From/To
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_date_from = empty( $_GET['_anwpfl_date_from'] ) ? '' : sanitize_text_field( $_GET['_anwpfl_date_from'] );

		if ( $filter_by_date_from ) {

			$sub_query[] =
				[
					'key'     => '_anwpfl_match_datetime',
					'value'   => $filter_by_date_from . ' 00:00:00',
					'compare' => '>=',
				];
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_date_to = empty( $_GET['_anwpfl_date_to'] ) ? '' : sanitize_text_field( $_GET['_anwpfl_date_to'] );

		if ( $filter_by_date_to ) {

			$sub_query[] =
				[
					'key'     => '_anwpfl_match_datetime',
					'value'   => $filter_by_date_to . ' 23:59:59',
					'compare' => '<=',
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By Status
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_status = empty( $_GET['_anwpfl_current_status'] ) ? '' : sanitize_text_field( $_GET['_anwpfl_current_status'] );

		if ( $filter_by_status ) {
			$sub_query[] =
				[
					'key'   => '_anwpfl_status',
					'value' => $filter_by_status,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By League
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_league = empty( $_GET['_anwpfl_current_league'] ) ? '' : intval( $_GET['_anwpfl_current_league'] );

		if ( $filter_by_league ) {
			$sub_query[] =
				[
					'key'   => '_anwpfl_league',
					'value' => $filter_by_league,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Filter By Matchweek
		|--------------------------------------------------------------------
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		$filter_by_matchweek = empty( $_GET['_anwpfl_current_matchweek'] ) ? '' : intval( $_GET['_anwpfl_current_matchweek'] );

		if ( $filter_by_matchweek ) {
			$sub_query[] =
				[
					'key'   => '_anwpfl_matchweek',
					'value' => $filter_by_matchweek,
				];
		}

		/*
		|--------------------------------------------------------------------
		| Join All values to main query
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $sub_query ) ) {
			$query->set(
				'meta_query',
				[
					array_merge( [ 'relation' => 'AND' ], $sub_query ),
				]
			);
		}
	}

	/**
	 * Filters whether to remove the 'Months' drop-down from the post list table.
	 *
	 * @param bool   $disable   Whether to disable the drop-down. Default false.
	 * @param string $post_type The post type.
	 *
	 * @return bool
	 */
	public function disable_months_dropdown( $disable, $post_type ) {

		return 'anwp_match' === $post_type ? true : $disable;
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @since 0.2.0 (2018-01-24)
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

		$current_screen = get_current_screen();

		if ( in_array( $hook_suffix, [ 'post-new.php', 'post.php' ], true ) && 'anwp_match' === $current_screen->id ) {

			$post_id = get_the_ID();

			// phpcs:ignore WordPress.Security.NonceVerification
			$setup_match = isset( $_GET['setup-match-header'] ) && 'yes' === $_GET['setup-match-header'];

			if ( ! $setup_match && 'true' === get_post_meta( $post_id, '_anwpfl_fixed', true ) ) {

				$competition_id   = get_post_meta( $post_id, '_anwpfl_competition', true );
				$season_id        = get_post_meta( $post_id, '_anwpfl_season', true );
				$competition_type = get_post_meta( $competition_id, '_anwpfl_type', true );

				/*
				|--------------------------------------------------------------------
				| Prepare Clubs Data
				|--------------------------------------------------------------------
				*/
				$club_home_id = get_post_meta( $post_id, '_anwpfl_club_home', true );
				$club_away_id = get_post_meta( $post_id, '_anwpfl_club_away', true );

				$club_home = (object) [
					'id'    => $club_home_id,
					'logo'  => $this->plugin->club->get_club_logo_by_id( $club_home_id ),
					'title' => $this->plugin->club->get_club_title_by_id( $club_home_id ),
				];
				$club_away = (object) [
					'id'    => $club_away_id,
					'logo'  => $this->plugin->club->get_club_logo_by_id( $club_away_id ),
					'title' => $this->plugin->club->get_club_title_by_id( $club_away_id ),
				];

				/*
				|--------------------------------------------------------------------
				| Prepare players
				|--------------------------------------------------------------------
				*/
				$home_players = $this->plugin->club->get_club_season_players(
					[
						'club' => $club_home_id,
						'id'   => $season_id,
					],
					'short'
				);

				$away_players = $this->plugin->club->get_club_season_players(
					[
						'club' => $club_away_id,
						'id'   => $season_id,
					],
					'short'
				);

				$home_lineup = get_post_meta( $post_id, '_anwpfl_players_home_line_up', true );
				$away_lineup = get_post_meta( $post_id, '_anwpfl_players_away_line_up', true );

				$home_subs = get_post_meta( $post_id, '_anwpfl_players_home_subs', true );
				$away_subs = get_post_meta( $post_id, '_anwpfl_players_away_subs', true );

				/*
				|--------------------------------------------------------------------
				| Players in Squad + Number
				|--------------------------------------------------------------------
				*/
				$home_squad_numbers = [];
				$away_squad_numbers = [];
				$squad_position_map = [];

				foreach ( $home_players as $player ) {
					$home_squad_numbers[ $player->id ] = isset( $player->number ) ? $player->number : '';

					if ( ! empty( $player->position ) ) {
						$squad_position_map[ $player->id ] = $player->position;
					}
				}

				foreach ( $away_players as $player ) {
					$away_squad_numbers[ $player->id ] = isset( $player->number ) ? $player->number : '';

					if ( ! empty( $player->position ) ) {
						$squad_position_map[ $player->id ] = $player->position;
					}
				}

				/*
				|--------------------------------------------------------------------
				| Populate Data
				|--------------------------------------------------------------------
				*/
				$match_l10n = [
					'additional_referees'   => esc_html__( 'Additional referees', 'anwp-football-leagues' ),
					'add_another_referee'   => esc_html__( 'Add Another Referee', 'anwp-football-leagues' ),
					'add_coach'             => esc_html__( 'Add Coach', 'anwp-football-leagues' ),
					'add_temporary_referee' => esc_html__( 'Add Temporary Referee', 'anwp-football-leagues' ),
					'add_temporary_coach'   => esc_html__( 'Add Temporary Coach', 'anwp-football-leagues' ),
					'add_player_as_text'    => esc_html__( 'Add player as text string without creating its profile in the site database.', 'anwp-football-leagues' ),
					'add_referee_as_text'   => esc_html__( 'Add referee as text string without creating its profile in the site database.', 'anwp-football-leagues' ),
					'add_coach_as_text'     => esc_html__( 'Add coach as text string without creating its profile in the site database.', 'anwp-football-leagues' ),
					'assistant_1'           => esc_html__( 'Assistant 1', 'anwp-football-leagues' ),
					'assistant_2'           => esc_html__( 'Assistant 2', 'anwp-football-leagues' ),
					'captain'               => esc_html__( 'Captain', 'anwp-football-leagues' ),
					'coach_name'            => esc_html__( 'Coach Name', 'anwp-football-leagues' ),
					'close'                 => esc_html__( 'Close', 'anwp-football-leagues' ),
					'club_players'          => esc_html__( 'club players', 'anwp-football-leagues' ),
					'club_squad'            => esc_html__( 'Club Squad', 'anwp-football-leagues' ),
					'club_coach'            => esc_html__( 'club coach', 'anwp-football-leagues' ),
					'custom_number'         => esc_html__( 'Custom Number', 'anwp-football-leagues' ),
					'delete'                => esc_html__( 'Delete', 'anwp-football-leagues' ),
					'nationality'           => esc_html__( 'Nationality', 'anwp-football-leagues' ),
					'next'                  => esc_html__( 'Next', 'anwp-football-leagues' ),
					'prev'                  => esc_html__( 'Prev', 'anwp-football-leagues' ),
					'referee'               => esc_html__( 'Referee', 'anwp-football-leagues' ),
					'referee_name'          => esc_html__( 'Referee Name', 'anwp-football-leagues' ),
					'referee_fourth'        => esc_html__( 'Fourth official', 'anwp-football-leagues' ),
					'remove_referee'        => esc_html__( 'Remove Referee', 'anwp-football-leagues' ),
					'role'                  => esc_html__( 'Role', 'anwp-football-leagues' ),
					'saved_referees'        => esc_html__( 'Saved Referees', 'anwp-football-leagues' ),
					'saved_players'         => esc_html__( 'Saved Players', 'anwp-football-leagues' ),
					'saved_coaches'         => esc_html__( 'Saved Coaches', 'anwp-football-leagues' ),
					'search_by_name'        => esc_html__( 'search by name', 'anwp-football-leagues' ),
					'select'                => esc_html__( 'Select', 'anwp-football-leagues' ),
					'select_saved_players'  => esc_html__( 'Select from the list of players saved on the site.', 'anwp-football-leagues' ),
					'select_saved_site'     => esc_html__( 'Select from the list of referees saved on the site.', 'anwp-football-leagues' ),
					'select_saved_coach'    => esc_html__( 'Select from the list of coaches saved on the site.', 'anwp-football-leagues' ),
					'select_referee'        => esc_html__( 'Select Referee', 'anwp-football-leagues' ),
					'select_player'         => esc_html__( 'Select Player', 'anwp-football-leagues' ),
					'squad_number'          => esc_html__( 'Squad Number', 'anwp-football-leagues' ),
					'starting_line_up'      => esc_html__( 'Starting LineUp', 'anwp-football-leagues' ),
					'substitutes'           => esc_html__( 'Substitutes', 'anwp-football-leagues' ),
					'temporary_players'     => esc_html__( 'Temporary Player', 'anwp-football-leagues' ),
					'temporary_referee'     => esc_html__( 'Temporary Referee', 'anwp-football-leagues' ),
					'temporary_coach'       => esc_html__( 'Temporary Coach', 'anwp-football-leagues' ),
					'add_temp_player'       => esc_html__( 'add temporary Player (as text string without saving into database)', 'anwp-football-leagues' ),
				];

				/*
				|--------------------------------------------------------------------
				| Populate Data
				|--------------------------------------------------------------------
				*/
				$data = [
					'stadiumDefault'       => anwp_football_leagues()->stadium->get_stadium_id_by_club( $club_home_id ),
					'l10n'                 => $match_l10n,
					'l10n_datepicker'      => anwp_football_leagues()->data->get_vue_datepicker_locale(),
					'optionsStadium'       => $this->plugin->stadium->get_stadiums(),
					'default_photo'        => anwp_football_leagues()->helper->get_default_player_photo(),
					'optionsPlayers'       => $this->plugin->player->get_players_list( $squad_position_map ),
					'optionsStaff'         => $this->plugin->staff->get_staff_list(),
					'optionsSpecialStatus' => $this->plugin->data->get_game_special_statuses(),
					'optionsReferees'      => $this->plugin->referee->get_referee_list(),
					'optionsClubMap'       => $this->plugin->club->get_clubs_options(),
					'squadHomeNumbers'     => empty( $home_squad_numbers ) ? (object) [] : $home_squad_numbers,
					'squadHomeOrder'       => array_keys( $home_squad_numbers ),
					'squadAwayNumbers'     => empty( $away_squad_numbers ) ? (object) [] : $away_squad_numbers,
					'squadAwayOrder'       => array_keys( $away_squad_numbers ),
					'staffHomeAll'         => array_keys(
						$this->plugin->club->get_club_season_staff(
							[
								'club' => $club_home_id,
								'id'   => $season_id,
							]
						)
					),
					'staffAwayAll'         => array_keys(
						$this->plugin->club->get_club_season_staff(
							[
								'club' => $club_away_id,
								'id'   => $season_id,
							]
						)
					),
					'status'               => get_post_meta( $post_id, '_anwpfl_status', true ),
					'datetime'             => get_post_meta( $post_id, '_anwpfl_match_datetime', true ),
					'stadium'              => get_post_meta( $post_id, '_anwpfl_stadium', true ),
					'competitionType'      => $competition_type,
					'matchWeek'            => get_post_meta( $post_id, '_anwpfl_matchweek', true ),
					'clubHome'             => $club_home,
					'clubAway'             => $club_away,
					'stats'                => get_post_meta( $post_id, '_anwpfl_match_stats', true ),
					'attendance'           => get_post_meta( $post_id, '_anwpfl_attendance', true ),
					'special_status'       => get_post_meta( $post_id, '_anwpfl_special_status', true ),
					'aggtext'              => get_post_meta( $post_id, '_anwpfl_aggtext', true ),
					'extraTime'            => get_post_meta( $post_id, '_anwpfl_extra_time', true ),
					'penalty'              => get_post_meta( $post_id, '_anwpfl_penalty', true ),
					'playersHomeStart'     => $home_lineup,
					'playersHomeSubs'      => $home_subs,
					'playersAwayStart'     => $away_lineup,
					'playersAwaySubs'      => $away_subs,
					'coachHome'            => get_post_meta( $post_id, '_anwpfl_coach_home', true ),
					'coachAway'            => get_post_meta( $post_id, '_anwpfl_coach_away', true ),
					'tempCoach'            => get_post_meta( $post_id, '_anwpfl_temp_coach', true ),
					'captainHomeId'        => get_post_meta( $post_id, '_anwpfl_captain_home', true ),
					'captainAwayId'        => get_post_meta( $post_id, '_anwpfl_captain_away', true ),
					'matchEvents'          => get_post_meta( $post_id, '_anwpfl_match_events', true ),
					'missingPlayers'       => wp_json_encode( $this->get_game_missed_players( $post_id ) ),
					'customNumbers'        => get_post_meta( $post_id, '_anwpfl_match_custom_numbers', true ),
					'tempPlayers'          => get_post_meta( $post_id, '_anwpfl_match_temp_players', true ),
					'matchID'              => $post_id,
					'optionsPositions'     => anwp_football_leagues()->data->get_positions(),
					'optionsCountries'     => anwp_football_leagues()->data->cb_get_countries(),
					'referee'              => get_post_meta( $post_id, '_anwpfl_referee', true ),
					'assistant_1'          => get_post_meta( $post_id, '_anwpfl_assistant_1', true ),
					'assistant_2'          => get_post_meta( $post_id, '_anwpfl_assistant_2', true ),
					'referee_fourth'       => get_post_meta( $post_id, '_anwpfl_referee_fourth', true ),
					'additional_referees'  => get_post_meta( $post_id, '_anwpfl_additional_referees', true ),
					'tempReferees'         => get_post_meta( $post_id, '_anwpfl_temp_referees', true ),
				];

				/**
				 * Filters a match data to localize.
				 *
				 * @since 0.7.4
				 *
				 * @param array $data    Match data
				 * @param int   $post_id Match Post ID
				 */
				$data = apply_filters( 'anwpfl/match/data_to_localize', $data, $post_id );

				wp_localize_script( 'anwpfl_admin_vue', 'anwpMatch', $data );
			} else {

				$competition_id = get_post_meta( $post_id, '_anwpfl_competition', true );
				$round          = '';

				if ( $competition_id && 'knockout' === get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
					$round = get_post_meta( $post_id, '_anwpfl_matchweek', true );
				}

				$data = [
					'competition' => $competition_id,
					'season'      => get_post_meta( $post_id, '_anwpfl_season', true ),
					'group'       => get_post_meta( $post_id, '_anwpfl_competition_group', true ),
					'round'       => $round,
					'clubHome'    => get_post_meta( $post_id, '_anwpfl_club_home', true ),
					'clubAway'    => get_post_meta( $post_id, '_anwpfl_club_away', true ),
					'clubsList'   => $this->plugin->club->get_clubs_list(),
				];

				/**
				 * Filters a match data to localize.
				 *
				 * @since 0.10.0
				 *
				 * @param array $data    Match data
				 * @param int   $post_id Match Post ID
				 */
				$data = apply_filters( 'anwpfl/match/data_setup_to_localize', $data, $post_id );

				wp_localize_script( 'anwpfl_admin_vue', 'anwpMatchSetup', $data );
			}
		}
	}

	/**
	 * Fires before removing a post.
	 *
	 * @param int     $post_ID Post ID.
	 *
	 * @since 0.2.0 (2018-01-22)
	 */
	public function on_match_delete( $post_ID ) {

		// Check post type
		if ( 'anwp_match' === get_post_type( $post_ID ) ) {
			$this->remove_match_statistics( $post_ID );
			$this->remove_match_missing_players( $post_ID );
		}
	}

	/**
	 * Meta box initialization.
	 *
	 * @since  0.2.0 (2018-01-18)
	 */
	public function init_metaboxes() {
		add_action(
			'add_meta_boxes',
			function ( $post_type ) {

				if ( 'anwp_match' === $post_type ) {
					add_meta_box(
						'anwpfl_match_data',
						esc_html__( 'Match Data', 'anwp-football-leagues' ),
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
	 * Render Meta Box content for Match Data.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @since  0.2.0 (2018-01-18)
	 */
	public function render_metabox( $post ) {

		// Error message on competition not exists
		if ( ! $this->plugin->competition->get_competitions() ) {
			echo '<div class="anwp-b-wrap"><div class="my-3 alert alert-warning">' . esc_html__( 'Please, create a Competition first.', 'anwp-football-leagues' ) . '</div></div>';

			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		$setup_match = isset( $_GET['setup-match-header'] ) && 'yes' === $_GET['setup-match-header'];

		// Add nonce for security and authentication.
		wp_nonce_field( 'anwp_save_metabox_' . $post->ID, 'anwp_metabox_nonce' );

		$app_id = apply_filters( 'anwpfl/match/vue_app_id', 'anwpfl-app-match' );

		if ( ! $setup_match && 'true' === get_post_meta( $post->ID, '_anwpfl_fixed', true ) ) :

			$home_id = get_post_meta( $post->ID, '_anwpfl_club_home', true );
			$away_id = get_post_meta( $post->ID, '_anwpfl_club_away', true );

			$home_title = $this->plugin->club->get_club_title_by_id( $home_id );
			$away_title = $this->plugin->club->get_club_title_by_id( $away_id );

			$home_logo = $this->plugin->club->get_club_logo_by_id( $home_id, false );
			$away_logo = $this->plugin->club->get_club_logo_by_id( $away_id, false );

			$competition_id = get_post_meta( $post->ID, '_anwpfl_competition', true );
			$season_id      = get_post_meta( $post->ID, '_anwpfl_season', true );

			$season = get_term( $season_id, 'anwp_season' );
			$league = get_term( get_post_meta( $post->ID, '_anwpfl_league', true ), 'anwp_league' );

			// Check for a Round title
			$is_knockout = 'knockout' === get_post_meta( $competition_id, '_anwpfl_type', true );
			$matchweek   = get_post_meta( $post->ID, '_anwpfl_matchweek', true );

			$round_title = $is_knockout ? $this->plugin->competition->get_round_title( $competition_id, $matchweek ) : '';

			$is_menu_collapsed = 'yes' === get_user_setting( 'anwp-fl-collapsed-menu' );
			?>
			<div class="anwp-b-wrap anwpfl-match-metabox-wrapper">

				<div class="mb-3 border border-success bg-light px-3 py-2">
					<div class="d-flex flex-wrap align-items-center">
						<b class="mr-1"><?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?>:</b> <span><?php echo esc_html( get_the_title( $competition_id ) ); ?></span>

						<?php if ( ! empty( $league->name ) ) : ?>
							<span class="text-muted small mx-2">|</span>
							<b class="mr-1"><?php echo esc_html__( 'League', 'anwp-football-leagues' ); ?>:</b> <span><?php echo esc_html( $league->name ); ?></span>
						<?php endif; ?>

						<?php if ( ! empty( $season->name ) ) : ?>
							<span class="text-muted small mx-2">|</span>
							<b class="mr-1"><?php echo esc_html__( 'Season', 'anwp-football-leagues' ); ?>:</b> <span><?php echo esc_html( $season->name ); ?></span>
						<?php endif; ?>

						<?php if ( $round_title ) : ?>
							<span class="text-muted small mx-2">|</span>
							<b class="mr-1"><?php echo esc_html__( 'Round', 'anwp-football-leagues' ); ?>:</b> <span><?php echo esc_html( $round_title ); ?></span>
						<?php endif; ?>

						<a class="ml-auto" target="_blank" href="<?php echo esc_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit&setup-match-header=yes' ) ); ?>"><?php echo esc_html__( 'Edit structure', 'anwp-football-leagues' ); ?></a>
					</div>

					<div class="d-flex flex-wrap pt-2 mt-2 align-items-center border-top">
						<div class="match__club-wrapper--header d-flex align-items-center py-3">
							<?php if ( $home_logo ) : ?>
								<div class="club-logo__cover" style="background-image: url('<?php echo esc_attr( $home_logo ); ?>')"></div>
							<?php endif; ?>
							<a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $home_id ) . '&action=edit' ) ); ?>" target="_blank"
								data-anwpfl_tippy data-tippy-content="<?php echo esc_attr__( 'Edit Club', 'anwp-football-leagues' ); ?>"
								class="text-decoration-none mx-3 d-inline-block anwp-text-xl anwp-text-gray-800"><?php echo esc_html( $home_title ); ?></a>
						</div>

						<div class="match__scores-number-wrapper mx-3">
							<div class="anwp-text-gray-500 anwp-text-base d-inline-block my-0">-</div>
						</div>

						<div class="match__club-wrapper--header d-flex flex-sm-row-reverse align-items-center py-3">
							<?php if ( $away_logo ) : ?>
								<div class="club-logo__cover club-logo__cover--xlarge d-block" style="background-image: url('<?php echo esc_attr( $away_logo ); ?>')"></div>
							<?php endif; ?>
							<a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $away_id ) . '&action=edit' ) ); ?>" target="_blank"
								data-anwpfl_tippy data-tippy-content="<?php echo esc_attr__( 'Edit Club', 'anwp-football-leagues' ); ?>"
								class="text-decoration-none mx-3 d-inline-block anwp-text-xl anwp-text-gray-800"><?php echo esc_html( $away_title ); ?></a>
						</div>
					</div>

					<?php if ( empty( $league->name ) || empty( $season->name ) || empty( get_post( $competition_id )->post_title ) ) : ?>
						<div class="my-2 p-3 anwp-bg-orange-200 anwp-border anwp-border-orange-800 anwp-text-orange-900 d-flex align-items-center">
							<svg class="anwp-icon anwp-icon--octi anwp-icon--s24 mr-2 anwp-fill-current">
								<use xlink:href="#icon-alert"></use>
							</svg>
							<div>
								<?php echo esc_html__( 'Your Match Structure is invalid.', 'anwp-football-leagues' ); ?><br>
								<?php echo esc_html__( 'Match should have published Competition, Season and League.', 'anwp-football-leagues' ); ?>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<div class="d-flex mt-2" id="anwp-fl-metabox-page-nav">
					<div class="anwp-fl-menu-wrapper mr-3 d-none d-md-block sticky-top align-self-start anwp-flex-none <?php echo esc_attr( $is_menu_collapsed ? 'anwp-fl-collapsed-menu' : '' ); ?>" style="top: 50px;">

						<button id="anwp-fl-publish-click-proxy" class="w-100 button button-primary py-2 mb-4 d-flex align-items-center justify-content-center" type="submit">
							<svg class="anwp-icon anwp-icon--feather anwp-icon--s16"><use xlink:href="#icon-save"></use></svg>
							<span class="ml-2"><?php echo esc_html__( 'Save', 'anwp-football-leagues' ); ?></span>
							<span class="spinner m-0"></span>
						</button>

						<ul class="m-0 p-0 list-unstyled">
							<?php
							$nav_items = [
								[
									'icon'  => 'gear',
									'label' => _x( 'Basic Information', 'submenu', 'anwp-football-leagues' ),
									'slug'  => 'anwp-fl-general-metabox',
								],
								[
									'icon'  => 'graph',
									'label' => _x( 'Scores & Match Stats', 'submenu', 'anwp-football-leagues' ),
									'slug'  => 'anwp-fl-game-statistics-metabox',
								],
								[
									'icon'  => 'jersey',
									'label' => _x( 'Lineups', 'submenu', 'anwp-football-leagues' ),
									'slug'  => 'anwp-fl-players-metabox',
								],
								[
									'icon'  => 'pulse',
									'label' => __( 'Match Events', 'anwp-football-leagues' ),
									'slug'  => 'anwp-fl-game-events-metabox',
								],
								[
									'icon'  => 'x',
									'label' => __( 'Missing Players', 'anwp-football-leagues' ),
									'slug'  => 'anwp-fl-game-sidelines-metabox',
								],
								[
									'icon'  => 'organization',
									'label' => __( 'Referee', 'anwp-football-leagues' ),
									'slug'  => 'anwp-fl-referee-metabox',
								],
								[
									'icon'  => 'note',
									'label' => _x( 'Text Content', 'submenu', 'anwp-football-leagues' ),
									'slug'  => 'anwp-fl-summary-metabox',
								],
								[
									'icon'  => 'device-camera',
									'label' => __( 'Media', 'anwp-football-leagues' ),
									'slug'  => 'anwp-fl-media-match-metabox',
								],
								[
									'icon'  => 'watch',
									'label' => __( 'Match Duration', 'anwp-football-leagues' ),
									'slug'  => 'anwp-fl-timing-match-metabox',
								],
								[
									'icon'  => 'law',
									'label' => __( 'Custom Outcome', 'anwp-football-leagues' ),
									'slug'  => 'anwp-fl-outcome-match-metabox',
								],
							];

							/**
							 * Modify metabox nav items
							 *
							 * @since 0.12.7
							 */
							$nav_items = apply_filters( 'anwpfl/match/metabox_nav_items', $nav_items );

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo anwp_football_leagues()->helper->create_metabox_navigation( $nav_items );

							/**
							 * Fires at the bottom of Metabox Nav.
							 *
							 * @since 0.12.7
							 */
							do_action( 'anwpfl/match/metabox_nav_bottom' );
							?>
						</ul>

					</div>
					<div class="flex-grow-1 anwp-min-width-0 mb-4">

						<div id="<?php echo esc_attr( $app_id ); ?>"></div>

						<?php cmb2_metabox_form( 'anwp_match_metabox' ); ?>

						<?php
						/**
						 * Fires at the bottom of Metabox.
						 *
						 * @since 0.12.7
						 */
						do_action( 'anwpfl/match/metabox_bottom' );
						?>
					</div>
				</div>

				<input type="hidden" name="_anwpfl_fixed" value="true">

				<?php
				/**
				 * Fires at the bottom of Match edit form.
				 *
				 * @since 0.10.0
				 */
				do_action( 'anwpfl/match/edit_form_bottom' );
				?>
			</div>
		<?php else : ?>
			<div class="anwp-b-wrap anwpfl-match-metabox-wrapper">
				<?php
				if ( $setup_match ) :

					$home_id = get_post_meta( $post->ID, '_anwpfl_club_home', true );
					$away_id = get_post_meta( $post->ID, '_anwpfl_club_away', true );

					$home_title = $this->plugin->club->get_club_title_by_id( $home_id );
					$away_title = $this->plugin->club->get_club_title_by_id( $away_id );

					$competition_id = get_post_meta( $post->ID, '_anwpfl_competition', true );
					$season_id      = get_post_meta( $post->ID, '_anwpfl_season', true );

					$season = get_term( $season_id, 'anwp_season' );
					$league = get_term( get_post_meta( $post->ID, '_anwpfl_league', true ), 'anwp_league' );

					// Check for a Round title
					$is_knockout = 'knockout' === get_post_meta( $competition_id, '_anwpfl_type', true );
					$matchweek   = get_post_meta( $post->ID, '_anwpfl_matchweek', true );

					$round_title = $is_knockout ? $this->plugin->competition->get_round_title( $competition_id, $matchweek ) : '';
					?>
					<div class="my-2 p-3 anwp-bg-orange-200 anwp-border anwp-border-orange-800 anwp-text-orange-900 d-flex align-items-center">
						<svg class="anwp-icon anwp-icon--octi anwp-icon--s24 mr-2 anwp-fill-current">
							<use xlink:href="#icon-alert"></use>
						</svg>
						<div>
							<?php echo esc_html__( 'Use the Match Structure editing with caution.', 'anwp-football-leagues' ); ?><br>
							<?php echo esc_html__( 'Save Match data on the next step to recalculate statistic.', 'anwp-football-leagues' ); ?>
						</div>
					</div>
					<div class="my-2 p-3 anwp-bg-blue-200 anwp-border anwp-border-blue-800 anwp-text-blue-900">
						<h4 class="mt-0 mb-2 anwp-text-base"><?php echo esc_html__( 'Old Structure', 'anwp-football-leagues' ); ?></h4>
						<b class="mr-1"><?php echo esc_html__( 'Competition', 'anwp-football-leagues' ); ?>:</b>
						<span><?php echo esc_html( get_the_title( $competition_id ) ); ?></span>

						<?php if ( ! empty( $league->name ) ) : ?>
							<span class="text-muted small mx-2">|</span>
							<b class="mr-1"><?php echo esc_html__( 'League', 'anwp-football-leagues' ); ?>:</b>
							<span><?php echo esc_html( $league->name ); ?></span>
						<?php endif; ?>

						<?php if ( ! empty( $season->name ) ) : ?>
							<span class="text-muted small mx-2">|</span>
							<b class="mr-1"><?php echo esc_html__( 'Season', 'anwp-football-leagues' ); ?>:</b>
							<span><?php echo esc_html( $season->name ); ?></span>
						<?php endif; ?>

						<?php if ( $round_title ) : ?>
							<span class="text-muted small mx-2">|</span>
							<b class="mr-1"><?php echo esc_html__( 'Round', 'anwp-football-leagues' ); ?>:</b>
							<span><?php echo esc_html( $round_title ); ?></span>
						<?php endif; ?>
						<br>
						<?php echo esc_html( $home_title . ' - ' . $away_title ); ?>
					</div>
				<?php endif; ?>
				<script type="text/javascript">
					var _anwp_FL_MatchSetup_Competitions = <?php echo wp_json_encode( anwp_football_leagues()->competition->get_competitions() ); ?>;
				</script>
				<div id="anwpfl-app-match-setup"></div>
			</div>
			<?php
		endif;
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 *
	 * @since  0.2.0 (2017-12-10)
	 * @return int
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
		if ( 'anwp_match' !== $_POST['post_type'] ) {
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

		/** ---------------------------------------
		 * Save Match Data
		 *
		 * @since 0.2.0
		 * ---------------------------------------*/

		$fixed       = isset( $_POST['_anwpfl_fixed'] ) ? sanitize_key( $_POST['_anwpfl_fixed'] ) : '';
		$setup       = isset( $_POST['_anwpfl_match_setup'] ) ? sanitize_key( $_POST['_anwpfl_match_setup'] ) : '';
		$slug_update = false;
		$data        = [];

		if ( 'yes' === $setup ) {

			$data['competition']       = empty( $_POST['_anwpfl_match_competition'] ) ? '' : (int) $_POST['_anwpfl_match_competition'];
			$data['season']            = empty( $_POST['_anwpfl_match_season'] ) ? '' : (int) $_POST['_anwpfl_match_season'];
			$data['league']            = empty( $_POST['_anwpfl_match_league'] ) ? '' : (int) $_POST['_anwpfl_match_league'];
			$data['matchweek']         = empty( $_POST['_anwpfl_match_round'] ) ? '' : (int) $_POST['_anwpfl_match_round'];
			$data['competition_group'] = empty( $_POST['_anwpfl_match_group'] ) ? '' : (int) $_POST['_anwpfl_match_group'];
			$data['club_home']         = empty( $_POST['_anwpfl_match_home'] ) ? '' : (int) $_POST['_anwpfl_match_home'];
			$data['club_away']         = empty( $_POST['_anwpfl_match_away'] ) ? '' : (int) $_POST['_anwpfl_match_away'];

			if ( isset( $_POST['anwp-match-setup-submit'] ) && 'yes' === $_POST['anwp-match-setup-submit'] ) {
				if ( $data['competition'] && $data['season'] && $data['club_home'] && $data['club_away'] ) {
					$data['fixed'] = 'true';
				}
			}

			foreach ( $data as $key => $value ) {
				update_post_meta( $post_id, '_anwpfl_' . $key, $value );
			}
		} elseif ( 'true' === $fixed ) {

			$competition_id = get_post_meta( $post_id, '_anwpfl_competition', true );

			/*
			|--------------------------------------------------------------------
			| Additional Match Data
			|--------------------------------------------------------------------
			*/
			$data['extra_time'] = isset( $_POST['_anwpfl_extra_time'] ) ? sanitize_key( $_POST['_anwpfl_extra_time'] ) : '';
			$data['penalty']    = isset( $_POST['_anwpfl_penalty'] ) ? sanitize_key( $_POST['_anwpfl_penalty'] ) : '';
			$data['status']     = isset( $_POST['_anwpfl_status'] ) ? sanitize_key( $_POST['_anwpfl_status'] ) : '';
			$data['stadium']    = isset( $_POST['_anwpfl_stadium'] ) ? sanitize_key( $_POST['_anwpfl_stadium'] ) : '';
			$data['coach_home'] = isset( $_POST['_anwpfl_coach_home'] ) ? sanitize_key( $_POST['_anwpfl_coach_home'] ) : '';
			$data['coach_away'] = isset( $_POST['_anwpfl_coach_away'] ) ? sanitize_key( $_POST['_anwpfl_coach_away'] ) : '';
			$data['attendance'] = isset( $_POST['_anwpfl_attendance'] ) ? sanitize_key( $_POST['_anwpfl_attendance'] ) : '';

			// Save Special Status / Unset on game finished
			$data['special_status'] = ( ! empty( $_POST['_anwpfl_special_status'] ) && 'result' !== $_POST['_anwpfl_status'] ) ? sanitize_text_field( $_POST['_anwpfl_special_status'] ) : '';

			$data['aggtext'] = isset( $_POST['_anwpfl_aggtext'] ) ? sanitize_text_field( wp_unslash( $_POST['_anwpfl_aggtext'] ) ) : '';

			/*
			|--------------------------------------------------------------------
			| Players
			|--------------------------------------------------------------------
			*/
			$data['players_home_line_up'] = sanitize_text_field( $_POST['_anwpfl_players_home_line_up'] );
			$data['players_away_line_up'] = sanitize_text_field( $_POST['_anwpfl_players_away_line_up'] );
			$data['players_home_subs']    = sanitize_text_field( $_POST['_anwpfl_players_home_subs'] );
			$data['players_away_subs']    = sanitize_text_field( $_POST['_anwpfl_players_away_subs'] );

			$data['captain_home'] = isset( $_POST['_anwpfl_captain_home'] ) ? sanitize_text_field( $_POST['_anwpfl_captain_home'] ) : '';
			$data['captain_away'] = isset( $_POST['_anwpfl_captain_away'] ) ? sanitize_text_field( $_POST['_anwpfl_captain_away'] ) : '';

			/*
			|--------------------------------------------------------------------
			| Save MatchWeek for Round-Robin Competition
			| For Knockout MatchWeek = Round and is saved on match setup step
			|--------------------------------------------------------------------
			*/
			if ( 'round-robin' === get_post_meta( $competition_id, '_anwpfl_type', true ) ) {
				$data['matchweek'] = isset( $_POST['_anwpfl_matchweek'] ) ? sanitize_text_field( $_POST['_anwpfl_matchweek'] ) : '';
			} else {
				$data['matchweek'] = get_post_meta( $post_id, '_anwpfl_matchweek', true ) ? : 1;
			}

			/*
			|--------------------------------------------------------------------
			| Update Meta!
			|--------------------------------------------------------------------
			*/
			foreach ( $data as $key => $value ) {
				update_post_meta( $post_id, '_anwpfl_' . $key, $value );
			}

			/*
			|--------------------------------------------------------------------
			| Temporary players (text fields)
			|--------------------------------------------------------------------
			*/
			$temp_players = isset( $_POST['_anwpfl_match_temp_players'] ) ? json_decode( stripslashes( $_POST['_anwpfl_match_temp_players'] ) ) : [];

			if ( empty( $temp_players ) ) {
				delete_post_meta( $post_id, '_anwpfl_match_temp_players' );
			} else {
				update_post_meta( $post_id, '_anwpfl_match_temp_players', wp_slash( wp_json_encode( $temp_players ) ) );
			}

			/*
			|--------------------------------------------------------------------
			| Temporary coaches (text fields)
			|--------------------------------------------------------------------
			*/
			$temp_coach = isset( $_POST['_anwpfl_temp_coach'] ) ? json_decode( stripslashes( $_POST['_anwpfl_temp_coach'] ) ) : false;

			if ( empty( $temp_coach ) || ( 'temp' !== $data['coach_home'] && 'temp' !== $data['coach_away'] ) ) {
				delete_post_meta( $post_id, '_anwpfl_temp_coach' );
			} else {
				update_post_meta( $post_id, '_anwpfl_temp_coach', anwp_football_leagues()->helper->recursive_sanitize( $temp_coach ) );
			}

			/*
			|--------------------------------------------------------------------
			| Referees
			|--------------------------------------------------------------------
			*/
			foreach ( [ '_anwpfl_referee', '_anwpfl_assistant_1', '_anwpfl_assistant_2', '_anwpfl_referee_fourth', '_anwpfl_additional_referees', '_anwpfl_temp_referees' ] as $referee_slug ) {

				if ( '_anwpfl_referee' === $referee_slug ) {
					if ( empty( $_POST['_anwpfl_referee'] ) ) {
						delete_post_meta( $post_id, '_anwpfl_referee' );
						delete_post_meta( $post_id, '_anwpfl_referee_manually_updated' );
					} else {
						if ( absint( get_post_meta( $post_id, '_anwpfl_referee', true ) ) !== absint( $_POST['_anwpfl_referee'] ) ) {
							update_post_meta( $post_id, '_anwpfl_referee_manually_updated', 'yes' );
						}

						update_post_meta( $post_id, '_anwpfl_referee', sanitize_text_field( $_POST[ $referee_slug ] ) );
					}
				} else {
					if ( empty( $_POST[ $referee_slug ] ) ) {
						delete_post_meta( $post_id, $referee_slug );
					} else {
						if ( in_array( $referee_slug, [ '_anwpfl_temp_referees', '_anwpfl_additional_referees' ], true ) ) {
							$json_referees = json_decode( wp_unslash( $_POST[ $referee_slug ] ), true );
							update_post_meta( $post_id, $referee_slug, $json_referees ? anwp_football_leagues()->helper->recursive_sanitize( $json_referees ) : [] );
						} else {
							update_post_meta( $post_id, $referee_slug, sanitize_text_field( $_POST[ $referee_slug ] ) );
						}
					}
				}
			}

			/*
			|--------------------------------------------------------------------
			| General Data (already saved). Needed to recalculate stats.
			|--------------------------------------------------------------------
			*/
			$data['competition']       = $competition_id;
			$data['league']            = get_post_meta( $post_id, '_anwpfl_league', true );
			$data['season']            = get_post_meta( $post_id, '_anwpfl_season', true );
			$data['competition_group'] = get_post_meta( $post_id, '_anwpfl_competition_group', true );
			$data['club_home']         = get_post_meta( $post_id, '_anwpfl_club_home', true );
			$data['club_away']         = get_post_meta( $post_id, '_anwpfl_club_away', true );

			/*
			|--------------------------------------------------------------------
			| Check League and Season is properly set
			|--------------------------------------------------------------------
			*/
			if ( ! $data['league'] || ! $data['season'] ) {
				$terms = anwp_football_leagues()->competition->tmpl_get_competition_terms( $data['competition'] );

				$data['league'] = $data['league'] ?: $terms['league_id'];
				$data['season'] = $data['season'] ?: $terms['season_id'][0];

				update_post_meta( $post_id, '_anwpfl_league', $data['league'] );
				update_post_meta( $post_id, '_anwpfl_season', $data['season'] );
			}

			/*
			|--------------------------------------------------------------------
			| Complex fields with extra WP sanitization:
			| > Stats, Events & Custom Numbers
			|--------------------------------------------------------------------
			*/
			$stats      = isset( $_POST['_anwpfl_match_stats'] ) ? json_decode( stripslashes( $_POST['_anwpfl_match_stats'] ) ) : (object) [];
			$stats_json = wp_json_encode( $stats );

			$events      = isset( $_POST['_anwpfl_match_events'] ) ? json_decode( stripslashes( $_POST['_anwpfl_match_events'] ) ) : [];
			$events_json = wp_json_encode( $events );

			$missing_players = isset( $_POST['_anwpfl_missing_players'] ) ? json_decode( stripslashes( $_POST['_anwpfl_missing_players'] ) ) : [];

			$custom_numbers      = isset( $_POST['_anwpfl_match_custom_numbers'] ) ? json_decode( stripslashes( $_POST['_anwpfl_match_custom_numbers'] ) ) : (object) [];
			$custom_numbers_json = wp_json_encode( $custom_numbers );

			$data['stats']  = $stats;
			$data['events'] = $events;

			if ( $stats_json ) {
				update_post_meta( $post_id, '_anwpfl_match_stats', wp_slash( $stats_json ) );
			}

			if ( $events_json ) {
				update_post_meta( $post_id, '_anwpfl_match_events', wp_slash( $events_json ) );
			}

			if ( $custom_numbers_json ) {
				update_post_meta( $post_id, '_anwpfl_match_custom_numbers', wp_slash( $custom_numbers_json ) );
			}

			/*
			|--------------------------------------------------------------------
			| Save Final Scores
			|--------------------------------------------------------------------
			*/
			update_post_meta( $post_id, '_anwpfl_match_goals_home', empty( $stats->goalsH ) ? '' : (int) $stats->goalsH );
			update_post_meta( $post_id, '_anwpfl_match_goals_away', empty( $stats->goalsA ) ? '' : (int) $stats->goalsA );

			/*
			|--------------------------------------------------------------------
			| Validate and Save Kickoff time
			|--------------------------------------------------------------------
			*/
			$data['match_datetime'] = isset( $_POST['_anwpfl_datetime'] ) ? sanitize_text_field( $_POST['_anwpfl_datetime'] ) : '';
			$data['match_datetime'] = $this->plugin->helper->validate_date( $data['match_datetime'] ) ? $data['match_datetime'] : '';
			update_post_meta( $post_id, '_anwpfl_match_datetime', $data['match_datetime'] );

			/*
			|--------------------------------------------------------------------
			| Some extra data needed for recalculating
			|--------------------------------------------------------------------
			*/
			$data['match_id'] = (int) $post_id;
			$data['priority'] = isset( $_POST['_anwpfl_match_priority'] ) ? sanitize_text_field( $_POST['_anwpfl_match_priority'] ) : 0;

			/**
			 * Trigger on save match data.
			 *
			 * @param array $data Match data
			 * @param array $_POST
			 *
			 * @since 0.7.4
			 */
			do_action( 'anwpfl/match/on_save', $data, $_POST );

			// Send data to the statistic table or delete them
			if ( 'publish' === $_POST['post_status'] ) {
				$this->save_match_statistics( $data );
				$this->save_missing_players( $missing_players, $post_id );
			} else {
				$this->remove_match_statistics( $post_id );
			}

			// Recalculate standing
			$this->plugin->standing->calculate_standing_prepare( $post_id, $data['competition'], $data['competition_group'] );

			$slug_update = true;
		}

		/**
		 * Update Match title and slug
		 */
		if ( $slug_update && ! empty( $data['club_home'] && ! empty( $data['club_away'] ) ) ) {

			/**
			 * Update Match title and slug.
			 *
			 * @since 0.3.0
			 */
			$post      = get_post( $post_id );
			$home_club = $this->plugin->club->get_club_title_by_id( $data['club_home'] );
			$away_club = $this->plugin->club->get_club_title_by_id( $data['club_away'] );

			if ( ! $home_club || ! $away_club ) {
				return $post_id;
			}

			if ( 'true' === $fixed && trim( AnWPFL_Options::get_value( 'match_title_generator' ) ) ) {
				$match_title = $this->get_match_title_generated( $data, $home_club, $away_club );
			} else {
				$match_title_separator = AnWPFL_Options::get_value( 'match_title_separator', '-' );

				/**
				 * Filters a match title clubs separator.
				 *
				 * @since 0.10.1
				 *
				 * @param string  $match_title_separator Match title separator to be returned.
				 * @param WP_Post $post                  Match WP_Post object
				 * @param array   $data                  Match data
				 */
				$match_title_separator = apply_filters( 'anwpfl/match/title_separator_to_save', $match_title_separator, $post, $data );

				$match_title = sanitize_text_field( $home_club . ' ' . $match_title_separator . ' ' . $away_club );

				/**
				 * Filters a match title before save.
				 *
				 * @since 0.5.3
				 *
				 * @param string  $match_title Match title to be returned.
				 * @param string  $home_club   Home club title.
				 * @param string  $away_club   Away club title.
				 * @param WP_Post $post        Match WP_Post object
				 * @param array   $data        Match data
				 */
				$match_title = apply_filters( 'anwpfl/match/title_to_save', $match_title, $home_club, $away_club, $post, $data );
			}

			$match_slug = $this->get_match_slug_generated( $data, $home_club, $away_club, $post );

			// Rename Match (title and slug)
			if ( $post->post_name !== $match_slug || $post->post_title !== $match_title ) {

				remove_action( 'save_post', [ $this, 'save_metabox' ] );

				// update the post, which calls save_post again
				wp_update_post(
					[
						'ID'         => $post_id,
						'post_title' => $match_title,
						'post_name'  => $match_slug,
					]
				);

				// re-hook this function
				add_action( 'save_post', [ $this, 'save_metabox' ] );
			}
		}

		return $post_id;
	}

	/**
	 * Method removes match statistics from DB.
	 *
	 * @param int $match_id -
	 *
	 * @since 0.3.0 (2018-01-30)
	 */
	private function remove_match_statistics( $match_id ) {

		global $wpdb;

		$wpdb->delete( $wpdb->prefix . 'anwpfl_matches', [ 'match_id' => (int) $match_id ] );
		$wpdb->delete( $wpdb->prefix . 'anwpfl_players', [ 'match_id' => (int) $match_id ] );

		// Recalculate standing
		$this->plugin->standing->calculate_standing_prepare( $match_id );
	}

	/**
	 * Method saves match statistics into DB.
	 *
	 * @param array $data - Array of data to save
	 *
	 * @since 0.3.0 (2018-01-30)
	 * @return int|false The number of rows affected, or false on error.
	 */
	public function save_match_statistics( $data ) {

		global $wpdb;

		$stats = (array) $data['stats'];
		$table = $wpdb->prefix . 'anwpfl_matches';

		// Prepare main_stage_id field
		$data['main_stage_id'] = 0;

		$multistage = get_post_meta( $data['competition'], '_anwpfl_multistage', true );

		if ( 'main' === $multistage ) {
			$data['main_stage_id'] = $data['competition'];
		} elseif ( 'secondary' === $multistage ) {
			$data['main_stage_id'] = (int) get_post_meta( $data['competition'], '_anwpfl_multistage_main', true );
		}

		// Prepare _anwpfl_competition_status field
		$data['competition_status'] = sanitize_key( get_post_meta( $data['competition'], '_anwpfl_competition_status', true ) );

		$result = $wpdb->replace(
			$table,
			[
				'match_id'           => $data['match_id'],
				'competition_id'     => $data['competition'],
				'main_stage_id'      => $data['main_stage_id'],
				'competition_status' => $data['competition_status'],
				'group_id'           => $data['competition_group'],
				'league_id'          => $data['league'],
				'season_id'          => $data['season'],
				'home_club'          => $data['club_home'],
				'away_club'          => $data['club_away'],
				'kickoff'            => empty( $data['match_datetime'] ) ? '0000-00-00 00:00:00' : $data['match_datetime'],
				'finished'           => 'result' === $data['status'] ? 1 : 0,
				'extra'              => 'yes' === $data['penalty'] ? 2 : ( 'yes' === $data['extra_time'] ? 1 : 0 ),
				'attendance'         => empty( $data['attendance'] ) ? 0 : $data['attendance'],
				'special_status'     => empty( $data['special_status'] ) ? '' : $data['special_status'],
				'aggtext'            => empty( $data['aggtext'] ) ? '' : $data['aggtext'],
				'stadium_id'         => empty( $data['stadium'] ) ? 0 : intval( $data['stadium'] ),
				'match_week'         => empty( $data['matchweek'] ) ? 0 : intval( $data['matchweek'] ),
				'priority'           => empty( $data['priority'] ) ? 0 : (int) $data['priority'],
				'home_goals'         => empty( $stats['goalsH'] ) ? 0 : $stats['goalsH'],
				'away_goals'         => empty( $stats['goalsA'] ) ? 0 : $stats['goalsA'],
				'home_goals_half'    => empty( $stats['goals1H'] ) ? 0 : $stats['goals1H'],
				'away_goals_half'    => empty( $stats['goals1A'] ) ? 0 : $stats['goals1A'],
				'home_goals_ft'      => empty( $stats['goalsFTH'] ) ? 0 : $stats['goalsFTH'],
				'away_goals_ft'      => empty( $stats['goalsFTA'] ) ? 0 : $stats['goalsFTA'],
				'home_goals_e'       => empty( $stats['extraTimeH'] ) ? 0 : $stats['extraTimeH'],
				'away_goals_e'       => empty( $stats['extraTimeA'] ) ? 0 : $stats['extraTimeA'],
				'home_goals_p'       => empty( $stats['penaltyH'] ) ? 0 : $stats['penaltyH'],
				'away_goals_p'       => empty( $stats['penaltyA'] ) ? 0 : $stats['penaltyA'],
				'home_cards_y'       => empty( $stats['yellowCardsH'] ) ? 0 : $stats['yellowCardsH'],
				'away_cards_y'       => empty( $stats['yellowCardsA'] ) ? 0 : $stats['yellowCardsA'],
				'home_cards_yr'      => empty( $stats['yellow2RCardsH'] ) ? 0 : $stats['yellow2RCardsH'],
				'away_cards_yr'      => empty( $stats['yellow2RCardsA'] ) ? 0 : $stats['yellow2RCardsA'],
				'home_cards_r'       => empty( $stats['redCardsH'] ) ? 0 : $stats['redCardsH'],
				'away_cards_r'       => empty( $stats['redCardsA'] ) ? 0 : $stats['redCardsA'],
				'home_corners'       => empty( $stats['cornersH'] ) ? 0 : $stats['cornersH'],
				'away_corners'       => empty( $stats['cornersA'] ) ? 0 : $stats['cornersA'],
				'home_fouls'         => empty( $stats['foulsH'] ) ? 0 : $stats['foulsH'],
				'away_fouls'         => empty( $stats['foulsA'] ) ? 0 : $stats['foulsA'],
				'home_offsides'      => empty( $stats['offsidesH'] ) ? 0 : $stats['offsidesH'],
				'away_offsides'      => empty( $stats['offsidesA'] ) ? 0 : $stats['offsidesA'],
				'home_possession'    => empty( $stats['possessionH'] ) ? 0 : $stats['possessionH'],
				'away_possession'    => empty( $stats['possessionA'] ) ? 0 : $stats['possessionA'],
				'home_shots'         => empty( $stats['shotsH'] ) ? 0 : $stats['shotsH'],
				'away_shots'         => empty( $stats['shotsA'] ) ? 0 : $stats['shotsA'],
				'home_shots_on_goal' => empty( $stats['shotsOnGoalsH'] ) ? 0 : $stats['shotsOnGoalsH'],
				'away_shots_on_goal' => empty( $stats['shotsOnGoalsA'] ) ? 0 : $stats['shotsOnGoalsA'],
			]
		);

		if ( false !== $result ) {
			$this->save_player_statistics( $data );
		}

		return $result;
	}

	/**
	 * Method saves player statistics into DB.
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
	 *
	 * @param array $match_data - Array of match data
	 *
	 * @since 0.5.0 (2018-03-10)
	 * @return bool
	 */
	public function save_player_statistics( $match_data ) {

		global $wpdb;

		// Get match duration
		// @since 0.7.5
		$minutes_full  = intval( get_post_meta( $match_data['match_id'], '_anwpfl_duration_full', true ) ?: 90 );
		$minutes_extra = $minutes_full + intval( get_post_meta( $match_data['match_id'], '_anwpfl_duration_extra', true ) ?: 30 );

		$data_values = [ 'players_home_line_up', 'players_away_line_up', 'players_home_subs', 'players_away_subs' ];
		$players     = [];

		foreach ( $data_values as $value ) {
			if ( ! empty( $match_data[ $value ] ) ) {
				foreach ( explode( ',', $match_data[ $value ] ) as $player_id ) {
					if ( intval( $player_id ) ) {

						$club_id = in_array( $value, [ 'players_home_line_up', 'players_home_subs' ], true ) ? $match_data['club_home'] : $match_data['club_away'];

						$time_out = 0;

						if ( in_array( $value, [ 'players_home_line_up', 'players_away_line_up' ], true ) ) {
							$time_out = ( 'yes' === $match_data['extra_time'] ? $minutes_extra : $minutes_full );
						}

						$appearance = in_array( $value, [ 'players_home_line_up', 'players_away_line_up' ], true ) ? 1 : 0;

						$players[ (int) $player_id ] = [
							'match_id'           => (int) $match_data['match_id'],
							'player_id'          => (int) $player_id,
							'club_id'            => (int) $club_id,
							'season_id'          => (int) $match_data['season'],
							'competition_id'     => (int) $match_data['competition'],
							'main_stage_id'      => (int) $match_data['main_stage_id'],
							'competition_status' => sanitize_key( $match_data['competition_status'] ),
							'league_id'          => (int) $match_data['league'],
							'time_in'            => 0,
							'time_out'           => $time_out,
							'appearance'         => $appearance,
							'goals'              => 0,
							'goals_own'          => 0,
							'goals_penalty'      => 0,
							'goals_conceded'     => 0,
							'assist'             => 0,
							'card_y'             => 0,
							'card_yr'            => 0,
							'card_r'             => 0,
						];
					}
				}
			}
		}

		/**
		 * Add player stats when line ups not added
		 *
		 * @since 0.7.3 (2018-09-23)
		 */
		if ( ! empty( $match_data['events'] ) && is_array( $match_data['events'] ) ) {
			foreach ( $match_data['events'] as $e ) {
				if ( ! empty( $e->type ) && in_array( $e->type, [ 'goal', 'card' ], true ) ) {

					if ( intval( $e->player ) && empty( $players[ $e->player ] ) ) {
						$players[ (int) $e->player ] = [
							'match_id'           => (int) $match_data['match_id'],
							'player_id'          => (int) $e->player,
							'club_id'            => (int) $e->club,
							'season_id'          => (int) $match_data['season'],
							'competition_id'     => (int) $match_data['competition'],
							'main_stage_id'      => (int) $match_data['main_stage_id'],
							'competition_status' => sanitize_key( $match_data['competition_status'] ),
							'league_id'          => (int) $match_data['league'],
							'time_in'            => 0,
							'time_out'           => 0,
							'appearance'         => 0,
							'goals'              => 0,
							'goals_own'          => 0,
							'goals_penalty'      => 0,
							'goals_conceded'     => 0,
							'assist'             => 0,
							'card_y'             => 0,
							'card_yr'            => 0,
							'card_r'             => 0,
						];
					}
				}
			}
		}

		if ( empty( $players ) || ! is_array( $players ) ) {
			return false;
		}

		$mins_goals_home = [];
		$mins_goals_away = [];

		// Parse all events
		if ( ! empty( $match_data['events'] ) && is_array( $match_data['events'] ) ) {

			foreach ( $match_data['events'] as $e ) {
				if ( ! empty( $e->type ) && in_array( $e->type, [ 'goal', 'card', 'substitute' ], true ) ) {

					switch ( $e->type ) {
						case 'goal':
							if ( ! empty( $e->player ) && ! empty( $players[ $e->player ] ) ) {

								if ( 'yes' === $e->ownGoal ) {
									$players[ $e->player ]['goals_own'] ++;
								} else {
									$players[ $e->player ]['goals'] ++;

									if ( 'yes' === $e->fromPenalty ) {
										$players[ $e->player ]['goals_penalty'] ++;
									}
								}
							}

							if ( ! empty( $e->assistant ) && ! empty( $players[ $e->assistant ] ) ) {
								$players[ $e->assistant ]['assist'] ++;
							}

							if ( $e->minute > 0 ) {
								if ( (int) $e->club === (int) $match_data['club_home'] ) {
									$mins_goals_home[] = $e->minute;
								} elseif ( (int) $e->club === (int) $match_data['club_away'] ) {
									$mins_goals_away[] = $e->minute;
								}
							}

							break;

						case 'card':
							if ( ! empty( $e->player ) && ! empty( $players[ $e->player ] ) && in_array( $e->card, [ 'y', 'yr', 'r' ], true ) ) {
								$players[ $e->player ][ 'card_' . sanitize_key( $e->card ) ] ++;
							}

							// Reduce out time on red card
							// --
							// Fixed situation on getting red card on the bench
							// @since 0.6.5 (2018-08-17)
							if ( in_array( $e->card, [ 'yr', 'r' ], true ) && ! empty( $players[ $e->player ] ) && ! in_array( $players[ $e->player ]['appearance'], [ 4, 2 ], true ) ) {
								$players[ $e->player ]['time_out'] = $e->minute;
							}

							break;

						case 'substitute':
							// phpcs:disable WordPress.NamingConventions
							// IN
							if ( ! empty( $e->player ) && ! empty( $players[ $e->player ] ) && intval( $e->minute ) ) {
								$players[ $e->player ]['time_in']    = (int) $e->minute;
								$players[ $e->player ]['appearance'] = 3;
								$players[ $e->player ]['time_out']   = 'yes' === $match_data['extra_time'] ? $minutes_extra : $minutes_full;

								if ( $minutes_full === (int) $players[ $e->player ]['time_in'] && 'yes' !== $match_data['extra_time'] ) {
									$players[ $e->player ]['time_out'] = $minutes_full + 1;
								}
							}

							// OUT
							// info - appearance (0 - none; 1 - full match, 2 -subs out, 3 - subs in, 4 - subs in-out)
							if ( ! empty( $e->playerOut ) && ! empty( $players[ $e->playerOut ] ) && intval( $e->minute ) ) {
								$players[ $e->playerOut ]['time_out']   = (int) $e->minute;
								$players[ $e->playerOut ]['appearance'] = $players[ $e->playerOut ]['time_in'] > 0 ? 4 : 2;
							}
							// phpcs:enable

							break;
					}
				}
			}
		}

		// goals_conceded for goalkeepers
		$home_goalkeepers = anwp_football_leagues()->player->filter_goalkeepers_from_squad( $match_data['players_home_line_up'], $match_data['players_home_subs'] );
		$away_goalkeepers = anwp_football_leagues()->player->filter_goalkeepers_from_squad( $match_data['players_away_line_up'], $match_data['players_away_subs'] );

		foreach ( $home_goalkeepers as $h_p ) {
			foreach ( $mins_goals_away as $minute ) {
				if ( ! empty( $players[ $h_p ] ) && $players[ $h_p ]['time_in'] <= $minute && $players[ $h_p ]['time_out'] >= $minute ) {
					$players[ $h_p ]['goals_conceded'] ++;
				}
			}
		}

		foreach ( $away_goalkeepers as $a_p ) {
			foreach ( $mins_goals_home as $minute ) {
				if ( ! empty( $players[ $a_p ] ) && $players[ $a_p ]['time_in'] <= $minute && $players[ $a_p ]['time_out'] >= $minute ) {
					$players[ $a_p ]['goals_conceded'] ++;
				}
			}
		}

		// Save data to DB
		$table = $wpdb->prefix . 'anwpfl_players';

		// Remove old data
		if ( absint( $match_data['match_id'] ) ) {
			$wpdb->delete( $table, [ 'match_id' => (int) $match_data['match_id'] ] );
		}

		foreach ( $players as $player ) {
			if ( (int) $player['player_id'] ) {
				$wpdb->replace( $table, $player );
			}
		}

		return true;
	}

	/**
	 * Create CMB2 metaboxes
	 *
	 * @since 0.2.0 (2018-01-17)
	 */
	public function init_cmb2_metaboxes() {

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_anwpfl_';

		$cmb = new_cmb2_box(
			[
				'id'              => 'anwp_match_metabox',
				'object_types'    => [ 'anwp_match' ],
				'context'         => 'advanced',
				'show_on'         => [ 'key' => 'fixed' ],
				'priority'        => 'high',
				'save_button'     => '',
				'show_names'      => true,
				'remove_box_wrap' => true,
				'classes'         => 'anwp-b-wrap',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Match Summary
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'            => esc_html__( 'Text 1', 'anwp-football-leagues' ) . '<br>' . esc_html__( 'Match Preview or Summary', 'anwp-football-leagues' ),
				'id'              => $prefix . 'summary',
				'type'            => 'wysiwyg',
				'sanitization_cb' => false,
				'options'         => [
					'wpautop'       => true,
					'media_buttons' => true, // show insert/upload button(s)
					'textarea_name' => 'anwp_match_summary_input',
					'textarea_rows' => 5,
					'teeny'         => false, // output the minimal editor config used in Press This
					'dfw'           => false, // replace the default fullscreen with DFW (needs specific css)
					'tinymce'       => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
					'quicktags'     => true, // load Quicktags, can be used to pass settings directly to Quicktags using an array()
				],
				'before_row'      => anwp_football_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'note',
						'label' => __( 'Text Content', 'anwp-football-leagues' ),
						'slug'  => 'anwp-fl-summary-metabox',
					]
				),
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Text 2', 'anwp-football-leagues' ) . '<br>' . esc_html__( 'Custom Content', 'anwp-football-leagues' ),
				'id'        => $prefix . 'custom_content_below',
				'type'      => 'wysiwyg',
				'options'   => [
					'wpautop'       => true,
					'media_buttons' => true, // show insert/upload button(s)
					'textarea_name' => 'anwp_custom_content_below',
					'textarea_rows' => 5,
					'teeny'         => false, // output the minimal editor config used in Press This
					'dfw'           => false, // replace the default fullscreen with DFW (needs specific css)
					'tinymce'       => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
					'quicktags'     => true, // load Quicktags, can be used to pass settings directly to Quicktags using an array()
				],
				'after_row' => '</div></div>',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Media
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Video Source', 'anwp-football-leagues' ),
				'id'         => $prefix . 'video_source',
				'type'       => 'select',
				'before_row' => anwp_football_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'device-camera',
						'label' => __( 'Media', 'anwp-football-leagues' ),
						'slug'  => 'anwp-fl-media-match-metabox',
					]
				),
				'default'    => '',
				'options'    => [
					''        => esc_html__( '- select source -', 'anwp-football-leagues' ),
					'site'    => esc_html__( 'Media Library', 'anwp-football-leagues' ),
					'youtube' => esc_html__( 'Youtube', 'anwp-football-leagues' ),
					'vimeo'   => esc_html__( 'Vimeo', 'anwp-football-leagues' ),
				],
			]
		);

		$cmb->add_field(
			[
				'name'    => esc_html__( 'YouTube Image Format', 'anwp-football-leagues' ),
				'id'      => $prefix . 'yt_image_format',
				'type'    => 'select',
				'default' => '',
				'options' => [
					''     => esc_html__( '- default -', 'anwp-football-leagues' ),
					'webp' => 'webp',
					'jpg'  => 'jpg',
				],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Video ID (or URL)', 'anwp-football-leagues' ),
				'id'         => $prefix . 'video_id',
				'type'       => 'text_small',
				'label_cb'   => [ $this->plugin, 'cmb2_field_label' ],
				'label_help' => __( 'for Youtube or Vimeo', 'anwp-football-leagues' ),
			]
		);

		$cmb->add_field(
			[
				'name'         => esc_html__( 'Video File', 'anwp-football-leagues' ),
				'id'           => $prefix . 'video_media_url',
				'type'         => 'file',
				'label_cb'     => [ $this->plugin, 'cmb2_field_label' ],
				'label_help'   => __( 'for Media Library', 'anwp-football-leagues' ),
				'options'      => [
					'url' => false,
				],
				'text'         => [
					'add_upload_file_text' => esc_html__( 'Open Media Library', 'anwp-football-leagues' ),
				],
				'query_args'   => [
					'type' => 'video/mp4',
				],
				'preview_size' => 'large',
			]
		);

		$cmb->add_field(
			[
				'name' => esc_html__( 'Video Description', 'anwp-football-leagues' ),
				'id'   => $prefix . 'video_info',
				'type' => 'text',
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
				'name' => esc_html__( 'Text below gallery', 'anwp-football-leagues' ),
				'id'   => $prefix . 'gallery_notes',
				'type' => 'textarea_small',
			]
		);

		/*
		|--------------------------------------------------------------------
		| Additional Video
		|--------------------------------------------------------------------
		*/
		$group_field_id = $cmb->add_field(
			[
				'id'               => $prefix . 'additional_videos',
				'type'             => 'group',
				'after_group'      => '</div></div>',
				'classes'          => 'mt-0 pt-0',
				'before_group_row' => '<h4>' . esc_html__( 'Additional videos', 'anwp-football-leagues' ) . '</h4>',
				'options'          => [
					'group_title'    => __( 'Additional Video', 'anwp-football-leagues' ),
					'add_button'     => __( 'Add Another Video', 'anwp-football-leagues' ),
					'remove_button'  => __( 'Remove Video', 'anwp-football-leagues' ),
					'sortable'       => true,
					'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'anwp-football-leagues' ),
				],
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name'    => esc_html__( 'Video Source', 'anwp-football-leagues' ),
				'id'      => 'video_source',
				'type'    => 'select',
				'default' => '',
				'options' => [
					''        => esc_html__( '- select source -', 'anwp-football-leagues' ),
					'site'    => esc_html__( 'Media Library', 'anwp-football-leagues' ),
					'youtube' => esc_html__( 'Youtube', 'anwp-football-leagues' ),
					'vimeo'   => esc_html__( 'Vimeo', 'anwp-football-leagues' ),
				],
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name'       => esc_html__( 'Video ID (or URL)', 'anwp-football-leagues' ),
				'id'         => 'video_id',
				'type'       => 'text_small',
				'label_cb'   => [ $this->plugin, 'cmb2_field_label' ],
				'label_help' => __( 'for Youtube or Vimeo', 'anwp-football-leagues' ),
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name'         => esc_html__( 'Video File', 'anwp-football-leagues' ),
				'id'           => 'video_media_url',
				'type'         => 'file',
				'label_cb'     => [ $this->plugin, 'cmb2_field_label' ],
				'label_help'   => __( 'for Media Library', 'anwp-football-leagues' ),
				'options'      => [
					'url' => false,
				],
				'text'         => [
					'add_upload_file_text' => esc_html__( 'Open Media Library', 'anwp-football-leagues' ),
				],
				'query_args'   => [
					'type' => 'video/mp4',
				],
				'preview_size' => 'large',
			]
		);

		$cmb->add_group_field(
			$group_field_id,
			[
				'name' => esc_html__( 'Video Description', 'anwp-football-leagues' ),
				'id'   => 'video_info',
				'type' => 'text',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Match Timing
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Full Time', 'anwp-football-leagues' ),
				'id'         => $prefix . 'duration_full',
				'type'       => 'text_small',
				'default'    => '90',
				'before_row' => anwp_football_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'watch',
						'label' => __( 'Match Duration', 'anwp-football-leagues' ),
						'slug'  => 'anwp-fl-timing-match-metabox',
					]
				),
			]
		);

		$cmb->add_field(
			[
				'name'      => esc_html__( 'Extra Time', 'anwp-football-leagues' ),
				'id'        => $prefix . 'duration_extra',
				'type'      => 'text_small',
				'default'   => '30',
				'after_row' => '</div></div>',
			]
		);

		/*
		|--------------------------------------------------------------------------
		| Custom Outcome
		|--------------------------------------------------------------------------
		*/
		$cmb->add_field(
			[
				'name'       => esc_html__( 'Custom Outcome', 'anwp-football-leagues' ),
				'id'         => $prefix . 'custom_outcome',
				'type'       => 'select',
				'options'    => [
					''    => __( 'No', 'anwp-football-leagues' ),
					'yes' => __( 'Yes', 'anwp-football-leagues' ),
				],
				'attributes' => [
					'class'     => 'cmb2_select anwp-fl-parent-of-dependent',
					'data-name' => $prefix . 'custom_outcome',
				],
				'default'    => '',
				'before_row' => anwp_football_leagues()->helper->create_metabox_header(
					[
						'icon'  => 'law',
						'label' => __( 'Custom Outcome', 'anwp-football-leagues' ),
						'slug'  => 'anwp-fl-outcome-match-metabox',
					]
				),
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Outcome for home team', 'anwp-football-leagues' ),
				'id'         => $prefix . 'outcome_home',
				'type'       => 'select',
				'options'    => [
					''      => __( '- not selected -', 'anwp-football-leagues' ),
					'won'   => __( 'Won', 'anwp-football-leagues' ),
					'drawn' => __( 'Drawn', 'anwp-football-leagues' ),
					'lost'  => __( 'Lost', 'anwp-football-leagues' ),
				],
				'attributes' => [
					'class'       => 'regular-text anwp-fl-dependent-field',
					'data-parent' => $prefix . 'custom_outcome',
					'data-action' => 'show',
					'data-value'  => 'yes',
				],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Outcome for away team', 'anwp-football-leagues' ),
				'id'         => $prefix . 'outcome_away',
				'type'       => 'select',
				'options'    => [
					''      => __( '- not selected -', 'anwp-football-leagues' ),
					'won'   => __( 'Won', 'anwp-football-leagues' ),
					'drawn' => __( 'Drawn', 'anwp-football-leagues' ),
					'lost'  => __( 'Lost', 'anwp-football-leagues' ),
				],
				'attributes' => [
					'class'       => 'regular-text anwp-fl-dependent-field',
					'data-parent' => $prefix . 'custom_outcome',
					'data-action' => 'show',
					'data-value'  => 'yes',
				],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Points for home team', 'anwp-football-leagues' ),
				'id'         => $prefix . 'outcome_points_home',
				'type'       => 'text',
				'attributes' => [
					'class'       => 'regular-text anwp-input-number-small anwp-fl-dependent-field',
					'data-parent' => $prefix . 'custom_outcome',
					'data-action' => 'show',
					'data-value'  => 'yes',
					'type'        => 'number',
				],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Points for away team', 'anwp-football-leagues' ),
				'id'         => $prefix . 'outcome_points_away',
				'type'       => 'text',
				'attributes' => [
					'type'        => 'number',
					'class'       => 'regular-text anwp-input-number-small anwp-fl-dependent-field',
					'data-parent' => $prefix . 'custom_outcome',
					'data-action' => 'show',
					'data-value'  => 'yes',
				],
			]
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Outcome Text', 'anwp-football-leagues' ),
				'id'         => $prefix . 'outcome_text',
				'type'       => 'text',
				'attributes' => [
					'class'       => 'regular-text anwp-fl-dependent-field',
					'data-parent' => $prefix . 'custom_outcome',
					'data-action' => 'show',
					'data-value'  => 'yes',
				],
				'after_row'  => '</div></div>',
			]
		);

		/**
		 * Adds extra fields to the metabox.
		 *
		 * @since 0.9.0
		 */
		$extra_fields = apply_filters( 'anwpfl/cmb2_tabs_content/match', [] );

		if ( ! empty( $extra_fields ) && is_array( $extra_fields ) ) {
			foreach ( $extra_fields as $field ) {
				$cmb->add_field( $field );
			}
		}
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @param array $columns Array of registered column names/labels.
	 *
	 * @return array          Modified array.
	 */
	public function sortable_columns( $sortable_columns ) {

		return array_merge( $sortable_columns, [ 'match_id' => 'ID' ] );
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
			'anwpfl_match_competition' => esc_html__( 'Competition', 'anwp-football-leagues' ),
			'anwpfl_match_scores'      => esc_html__( 'Match', 'anwp-football-leagues' ),
			'anwpfl_match_datetime'    => esc_html__( 'Kick Off Time', 'anwp-football-leagues' ),
			'match_id'                 => esc_html__( 'ID', 'anwp-football-leagues' ),
		];

		// Merge old and new columns
		$columns = array_merge( $new_columns, $columns );

		// Change columns order
		$new_columns_order = [
			'cb',
			'title',
			'anwpfl_match_competition',
			'anwpfl_match_datetime',
			'anwpfl_match_scores',
			'comments',
			'match_id',
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
	public function match_columns_display( $column, $post_id ) {
		switch ( $column ) {

			case 'anwpfl_match_competition':
				// Get competition id
				$competition_id = (int) get_post_meta( $post_id, '_anwpfl_competition', true );

				// Get competition title
				$competition = anwp_football_leagues()->competition->get_competition( $competition_id );

				if ( ! $competition ) {
					return;
				}

				echo '<span class="anwp-admin-competition-icon"></span> <strong>' . esc_html( $competition->title ) . '</strong><br>';

				// Stage title
				if ( '' !== $competition->multistage && $competition->stage_title ) {
					echo '<strong>' . esc_html__( 'Stage', 'anwp-football-leagues' ) . ':</strong> ' . esc_html( $competition->stage_title ) . '<br>';
				}

				// Season
				if ( ! empty( $competition->season_text ) ) {
					echo '<strong>' . esc_html__( 'Season', 'anwp-football-leagues' ) . ':</strong> ' . esc_html( $competition->season_text ) . '<br>';
				}

				if ( 'knockout' === $competition->type ) {
					$round_id = get_post_meta( $post_id, '_anwpfl_matchweek', true ) ?: 1;

					if ( $round_id ) {
						$round_title = '';

						if ( ! empty( $competition->rounds ) && is_array( $competition->rounds ) ) {
							foreach ( $competition->rounds as $round ) {
								if ( intval( $round_id ) === intval( $round->id ) && ! empty( $round->title ) ) {
									$round_title = trim( $round->title );
									break;
								}
							}
						}

						echo '<strong>' . esc_html__( 'Round', 'anwp-football-leagues' ) . ' #' . intval( $round_id ) . ':</strong> ' . esc_html( $round_title );
					}
				} else {
					echo '<strong>' . esc_html__( 'MatchWeek', 'anwp-football-leagues' ) . ':</strong> ' . esc_html( get_post_meta( $post_id, '_anwpfl_matchweek', true ) ) . '<br>';
				}
				break;

			case 'anwpfl_match_scores':
				$home_score = '';
				$away_score = '';

				$is_result = 'result' === get_post_meta( $post_id, '_anwpfl_status', true );

				if ( $is_result ) {
					$home_score = get_post_meta( $post_id, '_anwpfl_match_goals_home', true );
					$away_score = get_post_meta( $post_id, '_anwpfl_match_goals_away', true );
				}

				// HOME
				echo '<span class="anwp-text-nowrap"><span class="anwp-admin-table-scores">' . ( $is_result ? (int) $home_score : '-' ) . '</span>';

				$club_id       = (int) get_post_meta( $post_id, '_anwpfl_club_home', true );
				$clubs_options = $this->plugin->club->get_clubs_options();

				if ( ! empty( $clubs_options[ $club_id ] ) ) {
					echo esc_html( $clubs_options[ $club_id ] ) . '</span><br>';
				}

				// AWAY
				echo '<span class="anwp-text-nowrap"><span class="anwp-admin-table-scores">' . ( $is_result ? (int) $away_score : '-' ) . '</span>';

				$club_id       = (int) get_post_meta( $post_id, '_anwpfl_club_away', true );
				$clubs_options = $this->plugin->club->get_clubs_options();

				if ( ! empty( $clubs_options[ $club_id ] ) ) {
					echo esc_html( $clubs_options[ $club_id ] );
				}

				echo '</span>';

				break;

			case 'anwpfl_match_datetime':
				$match_datetime = get_post_meta( $post_id, '_anwpfl_match_datetime', true );

				if ( ! empty( $match_datetime ) ) {
					echo esc_html( date_i18n( 'M j, Y', strtotime( $match_datetime ) ) ) . '<br>' . esc_html( date( 'H:i', strtotime( $match_datetime ) ) );
				}

				break;

			case 'match_id':
				echo (int) $post_id;
				break;
		}
	}

	/**
	 * Returns all available matchweek values.
	 *
	 * @return array
	 * @since 0.5.3
	 */
	public function get_matchweek_options() {

		global $wpdb;

		// Get finished matches
		return $wpdb->get_col(
			"
			SELECT DISTINCT match_week
			FROM {$wpdb->prefix}anwpfl_matches
			WHERE match_week != 0
			ORDER BY match_week ASC
			"
		);
	}

	/**
	 * Get match data.
	 *
	 * @param $match_id
	 *
	 * @return array|object|void|null
	 * @since 0.6.1
	 */
	public function get_match_data( $match_id ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT *
				FROM {$wpdb->prefix}anwpfl_matches
				WHERE match_id = %d
				",
				$match_id
			)
		);
	}

	/**
	 * Get match data.
	 *
	 * @param $m
	 * @param $args
	 * @param $context
	 * @param $layout
	 *
	 * @since 0.6.5
	 * @return array
	 */
	public function prepare_match_data_to_render( $m, $args = [], $context = 'shortcode', $layout = 'slim' ) {

		$args = wp_parse_args(
			$args,
			[
				'show_club_logos'     => 1,
				'show_match_datetime' => 1,
				'club_links'          => 1,
			]
		);

		// Club logos
		$club_home_logo = '';
		$club_away_logo = '';

		if ( 1 === intval( $args['show_club_logos'] ) ) {
			$club_home_logo = anwp_football_leagues()->club->get_club_logo_by_id( $m->home_club, 'slim' === $layout );
			$club_away_logo = anwp_football_leagues()->club->get_club_logo_by_id( $m->away_club, 'slim' === $layout );

			if ( empty( $club_home_logo ) ) {
				$club_home_logo = anwp_football_leagues()->helper->get_default_club_logo();
			}

			if ( empty( $club_away_logo ) ) {
				$club_away_logo = anwp_football_leagues()->helper->get_default_club_logo();
			}
		}

		// Stage title
		$stage_title = '';

		if ( '0' !== $m->main_stage_id ) {
			$stage_title = anwp_football_leagues()->competition->get_competition( (int) $m->competition_id )->stage_title;
		}

		$data = [
			'show_match_datetime' => anwp_football_leagues()->helper->string_to_bool( $args['show_match_datetime'] ),
			'kickoff'             => $m->kickoff,
			'kickoff_c'           => date_i18n( 'c', strtotime( $m->kickoff ) ),
			'kickoff_orig'        => isset( $m->kickoff_orig ) ? date_i18n( 'c', strtotime( $m->kickoff_orig ) ) : date_i18n( 'c', strtotime( $m->kickoff ) ),
			'club_links'          => anwp_football_leagues()->helper->string_to_bool( $args['club_links'] ),
			'home_club'           => $m->home_club,
			'away_club'           => $m->away_club,
			'club_home_title'     => anwp_football_leagues()->club->get_club_title_by_id( $m->home_club ),
			'club_away_title'     => anwp_football_leagues()->club->get_club_title_by_id( $m->away_club ),
			'club_home_logo'      => $club_home_logo,
			'club_away_logo'      => $club_away_logo,
			'club_home_link'      => anwp_football_leagues()->club->get_club_link_by_id( $m->home_club ),
			'club_away_link'      => anwp_football_leagues()->club->get_club_link_by_id( $m->away_club ),
			'match_id'            => $m->match_id,
			'finished'            => $m->finished,
			'home_goals'          => $m->home_goals,
			'away_goals'          => $m->away_goals,
			'home_goals_half'     => $m->home_goals_half,
			'away_goals_half'     => $m->away_goals_half,
			'home_goals_p'        => $m->home_goals_p,
			'away_goals_p'        => $m->away_goals_p,
			'home_goals_e'        => $m->home_goals_e,
			'away_goals_e'        => $m->away_goals_e,
			'home_goals_ft'       => $m->home_goals_ft,
			'away_goals_ft'       => $m->away_goals_ft,
			'match_week'          => $m->match_week,
			'stadium_id'          => $m->stadium_id,
			'competition_id'      => $m->competition_id,
			'season_id'           => $m->season_id,
			'main_stage_id'       => $m->main_stage_id,
			'extra'               => $m->extra,
			'stage_title'         => $stage_title,
			'attendance'          => $m->attendance,
			'special_status'      => $m->special_status,
			'aggtext'             => $m->aggtext,
			'permalink'           => empty( $m->permalink ) ? get_permalink( $m->match_id ) : $m->permalink,
			'context'             => $context,
		];

		// Date and time formats
		$custom_date_format = anwp_football_leagues()->get_option_value( 'custom_match_date_format' );
		$custom_time_format = anwp_football_leagues()->get_option_value( 'custom_match_time_format' );

		$data['match_date'] = date_i18n( $custom_date_format ?: 'j M Y', strtotime( $m->kickoff ) );
		$data['match_time'] = date( $custom_time_format ?: get_option( 'time_format' ), strtotime( $m->kickoff ) );

		// Set Club Abbr
		$data['club_home_abbr'] = anwp_football_leagues()->club->get_club_abbr_by_id( $m->home_club ) ?: $data['club_home_title'];
		$data['club_away_abbr'] = anwp_football_leagues()->club->get_club_abbr_by_id( $m->away_club ) ?: $data['club_away_title'];

		return $data;
	}

	/**
	 * Get event name from event object
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidVariableName.NotSnakeCaseMemberVar
	 *
	 * @param object $event
	 *
	 * @since 0.7.3
	 * @return string
	 */
	public function get_event_name_by_type( $event ) {

		$name = '';

		if ( 'goal' === $event->type ) {

			$name = esc_html( AnWPFL_Text::get_value( 'match__event__goal', _x( 'Goal', 'match event', 'anwp-football-leagues' ) ) );

			if ( 'yes' === $event->ownGoal ) {
				$name = esc_html( AnWPFL_Text::get_value( 'match__event__goal_own', _x( 'Goal (own)', 'match event', 'anwp-football-leagues' ) ) );
			} elseif ( 'yes' === $event->fromPenalty ) {
				$name = esc_html( AnWPFL_Text::get_value( 'match__event__goal_from_penalty', _x( 'Goal (from penalty)', 'match event', 'anwp-football-leagues' ) ) );
			}
		} elseif ( 'substitute' === $event->type ) {
			$name = esc_html( AnWPFL_Text::get_value( 'match__event__substitute', _x( 'Substitute', 'match event', 'anwp-football-leagues' ) ) );
		} elseif ( 'card' === $event->type ) {
			$card_options = anwp_football_leagues()->data->cards;
			$name         = isset( $card_options[ $event->card ] ) ? $card_options[ $event->card ] : '';
		} elseif ( 'missed_penalty' === $event->type ) {
			$name = esc_html( AnWPFL_Text::get_value( 'match__event__missed_penalty', _x( 'Missed Penalty', 'match event', 'anwp-football-leagues' ) ) );
		}

		return $name;
	}

	/**
	 * Helper function, returns match with id and title (kickoff date).
	 *
	 * @since 0.10.8
	 * @return array $output_data
	 */
	public function get_match_options() {

		static $options = null;

		if ( null === $options ) {

			$options = [];

			$posts = get_posts(
				[
					'numberposts' => - 1,
					'post_type'   => 'anwp_match',
				]
			);

			/** @var  $p WP_Post */
			foreach ( $posts as $p ) {
				$kickoff = get_post_meta( $p->ID, '_anwpfl_match_datetime', true );

				if ( ! empty( $kickoff ) ) {
					$kickoff = ' (' . explode( ' ', $kickoff )[0] . ')';
				} else {
					$kickoff = '';
				}

				$options[ $p->ID ] = $p->post_title . $kickoff;
			}
		}

		return $options;
	}

	/**
	 * Generate Match title
	 *
	 * @param array  $data
	 * @param string $home_club
	 * @param string $away_club
	 *
	 * @return string
	 * @since 0.10.14
	 */
	public function get_match_title_generated( $data, $home_club, $away_club ) {
		// %club_home% - %club_away% - %scores_home% - %scores_away% - %competition% - %kickoff%
		$match_title = trim( AnWPFL_Options::get_value( 'match_title_generator' ) );

		if ( false !== mb_strpos( $match_title, '%club_home%' ) ) {
			$match_title = str_ireplace( '%club_home%', $home_club, $match_title );
		}

		if ( false !== mb_strpos( $match_title, '%club_away%' ) ) {
			$match_title = str_ireplace( '%club_away%', $away_club, $match_title );
		}

		if ( false !== mb_strpos( $match_title, '%scores_home%' ) || false !== mb_strpos( $match_title, '%scores_away%' ) ) {
			$scores_home = '?';
			$scores_away = '?';

			if ( 'result' === $data['status'] ) {
				$scores_home = absint( $data['stats']->goalsH );
				$scores_away = absint( $data['stats']->goalsA );
			}

			$match_title = str_ireplace( '%scores_home%', $scores_home, $match_title );
			$match_title = str_ireplace( '%scores_away%', $scores_away, $match_title );
		}

		if ( false !== mb_strpos( $match_title, '%competition%' ) ) {
			$competition_title = anwp_football_leagues()->competition->get_competition( $data['competition'] )->title;
			$match_title       = str_ireplace( '%competition%', $competition_title, $match_title );
		}

		if ( false !== mb_strpos( $match_title, '%kickoff%' ) ) {
			$custom_date_format = anwp_football_leagues()->get_option_value( 'custom_match_date_format' );
			$match_title        = str_ireplace( '%kickoff%', date_i18n( $custom_date_format ?: 'Y-m-d', get_date_from_gmt( $data['match_datetime'], 'U' ) ), $match_title );
		}

		return sanitize_text_field( $match_title );
	}

	/**
	 * Generate Match slug
	 *
	 * @param array   $data
	 * @param string  $home_club
	 * @param string  $away_club
	 * @param WP_Post $post
	 *
	 * @return string
	 * @since 0.10.15
	 */
	public function get_match_slug_generated( $data, $home_club, $away_club, $post ) {

		if ( 'slug' === AnWPFL_Options::get_value( 'match_slug_generated_with' ) ) {
			$match_slug = [ get_post_field( 'post_name', $data['club_home'] ), get_post_field( 'post_name', $data['club_away'] ) ];
		} else {
			$match_slug = [ $home_club, $away_club ];
		}

		if ( ! empty( $data['match_datetime'] ) ) {
			$match_slug[] = explode( ' ', $data['match_datetime'] )[0];
		}

		$match_slug = implode( ' ', $match_slug );

		/**
		 * Filters a match slug before save.
		 *
		 * @since 0.5.3
		 *
		 * @param string  $match_title Match slug to be returned.
		 * @param string  $home_club   Home club title.
		 * @param string  $away_club   Away club title.
		 * @param WP_Post $post        Match WP_Post object
		 * @param array   $data        Match data
		 */
		$match_slug = apply_filters( 'anwpfl/match/slug_to_save', $match_slug, $home_club, $away_club, $post, $data );

		// return slug unique
		return wp_unique_post_slug( sanitize_title_with_dashes( $match_slug ), $post->ID, $post->post_status, $post->post_type, $post->post_parent );
	}

	/**
	 * Get all Matches with video
	 *
	 * @return array
	 * @since 0.10.23
	 */
	public function get_matches_with_video() {

		static $ids = null;

		if ( null === $ids ) {
			global $wpdb;

			$ids = $wpdb->get_col(
				"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_anwpfl_video_source' AND meta_value != ''
				"
			);

			$ids = array_unique( array_map( 'absint', $ids ) );
		}

		return $ids;
	}

	/**
	 * Get match outcome label
	 *
	 * @param object $data
	 * @param string $class
	 *
	 * @return string
	 * @since 0.10.23
	 */
	public function get_match_outcome_label( $data, $class = '' ) {

		$outcome_id = absint( $data->outcome_id );
		$home_id    = absint( $data->home_club );
		$away_id    = absint( $data->away_club );

		if ( ! absint( $data->finished ) || ( $outcome_id !== $home_id && $outcome_id !== $away_id ) ) {
			return '<span class="anwp-fl-outcome-label ' . esc_attr( $class ) . '"></span>';
		}

		$labels_l10n = anwp_football_leagues()->data->get_series();

		$result_class = 'anwp-bg-success';
		$result_code  = isset( $labels_l10n['w'] ) ? $labels_l10n['w'] : 'w';

		if ( $data->home_goals === $data->away_goals ) {
			$result_class = 'anwp-bg-warning';
			$result_code  = isset( $labels_l10n['d'] ) ? $labels_l10n['d'] : 'd';
		} elseif ( ( $home_id === $outcome_id && $data->home_goals < $data->away_goals ) || ( $outcome_id === $away_id && $data->home_goals > $data->away_goals ) ) {
			$result_class = 'anwp-bg-danger';
			$result_code  = isset( $labels_l10n['l'] ) ? $labels_l10n['l'] : 'l';
		}

		return '<span class="anwp-fl-outcome-label ' . $result_class . ' ' . esc_attr( $class ) . '">' . $result_code . '</span>';
	}

	/**
	 * Get match outcome label
	 *
	 * @param array $data
	 * @param int   $match_id
	 *
	 * @since 0.11.4
	 * @return bool
	 */
	public function save_missing_players( $data, $match_id ) {

		global $wpdb;

		if ( ! absint( $match_id ) ) {
			return false;
		}

		$this->remove_match_missing_players( $match_id );

		/*
		|--------------------------------------------------------------------
		| Prepare data for save
		|--------------------------------------------------------------------
		*/
		$table = $wpdb->prefix . 'anwpfl_missing_players';

		foreach ( $data as $missing_player ) {

			if ( ! absint( $missing_player->player ) ) {
				continue;
			}

			// Prepare data to insert
			$data = [
				'reason'    => $missing_player->reason,
				'match_id'  => $match_id,
				'club_id'   => absint( $missing_player->club ),
				'player_id' => absint( $missing_player->player ),
				'comment'   => sanitize_textarea_field( $missing_player->comment ),
			];

			// Insert data to DB
			$wpdb->insert( $table, $data );
		}

		return true;
	}

	/**
	 * Remove match missing players.
	 *
	 * @param $match_id
	 *
	 * @return bool
	 * @since 0.11.4
	 */
	public function remove_match_missing_players( $match_id ) {
		global $wpdb;

		if ( ! absint( $match_id ) ) {
			return false;
		}

		$table = $wpdb->prefix . 'anwpfl_missing_players';

		return $wpdb->delete( $table, [ 'match_id' => absint( $match_id ) ] );
	}

	/**
	 * Get Missed games by player ID and season ID.
	 *
	 * @param $player_id
	 * @param $season_id
	 *
	 * @return array
	 * @since 0.11.4
	 */
	public function get_player_missed_games_by_season( $player_id, $season_id ) {
		global $wpdb;

		if ( ! absint( $player_id ) || ( ! absint( $season_id ) && 'all' !== $season_id ) ) {
			return [];
		}

		// Get games with custom outcome
		$query = "
		SELECT p.match_id, p.player_id, p.club_id, p.reason, p.comment, m.kickoff, m.competition_id, m.main_stage_id, m.home_club, m.away_club, m.home_goals, m.away_goals
		FROM {$wpdb->prefix}anwpfl_missing_players p
		LEFT JOIN {$wpdb->prefix}anwpfl_matches m ON p.match_id = m.match_id
		";

		if ( 'all' === $season_id ) {
			$query .= $wpdb->prepare( ' WHERE p.player_id = %d', $player_id );
		} else {
			$query .= $wpdb->prepare( ' WHERE m.season_id = %d AND p.player_id = %d', $season_id, $player_id );
		}

		$query .= ' ORDER BY m.kickoff DESC';

		$matches = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL

		if ( empty( $matches ) ) {
			return [];
		}

		$data = [];

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
					'logo'    => get_post_meta( $competition->ID, '_anwpfl_logo', true ),
					'order'   => get_post_meta( $competition->ID, '_anwpfl_competition_order', true ),
				];
			}
		}

		// Add matches to competitions
		foreach ( $matches as $match ) {

			$competition_index = ( isset( $data[ $match->main_stage_id ] ) && (int) $data[ $match->main_stage_id ] )
				? (int) $match->main_stage_id
				: (int) $match->competition_id;

			if ( isset( $data[ $competition_index ] ) ) {
				$data[ $competition_index ]['matches'][] = $match;
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
	 * Get Missed Players for the Game
	 *
	 * @param $game_id
	 *
	 * @return array
	 * @since 0.13.7
	 */
	public function get_game_missed_players( $game_id ) {
		global $wpdb;

		if ( ! absint( $game_id ) ) {
			return [];
		}

		// Get games with custom outcome
		$query = $wpdb->prepare(
			"
			SELECT player_id as player, club_id as club, reason, comment
			FROM {$wpdb->prefix}anwpfl_missing_players
			WHERE match_id = %d
			",
			$game_id
		);

		return $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	/**
	 * Get game temporary players
	 *
	 * @param $game_id
	 *
	 * @return array
	 * @since 0.14.2
	 */
	public function get_temp_players( $game_id ) {

		static $players = [];

		if ( ! absint( $game_id ) ) {
			return [];
		}

		if ( ! isset( $players[ $game_id ] ) ) {

			$players[ $game_id ] = [];
			$game_players        = get_post_meta( $game_id, '_anwpfl_match_temp_players', true ) ? json_decode( get_post_meta( $game_id, '_anwpfl_match_temp_players', true ) ) : [];

			if ( ! empty( $game_players ) ) {
				foreach ( $game_players as $player ) {
					$players[ $game_id ][ $player->id ] = $player;
				}
			}
		}

		return $players[ $game_id ];
	}

	/**
	 * Get Post ID by External id
	 *
	 * @param $external_id
	 *
	 * @return string|null
	 * @since 0.14.2
	 */
	public function get_match_id_by_external_id( $external_id ) {

		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = '_anwpfl_match_external_id' AND meta_value = %s
				",
				$external_id
			)
		);
	}


	/**
	 * Get "load more" data
	 *
	 * @param $data
	 *
	 * @return string
	 * @since 0.15.0
	 */
	public function get_serialized_load_more_data( $data ) {

		$default_data = [
			'competition_id'        => '',
			'show_secondary'        => 0,
			'season_id'             => '',
			'league_id'             => '',
			'group_id'              => '',
			'type'                  => '',
			'limit'                 => 0,
			'date_from'             => '',
			'date_to'               => '',
			'stadium_id'            => '',
			'filter_by'             => '',
			'filter_values'         => '',
			'filter_by_clubs'       => '',
			'filter_by_matchweeks'  => '',
			'days_offset'           => '',
			'days_offset_to'        => '',
			'sort_by_date'          => '',
			'sort_by_matchweek'     => '',
			'club_links'            => true,
			'priority'              => '',
			'class'                 => '',
			'group_by'              => '',
			'group_by_header_style' => '',
			'show_club_logos'       => 1,
			'show_match_datetime'   => true,
			'competition_logo'      => '1',
			'exclude_ids'           => '',
			'include_ids'           => '',
			'outcome_id'            => '',
			'home_club'             => '',
			'away_club'             => '',
			'layout'                => 'slim',
			'header_class'          => '',
		];

		$options    = wp_parse_args( $data, $default_data );
		$output     = array_intersect_key( $options, $default_data );
		$min_output = [];

		foreach ( $output as $key => $value ) {
			if ( ! is_null( $value ) && '' !== $value ) {
				$min_output[ $key ] = $value;
			}
		}

		return wp_json_encode( $min_output );
	}

	/**
	 * Handle ajax request and provide games to load.
	 *
	 * @since 0.15.0
	 */
	public function load_more_games( WP_REST_Request $request ) {

		$args        = $request->get_params();
		$post_loaded = isset( $args['loaded'] ) ? absint( $args['loaded'] ) : 0;
		$post_qty    = isset( $args['qty'] ) ? absint( $args['qty'] ) : 0;

		// Parse with default values
		$args = (object) wp_parse_args(
			wp_unslash( $args ),
			[
				'competition_id'        => '',
				'show_secondary'        => 0,
				'season_id'             => '',
				'league_id'             => '',
				'group_id'              => '',
				'type'                  => '',
				'limit'                 => 0,
				'date_from'             => '',
				'date_to'               => '',
				'stadium_id'            => '',
				'filter_by'             => '',
				'filter_values'         => '',
				'filter_by_clubs'       => '',
				'filter_by_matchweeks'  => '',
				'days_offset'           => '',
				'days_offset_to'        => '',
				'sort_by_date'          => '',
				'sort_by_matchweek'     => '',
				'club_links'            => true,
				'priority'              => '',
				'class'                 => '',
				'group_by'              => '',
				'group_by_header_style' => '',
				'show_club_logos'       => 1,
				'show_match_datetime'   => true,
				'competition_logo'      => '1',
				'outcome_id'            => '',
				'exclude_ids'           => '',
				'include_ids'           => '',
				'home_club'             => '',
				'away_club'             => '',
				'layout'                => 'slim',
				'header_class'          => '',
			]
		);

		$data = [];

		foreach ( $args as $arg_key => $arg_value ) {
			$data[ $arg_key ] = sanitize_text_field( $arg_value );
		}

		$data['limit']  = $post_qty + 1;
		$data['offset'] = $post_loaded;

		// Get games
		$games = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $data );

		// Check next time "load more"
		$next_load = count( $games ) > $post_qty;

		if ( $next_load ) {
			array_pop( $games );
		}

		$group_current = isset( $args->group ) ? sanitize_text_field( $args->group ) : '';

		// Start output
		ob_start();

		foreach ( $games as $ii => $game ) {

			if ( '' !== $data['group_by'] ) {

				$group_text = '';

				// Check current group by value
				if ( 'stage' === $args->group_by && $group_current !== $game->competition_id ) {
					$group_text    = get_post_meta( $game->competition_id, '_anwpfl_stage_title', true );
					$group_current = $game->competition_id;
				} elseif ( 'competition' === $args->group_by && $group_current !== $game->competition_id ) {
					$group_text    = anwp_football_leagues()->competition->get_competition_title( $game->competition_id );
					$group_current = $game->competition_id;
				} elseif ( 'matchweek' === $args->group_by && $group_current !== $game->match_week && '0' !== $game->match_week ) {
					$group_text    = anwp_football_leagues()->competition->tmpl_get_matchweek_round_text( $game->match_week, $game->competition_id );
					$group_current = $game->match_week;
				} elseif ( 'day' === $args->group_by ) {
					$day_to_compare = date( 'Y-m-d', strtotime( $game->kickoff ) );

					if ( $day_to_compare !== $group_current ) {
						$group_text    = '0000-00-00 00:00:00' === $game->kickoff ? '&nbsp;' : date_i18n( anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M Y', strtotime( $game->kickoff ) );
						$group_current = $day_to_compare;
					}
				} elseif ( 'month' === $args->group_by ) {
					$month_to_compare = date( 'Y-m', strtotime( $game->kickoff ) );

					if ( $month_to_compare !== $group_current ) {
						$group_text    = '0000-00-00 00:00:00' === $game->kickoff ? '&nbsp;' : date_i18n( 'M Y', strtotime( $game->kickoff ) );
						$group_current = $month_to_compare;
					}
				}

				if ( $group_text ) {
					if ( 'secondary' === $args->group_by_header_style ) {
						anwp_football_leagues()->load_partial(
							[
								'text'  => esc_html( $group_text ),
								'class' => ' mt-4 mb-1',
							],
							'general/subheader'
						);
					} else {
						anwp_football_leagues()->load_partial(
							[
								'text'  => esc_html( $group_text ),
								'class' => ' mt-4',
							],
							'general/header'
						);
					}
				}
			}

			// Get match data to render
			$game_data = anwp_football_leagues()->match->prepare_match_data_to_render( $game, $args );

			$game_data['competition_logo'] = $args->competition_logo;
			$game_data['outcome_id']       = $args->outcome_id;

			anwp_football_leagues()->load_partial( $game_data, 'match/match', $args->layout ?: 'slim' );
		}

		$html_output = ob_get_clean();

		wp_send_json_success(
			[
				'html'   => $html_output,
				'next'   => $next_load,
				'group'  => $group_current,
				'offset' => $post_loaded + count( $games ),
			]
		);
	}
}
