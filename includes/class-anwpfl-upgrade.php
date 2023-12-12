<?php
/**
 * AnWP Football Leagues Upgrade.
 *
 * @since   0.7.0
 * @package AnWP_Football_Leagues
 */


/**
 * AnWP Football Leagues Upgrade class.
 */
class AnWPFL_Upgrade {

	/**
	 * Parent plugin class.
	 *
	 * @var    AnWP_Football_Leagues
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @param  AnWP_Football_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		// Set up plugin instance
		$this->plugin = $plugin;

		$version_saved   = get_option( 'anwpfl_version', '0.1.0' );
		$version_current = AnWP_Football_Leagues::VERSION;

		if ( $version_saved === $version_current ) {
			return;
		}

		if ( version_compare( $version_saved, '0.7.3', '<' ) ) {
			$this->finish_upgrade();
		}

		if ( version_compare( $version_saved, '0.13.8', '<' ) ) {
			$this->upgrade_0_14();
			anwp_football_leagues()->cache->flush_all_cache();
		}

		if ( version_compare( $version_saved, '0.14.14', '<' ) ) {
			delete_transient( 'FL-COMPETITIONS-LIST' );
		}

		update_option( 'anwpfl_version', $version_current );
	}

	/**
	 * Finishing Upgrade
	 */
	public function finish_upgrade() {

		add_action( 'shutdown', 'flush_rewrite_rules' );
	}

	/**
	 * v0.14.0
	 */
	public function upgrade_0_14() {

		$customizer_settings = [];

		if ( 'yes' === AnWPFL_Options::get_value( 'load_alternative_page_layout' ) ) {
			if ( ! isset( $customizer_settings['general'] ) ) {
				$customizer_settings['general'] = [];
			}

			$customizer_settings['general']['load_alternative_page_layout'] = 'yes';
		}

		if ( 'no' === AnWPFL_Options::get_value( 'hide_post_titles' ) ) {
			if ( ! isset( $customizer_settings['general'] ) ) {
				$customizer_settings['general'] = [];
			}

			$customizer_settings['general']['hide_post_titles'] = 'no';
		}

		if ( 'no' === AnWPFL_Options::get_value( 'show_default_club_logo' ) ) {
			if ( ! isset( $customizer_settings['club'] ) ) {
				$customizer_settings['club'] = [];
			}

			$customizer_settings['club']['show_default_club_logo'] = 'no';
		}

		if ( AnWPFL_Options::get_value( 'default_club_logo' ) ) {
			if ( ! isset( $customizer_settings['club'] ) ) {
				$customizer_settings['club'] = [];
			}

			$customizer_settings['club']['default_club_logo'] = AnWPFL_Options::get_value( 'default_club_logo' );
		}

		if ( AnWPFL_Options::get_value( 'club_squad_layout' ) ) {
			if ( ! isset( $customizer_settings['squad'] ) ) {
				$customizer_settings['squad'] = [];
			}

			$customizer_settings['squad']['club_squad_layout'] = AnWPFL_Options::get_value( 'club_squad_layout' );
		}

		if ( 'yes' === AnWPFL_Options::get_value( 'standing_font_mono' ) ) {
			if ( ! isset( $customizer_settings['standing'] ) ) {
				$customizer_settings['standing'] = [];
			}

			$customizer_settings['standing']['standing_font_mono'] = 'yes';
		}

		if ( 'no' === AnWPFL_Options::get_value( 'use_abbr_in_standing_mini' ) ) {
			if ( ! isset( $customizer_settings['standing'] ) ) {
				$customizer_settings['standing'] = [];
			}

			$customizer_settings['standing']['use_abbr_in_standing_mini'] = 'no';
		}

		if ( 'hide' === AnWPFL_Options::get_value( 'fixture_flip_countdown' ) ) {
			if ( ! isset( $customizer_settings['match'] ) ) {
				$customizer_settings['match'] = [];
			}

			$customizer_settings['match']['fixture_flip_countdown'] = 'hide';
		}

		if ( 'yes' === AnWPFL_Options::get_value( 'match_slim_stadium_show' ) || ( ! empty( AnWPFL_Options::get_value( 'match_slim_bottom_line' ) ) && in_array( 'stadium', AnWPFL_Options::get_value( 'match_slim_bottom_line' ), true ) ) ) {
			if ( ! isset( $customizer_settings['match_list'] ) ) {
				$customizer_settings['match_list'] = [];
			}

			if ( ! isset( $customizer_settings['match_list']['match_slim_bottom_line'] ) ) {
				$customizer_settings['match_list']['match_slim_bottom_line'] = [];
			}

			$customizer_settings['match_list']['match_slim_bottom_line']['stadium'] = true;
		}

		if ( ! empty( AnWPFL_Options::get_value( 'match_slim_bottom_line' ) ) ) {
			if ( ! isset( $customizer_settings['match_list'] ) ) {
				$customizer_settings['match_list'] = [];
			}

			if ( ! isset( $customizer_settings['match_list']['match_slim_bottom_line'] ) ) {
				$customizer_settings['match_list']['match_slim_bottom_line'] = [];
			}

			if ( in_array( 'referee', AnWPFL_Options::get_value( 'match_slim_bottom_line' ), true ) ) {
				$customizer_settings['match_list']['match_slim_bottom_line']['referee'] = true;
			}

			if ( in_array( 'referee_fourth', AnWPFL_Options::get_value( 'match_slim_bottom_line' ), true ) ) {
				$customizer_settings['match_list']['match_slim_bottom_line']['referee_fourth'] = true;
			}

			if ( in_array( 'referee_assistants', AnWPFL_Options::get_value( 'match_slim_bottom_line' ), true ) ) {
				$customizer_settings['match_list']['match_slim_bottom_line']['referee_assistants'] = true;
			}
		}

		if ( 'desc' === AnWPFL_Options::get_value( 'competition_matchweeks_order' ) ) {
			if ( ! isset( $customizer_settings['competition'] ) ) {
				$customizer_settings['competition'] = [];
			}

			$customizer_settings['competition']['competition_matchweeks_order'] = 'desc';
		}

		if ( 'desc' === AnWPFL_Options::get_value( 'competition_rounds_order' ) ) {
			if ( ! isset( $customizer_settings['competition'] ) ) {
				$customizer_settings['competition'] = [];
			}

			$customizer_settings['competition']['competition_rounds_order'] = 'desc';
		}

		if ( AnWPFL_Options::get_value( 'default_player_photo' ) ) {
			if ( ! isset( $customizer_settings['player'] ) ) {
				$customizer_settings['player'] = [];
			}

			$customizer_settings['player']['default_player_photo'] = AnWPFL_Options::get_value( 'default_player_photo' );
		}

		if ( 'hide' === AnWPFL_Options::get_value( 'player_render_main_photo_caption' ) ) {
			if ( ! isset( $customizer_settings['player'] ) ) {
				$customizer_settings['player'] = [];
			}

			$customizer_settings['player']['player_render_main_photo_caption'] = 'hide';
		}

		if ( 'full' === AnWPFL_Options::get_value( 'player_opposite_club_name' ) ) {
			if ( ! isset( $customizer_settings['player'] ) ) {
				$customizer_settings['player'] = [];
			}

			$customizer_settings['player']['player_opposite_club_name'] = 'full';
		}

		if ( ! empty( $customizer_settings ) ) {
			update_option( 'anwp-fl-customizer', $customizer_settings );
		}
	}
}
