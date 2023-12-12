<?php
/**
 * AnWP Football Leagues :: Cache.
 *
 * @since   0.13.3
 * @package AnWP_Football_Leagues
 *
 */

/**
 * AnWP Football Leagues :: Cache.
 */
class AnWPFL_Cache {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues
	 */
	protected $plugin = null;

	/**
	 * Is cache active.
	 *
	 * @var AnWP_Football_Leagues
	 */
	protected $active = true;

	/**
	 * Cache group map.
	 *
	 * @var AnWP_Football_Leagues
	 */
	protected $groups_map = [];

	/**
	 * Constructor.
	 *
	 * @param AnWP_Football_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		global $wp_version;

		$this->plugin = $plugin;

		/*
		|--------------------------------------------------------------------
		| Cache Groups
		|--------------------------------------------------------------------
		*/
		$this->groups_map = [
			'anwp_match'         => 'anwpfl_cached_keys__game',
			'anwp_player'        => 'anwpfl_cached_keys__player',
			'anwp_transfer'      => 'anwpfl_cached_keys__transfer',
			'anwp_fl_suspension' => 'anwpfl_cached_keys__suspension',
		];

		/*
		|--------------------------------------------------------------------
		| Disable/Enable Cache
		|--------------------------------------------------------------------
		*/
		$this->active = 'no' !== AnWPFL_Options::get_value( 'cache_active', $this->active );

		/**
		 * Disable/Enable cache
		 *
		 * @param bool $is_active
		 */
		$this->active = apply_filters( 'anwpfl/cache/is_active', $this->active );

		if ( ! wp_next_scheduled( 'anwp_fl_cache_maybe_cleanup' ) && $this->active ) {
			wp_schedule_event( time() + 86400, version_compare( $wp_version, '5.4', '>=' ) ? 'weekly' : 'daily', 'anwp_fl_cache_maybe_cleanup' );
		}

		// Run Hooks
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since   0.13.3
	 */
	public function hooks() {

		// Run cache invalidating on changing plugin instances
		add_action( 'before_delete_post', [ $this, 'on_modify_post' ], 10, 2 );
		add_action( 'edit_post', [ $this, 'on_modify_post' ], 10, 2 );
		add_action( 'wp_insert_post', [ $this, 'on_modify_post' ], 10, 2 );
		add_action( 'anwp_fl_edit_post', [ $this, 'on_modify_post' ], 10, 2 );

		// Cron action
		add_action( 'anwp_fl_cache_maybe_cleanup', [ $this, 'cleanup_generated_keys' ], 10, 1 );
		add_action( 'anwp_fl_cache_maybe_reflush', [ $this, 'flush_cache_by_post_type' ], 10, 1 );

		add_action( 'permalink_structure_changed', [ $this, 'permalink_structure_changed' ], 10, 1 );
	}

	/**
	 * Get cached value
	 *
	 * @param string $cache_key
	 * @param string $dependent_group
	 * @param array  $default
	 *
	 * @return array|mixed
	 * @since   0.13.3
	 */
	public function get( $cache_key, $dependent_group = '', $default = [] ) {

		if ( ! $this->active ) {
			return false;
		}

		if ( $dependent_group && ! in_array( $cache_key, $this->get_saved_keys( $dependent_group ), true ) ) {
			return false;
		}

		$response = get_transient( $cache_key );

		return false !== $response ? $response : $default;
	}

	/**
	 * Sets/updates the value of a transient.
	 *
	 * @param $cache_key
	 * @param $value
	 * @param $dependent_group
	 */
	public function set( $cache_key, $value, $dependent_group = '' ) {

		if ( ! $this->active ) {
			return;
		}

		$expiration = $this->get_key_expiration( $cache_key );

		if ( $expiration ) {
			set_transient( $cache_key, $value, $expiration );

			// Add generated keys to the options
			if ( $dependent_group ) {
				if ( is_array( $dependent_group ) ) {
					foreach ( $dependent_group as $group ) {
						$this->maybe_add_cached_key( $cache_key, $group );
					}
				} else {
					$this->maybe_add_cached_key( $cache_key, $dependent_group );
				}
			}
		}
	}

	/**
	 * Maybe add cached key to the saved option
	 *
	 * @param string $cache_key
	 * @param string $dependent_group
	 */
	private function maybe_add_cached_key( $cache_key, $dependent_group ) {
		global $wpdb;

		if ( ! in_array( $cache_key, $this->get_saved_keys( $dependent_group ), true ) && ! empty( $this->groups_map[ $dependent_group ] ) ) {
			$wpdb->query(
				$wpdb->prepare(
					"
			        UPDATE $wpdb->options
			        SET option_value = CONCAT(option_value, %s)
			        WHERE option_name = %s
			        ",
					'+' . $cache_key . '+',
					$this->groups_map[ $dependent_group ]
				)
			);
		}
	}

