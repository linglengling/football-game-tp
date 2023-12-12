<?php
/**
 * AnWP Football Leagues :: Customizer.
 *
 * @package AnWP_Football_Leagues
 */

class AnWPFL_Customizer {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Football_Leagues
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 * Register Custom Post Types.
	 *
	 * See documentation in CPT_Core, and in wp-includes/post.php.
	 *
	 * @param  AnWP_Football_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		$this->hooks();

		require_once AnWP_Football_Leagues::dir( 'includes/customizer/custom-controls.php' );
	}

	/**
	 * Initiate our hooks.
	 */
	public function hooks() {
		add_action( 'customize_register', [ $this, 'register_customizer_settings' ] );

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( defined( 'SOCSS_VERSION' ) && ! is_admin() && isset( $_GET['so_css_preview'] ) && 'no' !== get_option( 'anwp_fl_customizer_mode' ) ) {
			add_filter( 'wp_enqueue_scripts', [ $this, 'enqueue_so_css_inspector_scripts' ], 20, 1 );
		}

		add_action( 'rest_api_init', [ $this, 'add_rest_routes' ] );
	}

	/**
	 * Register REST routes.
	 *
	 * @since 0.14.0
	 */
	public function add_rest_routes() {
		register_rest_route(
			'anwpfl/v1',
			'/customize/toggle-mode',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'customize_toggle_mode' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Callback for the rest route "/helper/recalculate-matches-stats/"
	 *
	 * @param WP_REST_Request $request
	 *
	 * @since 0.14.0
	 * @return mixed
	 */
	public function customize_toggle_mode( WP_REST_Request $request ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Access Denied !!!' );
		}

		// Get Request params
		$params = $request->get_params();

		if ( ! isset( $params['mode_active'] ) ) {
			return new WP_Error( 'rest_invalid', 'Incorrect Data', [ 'status' => 400 ] );
		}

		if ( 'yes' === $params['mode_active'] ) {
			update_option( 'anwp_fl_customizer_mode', 'no' );
		} else {
			delete_option( 'anwp_fl_customizer_mode' );
		}

		return rest_ensure_response( [] );
	}

	/**
	 * @return void
	 */
	public function enqueue_so_css_inspector_scripts() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		wp_deregister_script( 'siteorigin-css-inspector' );

		wp_enqueue_script(
			'siteorigin-css-inspector',
			AnWP_Football_Leagues::url( 'includes/customizer/so-css-inspector-mod.min.js' ),
			[
				'jquery',
				'underscore',
				'backbone',
			],
			'1.5.1',
			true
		);

		wp_localize_script( 'siteorigin-css-inspector', 'socssOptions', array() );

		$plugin_customizer_classes = [
			'anwp-fl-block-header',
			'anwp-fl-block-header__text',
			'anwp-fl-season-dropdown',
			'club',
			'club-header',
			'club-header__logo',
			'club-header__logo-wrapper',
			'club-header__options',
			'club-header__option-title',
			'club-header__option-value',
			'club-description',
			'club-gallery',
			'club-gallery__notes',
			'club-fixtures',
			'squad',
			'squad-rows',
			'squad-rows__header-title',
			'squad-rows__header-param',
			'squad-rows__number',
			'squad-rows__photo-wrapper',
			'squad-rows__photo',
			'squad-rows__status-badge',
			'squad-rows__name',
			'squad-rows__age',
			'squad-rows__nationality',
			'squad-blocks',
			'squad-blocks__header',
			'squad-blocks__block',
			'squad-blocks__photo-wrapper',
			'squad-blocks__photo',
			'squad-blocks__player-number',
			'squad-blocks__status-badge',
			'squad-blocks__name',
			'squad-blocks__player-param',
			'squad-blocks__player-param-title',
			'squad-blocks__player-param-value',
			'match-slim',
			'match-slim__stadium-referee',
			'match-slim__inner',
			'match-slim__outcome',
			'match-slim__inner-wrapper',
			'match-slim__main-meta',
			'match-slim__main-content',
			'match-slim__competition-wrapper',
			'match-slim__competition-logo',
			'match-slim__competition-title',
			'match-slim__date-wrapper',
			'match-slim__date-icon',
			'match-slim__date',
			'match-slim__time',
			'match-slim__top-bar-outcome',
			'match-slim__team-wrapper',
			'match-slim__team-home-title',
			'match-slim__team-home-logo',
			'match-slim__scores-wrapper',
			'match-slim__scores-number',
			'match-slim__scores-home',
			'match-slim__scores-away',
			'match-slim__team-away-logo',
			'match-slim__team-away-title',
			'match-slim__footer',
			'match-slim__stadium',
			'match-slim__referees',
			'match-slim__bottom-special',
			'match-list__live-block',
			'match-slim__scores-number-live',
			'anwp-match-slim-btn',
			'match-slim__prediction',
			'match-slim__prediction-term',
			'match-slim__prediction-value',
			'club-form__item',
			'club-title',
			'club-title__logo',
			'club-title__name',
			'match-stats',
			'match-stats__teams',
			'match-stats__stat-wrapper',
			'match-stats__stat-name',
			'match-stats__stat-row',
			'match-stats__stat-value',
			'match-stats__stat-bar',
			'match-stats__stat-bar-inner',
			'match-cards',
			'match__event-row',
			'match__event-team-row',
			'match__event-icon',
			'match__event-content',
			'match__event-type',
			'match__event-player',
			'match__event-minute',
			'match__event-minute-add',
			'match-gallery',
			'match-gallery__images',
			'match-gallery__notes',
			'match-goals',
			'match-latest',
			'match-lineups',
			'match__player-wrapper',
			'match__player-number',
			'match__player-flag',
			'options__flag',
			'match__player-position',
			'match__player-name',
			'anwp-fl-subheader',
			'match-missed-penalties',
			'match-missing',
			'match-missing__wrapper',
			'match-missing__team-wrapper',
			'match-missing__reason',
			'match-penalty-shootout',
			'match-referees',
			'match__referee-wrapper',
			'match__referee-outer',
			'match__referee-job',
			'match__referee-name',
			'match-substitutes',
			'match-summary',
			'match__summary',
			'match-video',
			'match__video',
			'anwp-video-grid',
			'match__event-player-name',
			'match__player-name-wrapper',
			'match__goals-assistant',
			'match-header',
			'match-header__top',
			'match-header__date',
			'match-header__competition',
			'match-header__competition-link',
			'match-header__period',
			'match-header__period-item',
			'match-header__main',
			'match-header__team-wrapper',
			'match-header__team-logo',
			'match-header__team-title',
			'match-header__scores-wrapper',
			'match__scores-number',
			'match__scores-number-separator',
			'match-header__outcome',
			'match-header__outcome-text',
			'match-header__special-status',
			'match-header__special-status-text',
			'anwp-match-flip-countdown',
			'anwp-match-flip-countdown-container',
			'match-header__bottom',
			'match__stadium',
			'match__stadium-title',
			'match__referee',
			'player-description',
			'player-gallery',
			'player-gallery__gallery',
			'player-gallery__notes',
			'player-header',
			'player-header__photo-wrapper',
			'player-header__photo-caption',
			'player-header__inner',
			'player-header__options',
			'player-header__option-title',
			'player-header__option-value',
			'player-header__option__full_name',
			'player-header__option__position',
			'player-header__option__national_team',
			'player-header__option__club_id',
			'player-header__option__nationality',
			'player-header__option__place_of_birth',
			'player-header__option__birth_date',
			'player-header__option__age',
			'player-header__option__death_date',
			'player-header__option__weight',
			'player-header__option__height',
			'player-header__option__social',
			'player-matches',
			'player-matches__wrapper',
			'player-matches__date',
			'player-matches__competition',
			'player-matches__for',
			'player-matches__opponent',
			'player-matches__home-away',
			'player-matches__result',
			'player-matches__minutes',
			'player-matches__goals_conceded',
			'player-matches__goals',
			'player-matches__assists',
			'player-missed',
			'player-missed__wrapper',
			'player-missed__competition',
			'player-missed__date',
			'player-missed__date-teams',
			'player-missed__for',
			'player-missed__opponent',
			'player-missed__reason',
			'player-stats',
			'player-stats__wrapper',
			'player-stats__competition',
			'player-stats__played',
			'player-stats__started',
			'player-stats__sub_in',
			'player-stats__minutes',
			'player-stats__card_y',
			'player-stats__card_yr',
			'player-stats__card_r',
			'player-stats__goals_conceded',
			'player-stats__clean_sheets',
			'player-stats__goals',
			'player-stats__assist',
			'player-stats__goals_own',
			'player-stats__totals',
			'player-stats__competition_totals',
			'anwp-fl-block-subheader',
			'referee-header',
			'referee-header__photo-wrapper',
			'referee-header__photo-caption',
			'referee-header__inner',
			'referee-header__options',
			'referee-header__option__job_title',
			'referee-header__option-value',
			'referee-header__option-title',
			'referee-header__option__nationality',
			'referee-header__option__place_of_birth',
			'referee-header__option__birth_date',
			'referee-header__option__age',
			'player-description',
			'referee-finished',
			'referee-finished__header',
			'referee-fixtures',
			'referee-fixtures__header',
			'staff-header',
			'staff-header__photo-wrapper',
			'staff-header__photo',
			'staff-header__photo-caption',
			'staff-header__inner',
			'staff-header__options',
			'staff-header__option__club',
			'staff-header__option-title',
			'staff-header__option-value',
			'staff-header__option__job_title',
			'staff-header__option__nationality',
			'staff-header__option__place_of_birth',
			'staff-header__option__birth_date',
			'staff-header__option__age',
			'staff-description',
			'staff-history',
			'staff-history__wrapper',
			'staff-history__club',
			'staff-history__job',
			'staff-history__from',
			'staff-history__to',
			'staff',
			'stadium',
			'stadium-description',
			'stadium-fixtures',
			'stadium-gallery',
			'stadium-gallery__notes',
			'stadium-header',
			'stadium-header__photo-wrapper',
			'stadium-header__photo',
			'stadium-header__options',
			'stadium-header__option__city',
			'stadium-header__option-title',
			'stadium-header__option-value',
			'stadium-header__option__clubs',
			'stadium-header__option__address',
			'stadium-header__option__capacity',
			'stadium-header__option__opened',
			'stadium-header__option__surface',
			'stadium-header__option__website',
			'stadium-latest',
			'stadium-map',
			'map--stadium',
			'competition',
			'competition__stage-title',
			'competition__round-title',
			'competition__group-wrapper',
			'competition__title-group',
			'competition__matchweek-title',
			'competition-tabs',
			'competition__tabs',
			'anwp-fl-tabs__item',
			'competition__tabs-content',
			'anwp-fl-tabs-content',
			'anwp-fl-tab__content',
			'competition-tabs__group-title',
			'competition-tabs__round-title',
			'competition-header',
			'competition-header__logo-wrapper',
			'competition-header__logo',
			'competition-header__title-wrapper',
			'competition-header__title',
			'competition-header__sub-title',
			'player-birthday-card__date-subtitle',
			'player-birthday-card__date-subtitle-text',
			'player-birthday-card',
			'player-birthday-card__photo',
			'player-birthday-card__photo-wrapper',
			'player-birthday-card__meta',
			'player-birthday-card__name',
			'player-birthday-card__position',
			'player-birthday-card__club-wrapper',
			'player-birthday-card__date-wrapper',
			'player-birthday-card__date',
			'player-birthday-card__date-text',
			'player-birthday-card__years',
			'player-birthday-card__age',
			'match-card',
			'match-card__header',
			'match-card__header-item',
			'match-card__club-title',
			'match-card__club-logo',
			'match-card__club-wrapper',
			'match-card__scores',
			'match-card__score',
			'match-card__footer',
			'match-card__time',
			'match-card__date',
			'match-modern',
			'match-modern__kickoff',
			'match-modern__date',
			'match-modern__time',
			'match-modern__inner-wrapper',
			'match-modern__team-wrapper',
			'match-modern__team-logo',
			'match-modern__team',
			'match-modern__scores',
			'match-modern__scores-number',
			'match-modern__time-result-wrapper',
			'match-simple',
			'match-simple__date',
			'match-simple__time',
			'match-simple__team-logo',
			'match-simple__team-logo-big',
			'match-simple__team',
			'match-simple__scores-number',
			'match-simple__time-result-wrapper',
			'match-simple__time-result',
			'widget-cards__link',
			'cards-shortcode',
			'cards-shortcode__rank',
			'cards-shortcode__clubs-players',
			'cards-shortcode__cards_y',
			'cards-shortcode__card_yr',
			'cards-shortcode__card_r',
			'cards-shortcode__pts',
			'cards-shortcode__countable',
			'cards-shortcode-mini',
			'cards-shortcode-mini__rank',
			'cards-shortcode-mini__clubs-players',
			'cards-shortcode-mini__cards_y',
			'cards-shortcode-mini__card_yr',
			'cards-shortcode-mini__card_r',
			'cards-shortcode-mini__pts',
			'cards-shortcode-mini__player-name',
			'cards-shortcode-mini__player-club',
			'cards-shortcode-mini__club-name',
			'cards-shortcode-mini__countable',
			'cards-shortcode__club-name',
			'cards-shortcode__player-club',
			'cards-shortcode__clubs',
			'cards-shortcode__player-name',
			'cards-shortcode__player-wrapper',
			'clubs-shortcode',
			'clubs-shortcode__wrapper',
			'clubs-shortcode__logo',
			'clubs-shortcode__text',
			'competition-list',
			'competition-list__country',
			'competition-list__country-name',
			'competition-list__country-collapsed-icon',
			'competition-list__competition',
			'competition-list__competition-name',
			'competition-list__season-name',
			'player-block',
			'player-block__header',
			'player-block__option',
			'player-block__option-label',
			'player-block__option-value',
			'player-block__option-value--wide',
			'player-block__club',
			'player-block__profile-link',
			'player-block__profile-link-btn',
			'players-shortcode',
			'players-shortcode__rank',
			'players-shortcode__player',
			'players-shortcode__club',
			'players-shortcode__nationality',
			'players-shortcode__played',
			'players-shortcode__stat',
			'players-shortcode__club-logo',
			'players-shortcode__club-title',
			'standing-table-mini',
			'standing__title',
			'standing-table-mini__club',
			'standing-table-mini__rank',
			'standing-table-mini__cell-number',
			'standing-table__notes',
			'standing-table__competition-link',
			'standing-table',
			'standing-table__rank',
			'standing-table__club',
			'standing-table__mini-cell-form',
			'standing-table__cell-form',
			'standing-table__cell-number',
			'match-widget',
			'match-widget__stadium',
			'match-widget__competition',
			'match-widget__kickoff',
			'match-widget__clubs',
			'match-widget__scores',
			'match-widget__scores-number',
			'match-widget__scores--home',
			'match-widget__scores--away',
			'match-widget__scores-number-status-0',
			'match-widget__scores-number-status-1',
			'match-widget__club-logo',
			'match-widget__club-title',
			'anwp-match-preview-link',
			'match-widget__link-preview',
			'match-widget__timer',
			'match-widget__timer-static',
			'match-widget__referee',
			'match-list__link-btn',
			'match__player-captain',
			'anwp-fl-lineups-event-minutes',
			'referee-header__option__death_date',
			'staff-header__option__death_date',
			'match-list__live-label',
			'match-list__live-time',
			'match-list__live-status',
			'referee-header__option__full_name',
			'anwp-fl-game-countdown__inner',
			'anwp-fl-game-countdown',
			'anwp-fl-game-countdown__item',
			'anwp-fl-game-countdown__label',
			'anwp-fl-game-countdown__value',
			'anwp-fl-game-countdown__separator',
			'match-widget__extra',
			'club-subteams',
			'club-subteams__item',
			'club-subteams__item--active',
		];

		$plugin_customizer_classes = apply_filters( 'anwpfl/customizer/plugin-classes', $plugin_customizer_classes );

		wp_localize_script( 'siteorigin-css-inspector', '_AnWP_CSS_Classes', $plugin_customizer_classes );
		wp_localize_script(
			'siteorigin-css-inspector',
			'_AnWP_SOCSS_Mode',
			[
				'admin_url' => esc_url_raw( admin_url( 'admin.php?page=anwpfl-plugin-customize' ) ),
				'active'    => true,
			]
		);
	}

	/**
	 * Register Customizer settings
	 */
	public function register_customizer_settings( $wp_customize ) {

		$wp_customize->add_panel(
			'anwp_fl_panel',
			[
				'title'       => __( 'Football Leagues', 'anwp-football-leagues' ),
				'description' => '', // Include html tags such as <p>.
				'priority'    => 160, // Mixed with top-level-section hierarchy.
			]
		);

		$wp_customize->add_section(
			'fl_font_sizes',
			// Arguments array
			[
				'title' => __( 'Font Sizes', 'anwp-football-leagues' ),
				'panel' => 'anwp_fl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| anwp-text-xxs
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[font_sizes][text-xxs]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[font_sizes][text-xxs]',
			[
				'type'               => 'number',
				'description_hidden' => false,
				'description'        => __( 'default', 'anwp-football-leagues' ) . ': 10px',
				'label'              => __( 'Font Size', 'anwp-football-leagues' ) . ' - XXS',
				'section'            => 'fl_font_sizes',
				'settings'           => 'anwp-fl-customizer[font_sizes][text-xxs]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| anwp-text-xs
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[font_sizes][text-xs]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[font_sizes][text-xs]',
			[
				'type'               => 'number',
				'description_hidden' => false,
				'description'        => __( 'default', 'anwp-football-leagues' ) . ': 12px',
				'label'              => __( 'Font Size', 'anwp-football-leagues' ) . ' - XS',
				'section'            => 'fl_font_sizes',
				'settings'           => 'anwp-fl-customizer[font_sizes][text-xs]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| anwp-text-sm
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[font_sizes][text-sm]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[font_sizes][text-sm]',
			[
				'type'               => 'number',
				'description_hidden' => false,
				'label'              => __( 'Font Size', 'anwp-football-leagues' ) . ' - SM',
				'description'        => __( 'default', 'anwp-football-leagues' ) . ': 14px',
				'section'            => 'fl_font_sizes',
				'settings'           => 'anwp-fl-customizer[font_sizes][text-sm]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| anwp-text-base
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[font_sizes][text-base]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[font_sizes][text-base]',
			[
				'type'               => 'number',
				'description_hidden' => false,
				'description'        => __( 'default', 'anwp-football-leagues' ) . ': 16px',
				'label'              => __( 'Font Size', 'anwp-football-leagues' ) . ' - Base',
				'section'            => 'fl_font_sizes',
				'settings'           => 'anwp-fl-customizer[font_sizes][text-base]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| anwp-text-lg
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[font_sizes][text-lg]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[font_sizes][text-lg]',
			[
				'type'               => 'number',
				'description_hidden' => false,
				'description'        => __( 'default', 'anwp-football-leagues' ) . ': 18px',
				'label'              => __( 'Font Size', 'anwp-football-leagues' ) . ' - LG',
				'section'            => 'fl_font_sizes',
				'settings'           => 'anwp-fl-customizer[font_sizes][text-lg]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| anwp-text-xl
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[font_sizes][text-xl]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[font_sizes][text-xl]',
			[
				'type'               => 'number',
				'description_hidden' => false,
				'description'        => __( 'default', 'anwp-football-leagues' ) . ': 20px',
				'label'              => __( 'Font Size', 'anwp-football-leagues' ) . ' - XL',
				'section'            => 'fl_font_sizes',
				'settings'           => 'anwp-fl-customizer[font_sizes][text-xl]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| anwp-text-2xl
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[font_sizes][text-2xl]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[font_sizes][text-2xl]',
			[
				'type'               => 'number',
				'description_hidden' => false,
				'description'        => __( 'default', 'anwp-football-leagues' ) . ': 24px',
				'label'              => __( 'Font Size', 'anwp-football-leagues' ) . ' - 2XL',
				'section'            => 'fl_font_sizes',
				'settings'           => 'anwp-fl-customizer[font_sizes][text-2xl]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| anwp-text-3xl
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[font_sizes][text-3xl]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[font_sizes][text-3xl]',
			[
				'type'               => 'number',
				'description_hidden' => false,
				'description'        => __( 'default', 'anwp-football-leagues' ) . ': 30px',
				'label'              => __( 'Font Size', 'anwp-football-leagues' ) . ' - 3XL',
				'section'            => 'fl_font_sizes',
				'settings'           => 'anwp-fl-customizer[font_sizes][text-3xl]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| anwp-text-4xl
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[font_sizes][text-4xl]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[font_sizes][text-4xl]',
			[
				'type'               => 'number',
				'description_hidden' => false,
				'description'        => __( 'default', 'anwp-football-leagues' ) . ': 36px',
				'label'              => __( 'Font Size', 'anwp-football-leagues' ) . ' - 4XL',
				'section'            => 'fl_font_sizes',
				'settings'           => 'anwp-fl-customizer[font_sizes][text-4xl]',
			]
		);

		//=================================
		//-- COLORS --
		//=================================
		$wp_customize->add_section(
			'fl_colors',
			[
				'title' => __( 'Colors', 'anwp-football-leagues' ),
				'panel' => 'anwp_fl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| anwp-bg-light
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[colors][bg-light]',
			[
				'default' => '#f8f9fa',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'anwp-fl-customizer[colors][bg-light]',
				[
					'description_hidden' => false,
					'description'        => __( 'default', 'anwp-football-leagues' ) . ': #f8f9fa',
					'label'              => __( 'Background Color', 'anwp-football-leagues' ) . ' - Light',
					'section'            => 'fl_colors',
					'settings'           => 'anwp-fl-customizer[colors][bg-light]',
				]
			)
		);

		/*
		|--------------------------------------------------------------------
		| anwp-bg-gray-light
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[colors][bg-gray-light]',
			[
				'default' => '#e9ecef',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'anwp-fl-customizer[colors][bg-gray-light]',
				[
					'description_hidden' => false,
					'description'        => __( 'default', 'anwp-football-leagues' ) . ': #e9ecef',
					'label'              => __( 'Background Color', 'anwp-football-leagues' ) . ' - Gray Light',
					'section'            => 'fl_colors',
					'settings'           => 'anwp-fl-customizer[colors][bg-gray-light]',
				]
			)
		);

		/*
		|--------------------------------------------------------------------
		| anwp-bg-gray
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[colors][bg-gray]',
			[
				'default' => '#dee2e6',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'anwp-fl-customizer[colors][bg-gray]',
				[
					'description_hidden' => false,
					'description'        => __( 'default', 'anwp-football-leagues' ) . ': #dee2e6',
					'label'              => __( 'Background Color', 'anwp-football-leagues' ) . ' - Gray',
					'section'            => 'fl_colors',
					'settings'           => 'anwp-fl-customizer[colors][bg-gray]',
				]
			)
		);

		/*
		|--------------------------------------------------------------------
		| anwp-bg-secondary
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[colors][bg-secondary]',
			[
				'default' => '#6c757d',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'anwp-fl-customizer[colors][bg-secondary]',
				[
					'description_hidden' => false,
					'description'        => __( 'default', 'anwp-football-leagues' ) . ': #6c757d',
					'label'              => __( 'Background Color', 'anwp-football-leagues' ) . ' - Secondary',
					'section'            => 'fl_colors',
					'settings'           => 'anwp-fl-customizer[colors][bg-secondary]',
				]
			)
		);

		/*
		|--------------------------------------------------------------------
		| anwp-border-light
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[colors][border-light]',
			[
				'default' => '#ced4da',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'anwp-fl-customizer[colors][border-light]',
				[
					'description_hidden' => false,
					'description'        => __( 'default', 'anwp-football-leagues' ) . ': #ced4da',
					'label'              => __( 'Border Color', 'anwp-football-leagues' ) . ' - Light',
					'section'            => 'fl_colors',
					'settings'           => 'anwp-fl-customizer[colors][border-light]',
				]
			)
		);

		//=================================
		//-- General --
		//=================================
		$wp_customize->add_section(
			'fl_general',
			[
				'title' => __( 'General', 'anwp-football-leagues' ),
				'panel' => 'anwp_fl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| load_alternative_page_layout
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[general][load_alternative_page_layout]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[general][load_alternative_page_layout]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Load alternative page layout (experimental)', 'anwp-football-leagues' ),
				'section'  => 'fl_general',
				'settings' => 'anwp-fl-customizer[general][load_alternative_page_layout]',
				'choices'  => [
					''    => __( 'No', 'anwp-football-leagues' ),
					'yes' => __( 'Yes', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| hide_post_titles
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[general][hide_post_titles]',
			[
				'default' => 'yes',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[general][hide_post_titles]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Hide post title for Match and Competition', 'anwp-football-leagues' ),
				'section'  => 'fl_general',
				'settings' => 'anwp-fl-customizer[general][hide_post_titles]',
				'choices'  => [
					'no'  => __( 'No', 'anwp-football-leagues' ),
					'yes' => __( 'Yes', 'anwp-football-leagues' ),
				],
			]
		);

		//=================================
		//-- Club --
		//=================================
		$wp_customize->add_section(
			'fl_club',
			[
				'title' => __( 'Club', 'anwp-football-leagues' ),
				'panel' => 'anwp_fl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| show_default_club_logo
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[club][show_default_club_logo]',
			[
				'default' => 'yes',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[club][show_default_club_logo]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Show default club logo', 'anwp-football-leagues' ),
				'section'  => 'fl_club',
				'settings' => 'anwp-fl-customizer[club][show_default_club_logo]',
				'choices'  => [
					'no'  => __( 'No', 'anwp-football-leagues' ),
					'yes' => __( 'Yes', 'anwp-football-leagues' ),
				],
			]
		);

		$wp_customize->add_setting(
			'anwp-fl-customizer[club][show_default_club_logo][notice]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			new AnWPFL_Simple_HTML_Custom_Control(
				$wp_customize,
				'anwp-fl-customizer[club][show_default_club_logo][notice]',
				[
					'description' => '<img style="height: 20px; margin-right: 5px; margin-bottom: -5px" src="' . AnWP_Football_Leagues::url( 'public/img/empty_logo.png' ) . '">' . esc_html__( 'will be visible if club logo is not set', 'anwp-football-leagues' ),
					'section'     => 'fl_club',
				]
			)
		);

		/*
		|--------------------------------------------------------------------
		| default_club_logo
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[club][default_club_logo]',
			[
				'default'           => '',
				'type'              => 'option',
				'sanitize_callback' => 'esc_url_raw',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'anwp-fl-customizer[club][default_club_logo]',
				[
					'label'   => esc_html__( 'Custom Default Club Logo', 'anwp-football-leagues' ),
					'section' => 'fl_club',
				]
			)
		);

		//=================================
		//-- Squad --
		//=================================
		$wp_customize->add_section(
			'fl_squad',
			[
				'title' => esc_html__( 'Squad', 'anwp-football-leagues' ),
				'panel' => 'anwp_fl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| club_squad_layout
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[squad][club_squad_layout]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[squad][club_squad_layout]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Squad Layout', 'anwp-football-leagues' ),
				'section'  => 'fl_squad',
				'settings' => 'anwp-fl-customizer[squad][club_squad_layout]',
				'choices'  => [
					''       => esc_html__( 'Table (default)', 'anwp-football-leagues' ),
					'blocks' => esc_html__( 'Blocks', 'anwp-football-leagues' ),
				],
			]
		);

		//=================================
		//-- Standing --
		//=================================
		$wp_customize->add_section(
			'fl_standing',
			[
				'title' => esc_html__( 'Standing', 'anwp-football-leagues' ),
				'panel' => 'anwp_fl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| standing_font_mono
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[standing][standing_font_mono]',
			[
				'default' => 'no',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[standing][standing_font_mono]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Use monospace font-family', 'anwp-football-leagues' ),
				'section'  => 'fl_standing',
				'settings' => 'anwp-fl-customizer[standing][standing_font_mono]',
				'choices'  => [
					'no'  => __( 'No', 'anwp-football-leagues' ),
					'yes' => __( 'Yes', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| use_abbr_in_standing_mini
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[standing][use_abbr_in_standing_mini]',
			[
				'default' => 'yes',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[standing][use_abbr_in_standing_mini]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Use Club abbreviation in Mini layout (widget)', 'anwp-football-leagues' ),
				'section'  => 'fl_standing',
				'settings' => 'anwp-fl-customizer[standing][use_abbr_in_standing_mini]',
				'choices'  => [
					'no'  => __( 'No', 'anwp-football-leagues' ),
					'yes' => __( 'Yes', 'anwp-football-leagues' ),
				],
			]
		);

		//=================================
		//-- Match --
		//=================================
		$wp_customize->add_section(
			'fl_match',
			[
				'title' => esc_html__( 'Match', 'anwp-football-leagues' ),
				'panel' => 'anwp_fl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| fixture_flip_countdown
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match][fixture_flip_countdown]',
			[
				'default' => 'show',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[match][fixture_flip_countdown]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Countdown in match fixture', 'anwp-football-leagues' ),
				'section'  => 'fl_match',
				'settings' => 'anwp-fl-customizer[match][fixture_flip_countdown]',
				'choices'  => [
					'hide' => __( 'Hide', 'anwp-football-leagues' ),
					'show' => __( 'Show', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| lineups_event_minutes
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match][lineups_event_minutes]',
			[
				'default' => 'show',
				'type'    => 'option',
			]
		);
		$wp_customize->add_control(
			'anwp-fl-customizer[match][lineups_event_minutes]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Minutes in Lineups Events', 'anwp-football-leagues' ),
				'section'  => 'fl_match',
				'settings' => 'anwp-fl-customizer[match][lineups_event_minutes]',
				'choices'  => [
					'hide' => __( 'Hide', 'anwp-football-leagues' ),
					'show' => __( 'Show', 'anwp-football-leagues' ),
				],
			]
		);

		//=================================
		//-- Match List --
		//=================================
		$wp_customize->add_section(
			'fl_match_list',
			[
				'title' => esc_html__( 'Match List', 'anwp-football-leagues' ),
				'panel' => 'anwp_fl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| match_simple_team_name
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match_list][match_simple_team_name]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[match_list][match_simple_team_name]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Team Name in "Simple Layout"', 'anwp-football-leagues' ),
				'section'  => 'fl_match_list',
				'settings' => 'anwp-fl-customizer[match_list][match_simple_team_name]',
				'choices'  => [
					''     => __( 'Abbreviation', 'anwp-football-leagues' ),
					'full' => __( 'Full Name', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| match_modern_team_name
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match_list][match_modern_team_name]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[match_list][match_modern_team_name]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Team Name in "Modern Layout"', 'anwp-football-leagues' ),
				'section'  => 'fl_match_list',
				'settings' => 'anwp-fl-customizer[match_list][match_modern_team_name]',
				'choices'  => [
					''     => __( 'Abbreviation', 'anwp-football-leagues' ),
					'full' => __( 'Full Name', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| kickoff_width
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match_list][match_slim_kickoff_width]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[match_list][match_slim_kickoff_width]',
			[
				'type'               => 'number',
				'description_hidden' => false,
				'description'        => __( 'default', 'anwp-football-leagues' ) . ': 70px',
				'label'              => __( 'Kickoff section Minimum Width', 'anwp-football-leagues' ),
				'section'            => 'fl_match_list',
				'settings'           => 'anwp-fl-customizer[match_list][match_slim_kickoff_width]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| match_slim_bottom_line notice
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match_list][match_slim_bottom_line][notice]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			new AnWPFL_Simple_HTML_Custom_Control(
				$wp_customize,
				'anwp-fl-customizer[match_list][match_slim_bottom_line][notice]',
				[
					'description' => '<strong>' . esc_html__( 'Bottom Line Info', 'anwp-football-leagues' ) . '</strong>',
					'section'     => 'fl_match_list',
				]
			)
		);

		/*
		|--------------------------------------------------------------------
		| match_slim_bottom_line stadium
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match_list][match_slim_bottom_line][stadium]',
			[
				'default' => false,
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[match_list][match_slim_bottom_line][stadium]',
			[
				'type'     => 'checkbox',
				'label'    => esc_html__( 'Stadium', 'anwp-football-leagues' ),
				'section'  => 'fl_match_list',
				'settings' => 'anwp-fl-customizer[match_list][match_slim_bottom_line][stadium]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| match_slim_bottom_line referee
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match_list][match_slim_bottom_line][referee]',
			[
				'default' => false,
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[match_list][match_slim_bottom_line][referee]',
			[
				'type'     => 'checkbox',
				'label'    => esc_html__( 'Referee', 'anwp-football-leagues' ),
				'section'  => 'fl_match_list',
				'settings' => 'anwp-fl-customizer[match_list][match_slim_bottom_line][referee]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| match_slim_bottom_line referee_assistants
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match_list][match_slim_bottom_line][referee_assistants]',
			[
				'default' => false,
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[match_list][match_slim_bottom_line][referee_assistants]',
			[
				'type'     => 'checkbox',
				'label'    => esc_html__( 'Referee Assistants', 'anwp-football-leagues' ),
				'section'  => 'fl_match_list',
				'settings' => 'anwp-fl-customizer[match_list][match_slim_bottom_line][referee_assistants]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| match_slim_bottom_line referee_fourth
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[match_list][match_slim_bottom_line][referee_fourth]',
			[
				'default' => false,
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[match_list][match_slim_bottom_line][referee_fourth]',
			[
				'type'     => 'checkbox',
				'label'    => esc_html__( 'Referee', 'anwp-football-leagues' ) . ' ' . esc_html__( 'Fourth official', 'anwp-football-leagues' ),
				'section'  => 'fl_match_list',
				'settings' => 'anwp-fl-customizer[match_list][match_slim_bottom_line][referee_fourth]',
			]
		);

		//=================================
		//-- Competition --
		//=================================
		$wp_customize->add_section(
			'fl_competition',
			[
				'title' => esc_html__( 'Competition', 'anwp-football-leagues' ),
				'panel' => 'anwp_fl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| competition_rounds_order
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[competition][competition_title_field]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[competition][competition_title_field]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Competition Title in Competition Header', 'anwp-football-leagues' ),
				'section'  => 'fl_competition',
				'settings' => 'anwp-fl-customizer[competition][competition_title_field]',
				'choices'  => [
					''            => __( 'League Name', 'anwp-football-leagues' ),
					'competition' => __( 'Competition Title', 'anwp-football-leagues' ),
				],
			]
		);

		//=================================
		//-- Stadium --
		//=================================
		$wp_customize->add_section(
			'fl_stadium',
			[
				'title' => esc_html__( 'Stadium', 'anwp-football-leagues' ),
				'panel' => 'anwp_fl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| map_consent_required
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[stadium][map_consent_required]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[stadium][map_consent_required]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Require consent before loading Map', 'anwp-football-leagues' ),
				'section'  => 'fl_stadium',
				'settings' => 'anwp-fl-customizer[stadium][map_consent_required]',
				'choices'  => [
					''    => __( 'No', 'anwp-football-leagues' ),
					'yes' => __( 'Yes', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| map_consent_text
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[stadium][map_consent_text]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[stadium][map_consent_text]',
			[
				'type'     => 'text',
				'label'    => esc_html__( 'Map consent - Text', 'anwp-football-leagues' ),
				'section'  => 'fl_stadium',
				'settings' => 'anwp-fl-customizer[stadium][map_consent_text]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| map_consent_btn_text
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[stadium][map_consent_btn_text]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[stadium][map_consent_btn_text]',
			[
				'type'     => 'text',
				'label'    => esc_html__( 'Map consent - Button Text', 'anwp-football-leagues' ),
				'section'  => 'fl_stadium',
				'settings' => 'anwp-fl-customizer[stadium][map_consent_btn_text]',
			]
		);

		/*
		|--------------------------------------------------------------------
		| competition_matchweeks_order
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[competition][competition_matchweeks_order]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[competition][competition_matchweeks_order]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Matchweeks order', 'anwp-football-leagues' ),
				'section'  => 'fl_competition',
				'settings' => 'anwp-fl-customizer[competition][competition_matchweeks_order]',
				'choices'  => [
					''     => __( 'Ascending (1...30)', 'anwp-football-leagues' ),
					'desc' => __( 'Descending (30...1)', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| competition_rounds_order
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[competition][competition_rounds_order]',
			[
				'default' => '',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[competition][competition_rounds_order]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Rounds order', 'anwp-football-leagues' ),
				'section'  => 'fl_competition',
				'settings' => 'anwp-fl-customizer[competition][competition_rounds_order]',
				'choices'  => [
					''     => __( 'Ascending (1...30)', 'anwp-football-leagues' ),
					'desc' => __( 'Descending (30...1)', 'anwp-football-leagues' ),
				],
			]
		);

		//=================================
		//-- Player & Staff --
		//=================================
		$wp_customize->add_section(
			'fl_player',
			[
				'title' => esc_html__( 'Player & Staff', 'anwp-football-leagues' ),
				'panel' => 'anwp_fl_panel',
			]
		);

		/*
		|--------------------------------------------------------------------
		| default_player_photo
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[player][default_player_photo]',
			[
				'default'           => '',
				'type'              => 'option',
				'sanitize_callback' => 'esc_url_raw',
			]
		);

		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'anwp-fl-customizer[player][default_player_photo]',
				[
					'label'   => esc_html__( 'Default Player Photo', 'anwp-football-leagues' ),
					'section' => 'fl_player',
				]
			)
		);

		/*
		|--------------------------------------------------------------------
		| player_render_main_photo_caption
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[player][player_render_main_photo_caption]',
			[
				'default' => 'show',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[player][player_render_main_photo_caption]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Main photo caption', 'anwp-football-leagues' ),
				'section'  => 'fl_player',
				'settings' => 'anwp-fl-customizer[player][player_render_main_photo_caption]',
				'choices'  => [
					'show' => __( 'Show', 'anwp-football-leagues' ),
					'hide' => __( 'Hide', 'anwp-football-leagues' ),
				],
			]
		);

		/*
		|--------------------------------------------------------------------
		| player_opposite_club_name
		|--------------------------------------------------------------------
		*/
		$wp_customize->add_setting(
			'anwp-fl-customizer[player][player_opposite_club_name]',
			[
				'default' => 'abbr',
				'type'    => 'option',
			]
		);

		$wp_customize->add_control(
			'anwp-fl-customizer[player][player_opposite_club_name]',
			[
				'type'     => 'select',
				'label'    => esc_html__( 'Opposite club name in Latest Player Matches', 'anwp-football-leagues' ),
				'section'  => 'fl_player',
				'settings' => 'anwp-fl-customizer[player][player_opposite_club_name]',
				'choices'  => [
					'abbr' => __( 'Abbreviation', 'anwp-football-leagues' ),
					'full' => __( 'Full Name', 'anwp-football-leagues' ),
				],
			]
		);
	}

	/**
	 * Get Customizer CSS
	 *
	 * @return string
	 * @since 0.14.0
	 */
	public function get_customizer_css() {

		$output_css = '';

		$plugin_options = get_option( 'anwp-fl-customizer' );

		if ( empty( $plugin_options ) ) {
			return '';
		}

		/*
		|--------------------------------------------------------------------
		| Load font sizes
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $plugin_options['font_sizes'] ) ) {
			$font_sizes = [
				'text-xxs'  => '10',
				'text-xs'   => '12',
				'text-sm'   => '14',
				'text-base' => '16',
				'text-lg'   => '18',
				'text-xl'   => '20',
				'text-2xl'  => '24',
				'text-3xl'  => '30',
				'text-4xl'  => '36',
			];

			foreach ( $font_sizes as $font_size_rule => $font_size_value ) {
				if ( ! empty( $plugin_options['font_sizes'][ $font_size_rule ] ) && absint( $plugin_options['font_sizes'][ $font_size_rule ] ) !== absint( $font_size_value ) ) {
					$output_css .= sprintf( '.anwp-%s {font-size: %dpx !important; line-height: 1.4 !important;}', $font_size_rule, absint( $plugin_options['font_sizes'][ $font_size_rule ] ) );
				}
			}
		}

		/*
		|--------------------------------------------------------------------
		| Load colors
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $plugin_options['colors']['border-light'] ) && '#ced4da' !== $plugin_options['colors']['border-light'] ) {
			$output_css .= sprintf( '.anwp-border-light, .anwp-fl-block-header, .anwp-fl-btn-outline, .match-modern__kickoff:after {border-color: %s !important;}', esc_attr( $plugin_options['colors']['border-light'] ) );
			$output_css .= sprintf( '.anwp-b-wrap .table-bordered, .anwp-b-wrap .table-bordered th, .anwp-b-wrap .table-bordered td {border-color: %s !important;}', esc_attr( $plugin_options['colors']['border-light'] ) );
		}

		if ( ! empty( $plugin_options['colors']['bg-light'] ) && '#f8f9fa' !== $plugin_options['colors']['bg-light'] ) {
			$output_css .= sprintf( '.anwp-bg-light {background-color: %s !important;}', esc_attr( $plugin_options['colors']['bg-light'] ) );
		}

		if ( ! empty( $plugin_options['colors']['bg-gray-light'] ) && '#e9ecef' !== $plugin_options['colors']['bg-gray-light'] ) {
			$output_css .= sprintf( '.anwp-bg-gray-light {background-color: %s !important;}', esc_attr( $plugin_options['colors']['bg-gray-light'] ) );
		}

		if ( ! empty( $plugin_options['colors']['bg-gray'] ) && '#dee2e6' !== $plugin_options['colors']['bg-gray'] ) {
			$output_css .= sprintf( '.anwp-bg-gray {background-color: %s !important;}', esc_attr( $plugin_options['colors']['bg-gray'] ) );
		}

		if ( ! empty( $plugin_options['colors']['bg-secondary'] ) && '#6c757d' !== $plugin_options['colors']['bg-secondary'] ) {
			$output_css .= sprintf( '.anwp-bg-secondary {background-color: %s !important;}', esc_attr( $plugin_options['colors']['bg-secondary'] ) );
		}

		/*
		|--------------------------------------------------------------------
		| match_slim_kickoff_width
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $plugin_options['match_list']['match_slim_kickoff_width'] ) && 70 !== absint( $plugin_options['match_list']['match_slim_kickoff_width'] ) ) {
			$output_css .= sprintf( '@media (min-width: 576px) { .match-slim__date-wrapper {min-width: %1$spx} .match-slim__date-wrapper + .match-list__live-block {min-width: %1$spx} }', esc_attr( absint( $plugin_options['match_list']['match_slim_kickoff_width'] ) ) );
		}

		return $output_css;
	}

	/**
	 * Get Customizer saved option
	 *
	 * @param string $section
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get_value( $section, $key, $default = '' ) {

		static $customizer_options = null;

		if ( null === $customizer_options ) {
			$customizer_options = get_option( 'anwp-fl-customizer' );
		}

		if ( empty( $customizer_options ) || empty( $section ) || empty( $key ) ) {
			return $default;
		}

		if ( ']' === mb_substr( $key, -1 ) ) {
			$array_parsed = explode( '[', trim( $key, ']' ) );

			if ( empty( $customizer_options[ $section ] ) || ! isset( $customizer_options[ $section ][ $array_parsed[0] ] ) || empty( $array_parsed[1] ) || ! isset( $customizer_options[ $section ][ $array_parsed[0] ][ $array_parsed[1] ] ) ) {
				return $default;
			}

			return $customizer_options[ $section ][ $array_parsed[0] ][ $array_parsed[1] ];

		} else {
			if ( empty( $customizer_options[ $section ] ) || ! isset( $customizer_options[ $section ][ $key ] ) ) {
				return $default;
			}
		}

		return $customizer_options[ $section ][ $key ];
	}

	/**
	 * Get Customizer saved option
	 *
	 * @param string $section
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public static function get_static_value( $section, $key, $default = '' ) {

		static $customizer_options = null;

		if ( null === $customizer_options ) {
			$customizer_options = get_option( 'anwp-fl-customizer' );
		}

		if ( empty( $customizer_options ) || empty( $section ) || empty( $key ) ) {
			return $default;
		}

		if ( ']' === mb_substr( $key, -1 ) ) {
			$array_parsed = explode( '[', trim( $key, ']' ) );

			if ( empty( $customizer_options[ $section ] ) || ! isset( $customizer_options[ $section ][ $array_parsed[0] ] ) || empty( $array_parsed[1] ) || ! isset( $customizer_options[ $section ][ $array_parsed[0] ][ $array_parsed[1] ] ) ) {
				return $default;
			}

			return $customizer_options[ $section ][ $array_parsed[0] ][ $array_parsed[1] ];

		} else {
			if ( empty( $customizer_options[ $section ] ) || ! isset( $customizer_options[ $section ][ $key ] ) ) {
				return $default;
			}
		}

		return $customizer_options[ $section ][ $key ];
	}
}
