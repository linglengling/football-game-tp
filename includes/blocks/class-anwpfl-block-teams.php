<?php
/**
 * AnWP Football Leagues :: Block > Teams
 *
 * @since   0.1.0
 * @package Football_Leagues
 */

class AnWPFL_Block_Teams {

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
			AnWP_Football_Leagues::dir( 'gutenberg/blocks/teams' ),
			[
				'title'           => 'FL Teams',
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
				'logo_size'      => 'big',
				'layout'         => '',
				'show_club_name' => 1,
				'exclude_ids'    => '',
				'include_ids'    => '',
				'team_title'     => '',
				'text_size'      => '',
				'width'          => '50',
				'multistage'     => 1,
			]
		);

		if ( empty( $attr['competition_id'] ) && empty( $attr['include_ids'] ) ) {
			ob_start();
			anwp_football_leagues()->load_partial(
				[
					'no_data_text' => __( 'Please specify Competition or Include IDs', 'anwp-football-leagues' ),
				],
				'general/no-data'
			);

			return ob_get_clean();
		}

		if ( ! empty( $attr['include_ids'] ) ) {
			$teams_array = wp_parse_id_list( $attr['include_ids'] );
		} else {

			$teams_array = AnWP_Football_Leagues::string_to_bool( $attr['multistage'] )
				? anwp_football_leagues()->competition->get_competition_clubs( $attr['competition_id'], 'all' )
				: anwp_football_leagues()->competition->get_competition_multistage_clubs( $attr['competition_id'] );

			// Check exclude ids
			if ( ! empty( $attr['exclude_ids'] ) ) {
				$teams_array = array_diff( $teams_array, wp_parse_id_list( $attr['exclude_ids'] ) );
			}
		}

		ob_start();

		if ( empty( $teams_array ) ) {
			anwp_football_leagues()->load_partial(
				[
					'no_data_text' => __( 'No teams', 'anwp-football-leagues' ),
				],
				'general/no-data'
			);

			return ob_get_clean();
		}

		$attr['width']   = ! absint( $attr['width'] ) ? 50 : absint( $attr['width'] );
		$style_font_size = absint( $attr['text_size'] ) ? ( 'font-size:' . absint( $attr['text_size'] ) . 'px' ) : '';
		?>
		<div class="anwp-b-wrap teams-gutenblock">
			<?php
			if ( 'list' === $attr['layout'] ) :
				foreach ( $teams_array as $team_id ) :
					$team_obj = anwp_football_leagues()->club->get_club( $team_id );

					if ( empty( $team_obj ) ) {
						continue;
					}

					$logo  = 'big' === $attr['include_ids'] && $team_obj->logo_big ? $team_obj->logo_big : $team_obj->logo;
					$title = 'title' === $attr['team_title'] ? $team_obj->title : $team_obj->abbr;
					?>
					<div class="d-flex align-items-center clubs-shortcode__wrapper club-logo position-relative anwp-fl-border-bottom anwp-border-light py-2">
						<img loading="lazy" class="clubs-shortcode__logo anwp-object-contain mr-2"
								style="width: <?php echo absint( $attr['width'] ); ?>px; height: <?php echo esc_attr( $attr['width'] ); ?>px;"
								src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $team_obj->title ); ?>">

						<?php if ( AnWP_Football_Leagues::string_to_bool( $attr['show_club_name'] ) ) : ?>
							<div class="clubs-shortcode__text anwp-text-xs anwp-leading-1" style="<?php echo esc_attr( $style_font_size ); ?>">
								<?php echo esc_html( $title ); ?>
							</div>
						<?php endif; ?>

						<a class="anwp-link-without-effects anwp-link-cover" href="<?php echo esc_url( $team_obj->link ); ?>"></a>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="d-flex flex-wrap">
					<?php
					foreach ( $teams_array as $team_id ) :
						$team_obj = anwp_football_leagues()->club->get_club( $team_id );

						if ( empty( $team_obj ) ) {
							continue;
						}

						$logo  = 'big' === $attr['include_ids'] && $team_obj->logo_big ? $team_obj->logo_big : $team_obj->logo;
						$title = 'title' === $attr['team_title'] ? $team_obj->title : $team_obj->abbr;
						?>
						<div class="clubs-shortcode__wrapper club-logo position-relative anwp-text-center p-2 m-1 anwp-fl-border anwp-border-light d-flex flex-column">
							<img loading="lazy" class="clubs-shortcode__logo anwp-object-contain mx-auto"
									style="width: <?php echo absint( $attr['width'] ); ?>px; height: <?php echo esc_attr( $attr['width'] ); ?>px;"
									src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $team_obj->title ); ?>">

							<?php if ( AnWP_Football_Leagues::string_to_bool( $attr['show_club_name'] ) ) : ?>
								<div class="clubs-shortcode__text anwp-text-center anwp-text-xs mt-auto anwp-leading-1 pt-1"
										style="width: <?php echo esc_attr( $attr['width'] ); ?>px; <?php echo esc_attr( $style_font_size ); ?>">
									<?php echo esc_html( $title ); ?>
								</div>
							<?php endif; ?>

							<a class="anwp-link-without-effects anwp-link-cover" href="<?php echo esc_url( $team_obj->link ); ?>"></a>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}

return new AnWPFL_Block_Teams();