	/**
	 * Get key expiration.
	 *
	 * @param string $cache_key
	 *
	 * @return string
	 */
	protected function get_key_expiration( $cache_key ) {

		$expiration_map = [
			'FL-PLAYER-PHOTO-MAP'                => WEEK_IN_SECONDS,
			'FL-PLAYERS-LIST'                    => WEEK_IN_SECONDS,
			'FL-REFEREES-LIST-SIMPLE'            => WEEK_IN_SECONDS,
			'FL-REFEREES-LIST'                   => WEEK_IN_SECONDS,
			'FL-STADIUMS-LIST'                   => WEEK_IN_SECONDS,
			'FL-CLUBS-LIST'                      => WEEK_IN_SECONDS,
			'FL-STANDINGS-LIST'                  => MONTH_IN_SECONDS,
			'FL-COMPETITIONS-LIST'               => WEEK_IN_SECONDS,
			'FL-PLAYER_tmpl_get_latest_matches'  => WEEK_IN_SECONDS,
			'FL-PLAYER_tmpl_get_players_by_type' => WEEK_IN_SECONDS,
			'FL-SHORTCODE_players'               => WEEK_IN_SECONDS,
			'FL-PLAYER_get_birthdays'            => DAY_IN_SECONDS,
			'FL-REFEREES-GAME-MAP'               => WEEK_IN_SECONDS,
			'FL-PRO-REFEREES-NAME-LIST'          => WEEK_IN_SECONDS,
		];

		$cache_group = explode( '__', $cache_key )[0];

		/**
		 * Add/Modify expiration keys (group) map
		 *
		 * @param array  $expiration_map
		 * @param string $cache_key
		 */
		$expiration_map = apply_filters( 'anwpfl/cache/expiration_map', $expiration_map, $cache_group, $cache_key );

		return isset( $expiration_map[ $cache_group ] ) ? $expiration_map[ $cache_group ] : HOUR_IN_SECONDS;
	}

	/**
	 * Delete cache
	 *
	 * @param $cache_key
	 */
	public function delete( $cache_key ) {
		delete_transient( $cache_key );
	}

	/**
	 * Run on post delete/update to invalidate some cache.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post_obj
	 */
	public function on_modify_post( $post_id, $post_obj ) {

		if ( ! $this->active ) {
			return;
		}

		if ( isset( $post_obj->post_type ) ) {
			$this->flush_cache_by_post_type( $post_obj->post_type );

			// On modify Plugin Instances
			if ( ! empty( $this->groups_map[ $post_obj->post_type ] ) ) {
				$this->schedule_cache_reflush( $post_obj->post_type );
			}
		}
	}

	/**
	 * Flush cache by post type
	 *
	 * @param string $post_type
	 */
	public function flush_cache_by_post_type( $post_type ) {

		if ( ! $this->active || empty( $post_type ) ) {
			return;
		}

		/*
		|--------------------------------------------------------------------
		| Remove Static Keys
		|--------------------------------------------------------------------
		*/

		// On modify PLAYER
		if ( 'anwp_player' === $post_type ) {
			$this->delete( 'FL-PLAYERS-LIST' );
			$this->delete( 'FL-PLAYER-PHOTO-MAP' );
		}

		// On modify REFEREE
		if ( 'anwp_referee' === $post_type ) {
			$this->delete( 'FL-REFEREES-LIST-SIMPLE' );
			$this->delete( 'FL-REFEREES-LIST' );
			$this->delete( 'FL-PRO-REFEREES-NAME-LIST' );
		}

		// On modify STADIUM
		if ( 'anwp_stadium' === $post_type ) {
			$this->delete( 'FL-STADIUMS-LIST' );
		}

		// On modify CLUB
		if ( 'anwp_club' === $post_type ) {
			$this->delete( 'FL-CLUBS-LIST' );
		}

		// On modify COMPETITION
		if ( 'anwp_competition' === $post_type ) {
			$this->delete( 'FL-COMPETITIONS-LIST' );
		}

		// On modify STANDING
		if ( 'anwp_standing' === $post_type ) {
			$this->delete( 'FL-STANDINGS-LIST' );
		}

		// On modify GAME
		if ( 'anwp_match' === $post_type ) {
			$this->delete( 'FL-REFEREES-GAME-MAP' );
		}

		/*
		|--------------------------------------------------------------------
		| Remove Dynamic Keys
		|--------------------------------------------------------------------
		*/

		// On modify Plugin Instances
		if ( ! empty( $this->groups_map[ $post_type ] ) ) {
			$this->remove_cached_group_keys( $post_type );
		}
	}

	/**
	 * Schedule Re-Flush cache to fix trailing queries
	 *
	 * @param string $post_type
	 */
	private function schedule_cache_reflush( $post_type ) {

		if ( empty( $post_type ) ) {
			return;
		}

		$args = [ $post_type ];

		if ( wp_next_scheduled( 'anwp_fl_cache_maybe_reflush', $args ) ) {
			wp_clear_scheduled_hook( 'anwp_fl_cache_maybe_reflush', $args );
		}

		wp_schedule_single_event( time() + 180, 'anwp_fl_cache_maybe_reflush', $args );
	}

	/**
	 * Run on post update to invalidate some cache.
	 *
	 * @param string $dependent_group
	 * @since 0.13.3
	 */
	private function remove_cached_group_keys( $dependent_group ) {

		global $wpdb;

		if ( empty( $dependent_group ) || empty( $this->groups_map[ $dependent_group ] ) ) {
			return;
		}

		$saved_keys = $this->get_saved_keys( $dependent_group, true );
		$wpdb->update( $wpdb->options, [ 'option_value' => '' ], [ 'option_name' => $this->groups_map[ $dependent_group ] ] );

		foreach ( $saved_keys as $saved_key ) {

			if ( empty( $saved_key ) ) {
				continue;
			}

			$this->delete( $saved_key );
		}
	}

	/**
	 * Remove single cached key
	 *
	 * @param string $cached_key
	 * @param string $dependent_group
	 *
	 * @since 0.13.3
	 */
	private function remove_cached_group_key( $cached_key, $dependent_group ) {

		global $wpdb;

		if ( empty( $cached_key ) || empty( $dependent_group ) || empty( $this->groups_map[ $dependent_group ] ) ) {
			return;
		}

		$wpdb->query(
			$wpdb->prepare(
				"
			        UPDATE $wpdb->options
			        SET option_value = REPLACE(option_value, %s, '')
			        WHERE option_name = %s
			        ",
				'+' . $cached_key . '+',
				$this->groups_map[ $dependent_group ]
			)
		);
	}

	/**
	 * Removes all cache items.
	 *
	 * @return bool
	 * @since  0.13.3
	 */
	public function flush_all_cache() {

		/*
		|--------------------------------------------------------------------
		| Remove Static Keys
		|--------------------------------------------------------------------
		*/
		$this->delete( 'FL-PLAYERS-LIST' );
		$this->delete( 'FL-PLAYER-PHOTO-MAP' );
		$this->delete( 'FL-REFEREES-LIST-SIMPLE' );
		$this->delete( 'FL-REFEREES-LIST' );
		$this->delete( 'FL-STADIUMS-LIST' );
		$this->delete( 'FL-CLUBS-LIST' );
		$this->delete( 'FL-COMPETITIONS-LIST' );
		$this->delete( 'FL-STANDINGS-LIST' );
		$this->delete( 'FL-REFEREES-GAME-MAP' );
		$this->delete( 'FL-PRO-REFEREES-NAME-LIST' );

		/*
		|--------------------------------------------------------------------
		| Remove Generated Keys
		|--------------------------------------------------------------------
		*/
		foreach ( array_keys( $this->groups_map ) as $cached_group ) {
			$this->remove_cached_group_keys( $cached_group );
		}

		return true;
	}

	/**
	 * Removes cache items on permalinks structure changed.
	 *
	 * @since  0.13.3
	 */
	public function permalink_structure_changed() {
		$this->delete( 'FL-CLUBS-LIST' );
	}

	/**
	 * Clean Up generated expired keys (CRON)
	 *
	 * @return bool
	 */
	public function cleanup_generated_keys() {

		foreach ( array_keys( $this->groups_map ) as $cached_group ) {
			foreach ( $this->get_saved_keys( $cached_group ) as $cached_key ) {
				if ( false === get_transient( $cached_key ) ) {
					$this->remove_cached_group_key( $cached_key, $cached_group );
				}
			}
		}

		return true;
	}

	/**
	 * Get saved cached keys
	 *
	 * @param $group
	 * @param $force_get
	 *
	 * @return mixed|string
	 */
	private function get_saved_keys( $group = 'anwp_match', $force_get = false ) {

		global $wpdb;
		static $keys = [];

		if ( empty( $this->groups_map[ $group ] ) ) {
			return [];
		}

		if ( ! isset( $keys[ $group ] ) || $force_get ) {

			$keys[ $group ] = [];

			$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $this->groups_map[ $group ] ) );

			if ( is_object( $row ) && ! empty( $row->option_value ) ) {
				foreach ( explode( '++', $row->option_value ) as $cached_key ) {
					if ( $cached_key ) {
						$keys[ $group ][] = trim( $cached_key, '+' );
					}
				}
			} elseif ( ! is_object( $row ) ) {
				update_option( $this->groups_map[ $group ], '', false );
			}
		}

		return $keys[ $group ];
	}
}
