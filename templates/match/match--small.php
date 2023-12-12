<?php
/**
 * The Template for displaying Match (small version).
 * BETA !!!
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match--small.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.14.0
 *
 * @version       0.14.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'show_match_datetime' => true,
		'kickoff'             => '',
		'match_date'          => '',
		'match_time'          => '',
		'club_links'          => true,
		'home_club'           => '',
		'away_club'           => '',
		'club_home_title'     => '',
		'club_away_title'     => '',
		'club_home_logo'      => '',
		'club_away_logo'      => '',
		'club_home_link'      => '',
		'club_away_link'      => '',
		'match_id'            => '',
		'finished'            => '',
		'home_goals'          => '',
		'away_goals'          => '',
		'extra'               => '',
		'aggtext'             => '',
		'permalink'           => '',
		'competition_logo'    => true,
		'outcome_id'          => '',
		'special_status'      => '',
		'extra_actions_html'  => '',
	]
);

$stadium_title = ( anwp_football_leagues()->customizer->get_value( 'match_list', 'match_slim_bottom_line[stadium]' ) && (int) $data->stadium_id ) ? anwp_football_leagues()->stadium->get_stadium_title( $data->stadium_id ) : '';
$competition   = anwp_football_leagues()->competition->get_competition( $data->competition_id );

// Wrapper classes
$render_competition = AnWP_Football_Leagues::string_to_bool( $data->competition_logo );
$render_match_time  = $data->show_match_datetime;

// Prepare bottom line options
$available_options   = [ 'referee', 'referee_assistants', 'referee_fourth' ];
$bottom_line_options = [];

foreach ( $available_options as $available_option ) {
	if ( anwp_football_leagues()->customizer->get_value( 'match_list', 'match_slim_bottom_line[' . $available_option . ']' ) ) {
		$bottom_line_options[] = $available_option;
	}
}

$bottom_line_ref_only = [ 'referee' ] === $bottom_line_options;

/**
 * Inject extra actions info match slim.
 * Hook: anwpfl/tmpl-match-small/extra_action
 *
 * @param object $data
 *
 * @since 0.10.3
 */
$data->extra_actions_html = apply_filters( 'anwpfl/tmpl-match-small/extra_action', $data->extra_actions_html, $data );
?>
<div class="anwp-fl-game match-list__item p-2 match-small anwp-border-light position-relative game-status-<?php echo absint( $data->finished ); ?>"
	data-anwp-match="<?php echo intval( $data->match_id ); ?>">

	<div class="match-small__top-bar d-flex flex-wrap align-items-start anwp-text-xs mb-1">
		<?php if ( $render_competition ) : ?>
			<div class="match-small__competition-wrapper d-flex mr-auto">
				<?php if ( $competition->logo ) : ?>
					<img loading="lazy" width="20" height="20" class="anwp-object-contain match-small__competition-logo anwp-flex-none anwp-w-20 anwp-h-20" data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $competition->title ); ?>"
						src="<?php echo esc_url( $competition->logo ); ?>" alt="<?php echo esc_attr( $competition->title ); ?>">
				<?php endif; ?>

				<div class="match-small__competition-title">
					<?php echo esc_html( $competition->title_full ); ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $data->kickoff && '0000-00-00 00:00:00' !== $data->kickoff ) : ?>
			<div class="match-small__date-wrapper d-flex align-items-center">
				<svg class="match-small__date-icon anwp-icon anwp-icon--feather anwp-icon--em-1-2">
					<use xlink:href="#icon-clock-alt"></use>
				</svg>
				<span class="match-small__date pl-1"><?php echo esc_html( $data->match_date ); ?></span>

				<?php if ( 'TBD' !== $data->special_status && $data->match_time ) : ?>
					<span class="match-small__time pl-1">- <?php echo esc_html( $data->match_time ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $data->outcome_id ) : ?>
			<div class="match-small__top-bar-outcome ml-auto">
				<?php echo anwp_football_leagues()->match->get_match_outcome_label( $data );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="match-small__main-content d-flex align-items-center my-2 my-sm-0">
		<div class="match-small__team-wrapper match-small__team-home anwp-flex-1 d-flex flex-column flex-column-reverse flex-sm-row justify-content-center justify-content-sm-end align-items-center">
			<div class="match-small__team-home-title anwp-text-base anwp-leading-1 anwp-font-semibold mx-4 mx-sm-0 anwp-text-center anwp-text-sm-right anwp-break-word">
				<?php echo esc_html( $data->club_home_title ); ?>
			</div>
			<?php if ( $data->club_home_logo ) : ?>
				<img loading="lazy" width="35" height="35" class="anwp-object-contain match-small__team-home-logo anwp-flex-none mx-3 my-2 my-sm-0 anwp-w-35 anwp-h-35"
					src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="<?php echo esc_attr( $data->club_home_title ); ?>">
			<?php endif; ?>
		</div>

		<div class="match-small__scores-wrapper d-flex anwp-text-lg">
			<span class="match-small__scores-number match-small__scores-home mr-1 match-small__scores-number-status-<?php echo (int) $data->finished; ?>">
				<?php echo (int) $data->finished ? (int) $data->home_goals : '-'; ?>
			</span>
			<span class="match-small__scores-number match-small__scores-away match-small__scores-number-status-<?php echo (int) $data->finished; ?>">
				<?php echo (int) $data->finished ? (int) $data->away_goals : '-'; ?>
			</span>
		</div>

		<div class="match-small__team-wrapper match-small__team-away anwp-flex-1 d-flex flex-column flex-sm-row align-items-center justify-content-center justify-content-sm-start">
			<?php if ( $data->club_away_logo ) : ?>
				<img loading="lazy" width="35" height="35" class="anwp-object-contain match-small__team-away-logo anwp-flex-none mx-3 my-2 my-sm-0 anwp-w-35 anwp-h-35"
					src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="<?php echo esc_attr( $data->club_away_title ); ?>">
			<?php endif; ?>
			<div class="match-small__team-away-title anwp-text-base anwp-leading-1 anwp-font-semibold mx-4 mx-sm-0 anwp-text-center anwp-text-sm-left anwp-break-word">
				<?php echo esc_html( $data->club_away_title ); ?>
			</div>
		</div>
	</div>

	<div class="match-small__footer d-sm-flex mt-2">
		<?php if ( $data->extra_actions_html ) : ?>
			<div class="anwp-flex-1 d-none d-sm-block">&nbsp;</div>
		<?php endif; ?>

		<div class="anwp-flex-auto mt-3 mt-sm-0">
			<?php if ( $bottom_line_ref_only && $stadium_title ) : ?>
				<div class="d-flex flex-wrap align-items-center justify-content-center match-small__stadium-referee">
			<?php endif; ?>

			<?php if ( $stadium_title ) : ?>
				<div class="match-small__stadium anwp-text-xs d-flex align-items-center justify-content-center mt-1">
					<svg class="anwp-icon anwp-icon--octi mr-2 anwp-w-20 anwp-fill-current">
						<use xlink:href="#icon-stadium"></use>
					</svg>
					<?php echo esc_html( $stadium_title ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $bottom_line_ref_only && get_post_meta( $data->match_id, '_anwpfl_referee', true ) ) : ?>
				<div class="match-small__referees anwp-text-xs d-flex align-items-center justify-content-center mt-1">
					<svg class="anwp-icon anwp-icon--octi mr-1 anwp-w-20 anwp-fill-current">
						<use xlink:href="#icon-whistle"></use>
					</svg>
					<?php echo esc_html( get_the_title( get_post_meta( $data->match_id, '_anwpfl_referee', true ) ) ? : '' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $bottom_line_ref_only && $stadium_title ) : ?>
				</div>
			<?php endif; ?>

			<?php
			/*
			|--------------------------------------------------------------------
			| Bottom Line
			|--------------------------------------------------------------------
			*/
			$bottom_line_html = '';

			if ( ! empty( $bottom_line_options ) && is_array( $bottom_line_options ) && ! $bottom_line_ref_only ) {

				// Referee
				if ( in_array( 'referee', $bottom_line_options, true ) ) {
					$referee_id   = get_post_meta( $data->match_id, '_anwpfl_referee', true );
					$referee_name = absint( $referee_id ) ? get_the_title( $referee_id ) : '';

					if ( $referee_name ) {
						$bottom_line_html .= '<span class="match-small__separated anwp-text-nowrap"><span class="match-small__separated-inner">';
						$bottom_line_html .= esc_html( AnWPFL_Text::get_value( 'match__match__referee', __( 'Referee', 'anwp-football-leagues' ) ) ) . ': </span>';
						$bottom_line_html .= esc_html( $referee_name ) . '</span>';
					}
				}

				// Referee Assistants
				if ( in_array( 'referee_assistants', $bottom_line_options, true ) ) {

					$assistant_1_id   = get_post_meta( $data->match_id, '_anwpfl_assistant_1', true );
					$assistant_1_name = absint( $assistant_1_id ) ? get_the_title( $assistant_1_id ) : '';

					if ( $assistant_1_name ) {
						$bottom_line_html .= '<span class="match-small__separated anwp-text-nowrap"><span class="match-small__separated-inner">';
						$bottom_line_html .= esc_html( AnWPFL_Text::get_value( 'match__referees__assistant', __( 'Assistant Referee', 'anwp-football-leagues' ) ) ) . ' 1: </span>';
						$bottom_line_html .= esc_html( $assistant_1_name ) . '</span>';
					}

					$assistant_2_id   = get_post_meta( $data->match_id, '_anwpfl_assistant_2', true );
					$assistant_2_name = absint( $assistant_2_id ) ? get_the_title( $assistant_2_id ) : '';

					if ( $assistant_2_name ) {
						$bottom_line_html .= '<span class="match-small__separated anwp-text-nowrap"><span class="match-small__separated-inner">';
						$bottom_line_html .= esc_html( AnWPFL_Text::get_value( 'match__referees__assistant', __( 'Assistant Referee', 'anwp-football-leagues' ) ) ) . ' 2: </span>';
						$bottom_line_html .= esc_html( $assistant_2_name ) . '</span>';
					}
				}

				// Fourth official
				if ( in_array( 'referee_fourth', $bottom_line_options, true ) ) {
					$referee_fourth_id   = get_post_meta( $data->match_id, '_anwpfl_referee_fourth', true );
					$referee_fourth_name = absint( $referee_fourth_id ) ? get_the_title( $referee_fourth_id ) : '';

					if ( $referee_fourth_name ) {
						$bottom_line_html .= '<span class="match-small__separated anwp-text-nowrap"><span class="match-small__separated-inner">';
						$bottom_line_html .= esc_html( AnWPFL_Text::get_value( 'match__referees__fourth_official', __( 'Fourth official', 'anwp-football-leagues' ) ) ) . ': </span>';
						$bottom_line_html .= esc_html( $referee_fourth_name ) . '</span>';
					}
				}

				if ( $bottom_line_html ) :
					?>
					<div class="match-small__referees anwp-text-center my-1 anwp-text-xs d-flex flex-wrap anwp-text-center justify-content-center">
						<?php echo $bottom_line_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<?php
				endif;
			}

			$time_result = '';

			switch ( intval( $data->extra ) ) {
				case 1:
					$time_result = esc_html( AnWPFL_Text::get_value( 'match__match__aet', _x( 'AET', 'Abbr: after extra time', 'anwp-football-leagues' ) ) );
					break;
				case 2:
					$time_result  = esc_html( AnWPFL_Text::get_value( 'match__match__penalties', _x( 'Penalties', 'on penalties', 'anwp-football-leagues' ) ) );
					$time_result .= ' ' . $data->home_goals_p . '-' . $data->away_goals_p;
					break;
			}

			if ( $time_result || $data->aggtext || in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) :
				?>
				<div class="match-small__bottom-special anwp-text-center my-1 anwp-text-sm anwp-leading-1-25">
					<?php
					if ( in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) {
						echo '<span class="match-small__separated anwp-text-nowrap">' . esc_html( anwp_football_leagues()->data->get_value_by_key( $data->special_status, 'special_status' ) ) . '</span>';
					}

					if ( $time_result ) {
						echo '<span class="match-small__separated anwp-text-nowrap">' . esc_html( $time_result ) . '</span>';
					}

					if ( $data->aggtext ) {
						echo '<span class="match-small__separated anwp-text-nowrap">' . esc_html( $data->aggtext ) . '</span>';
					}
					?>
				</div>
			<?php endif; ?>

			<?php
			/**
			 * Hook: anwpfl/tmpl-match-small/bottom
			 *
			 * @param object $data
			 */
			do_action( 'anwpfl/tmpl-match-small/bottom', $data );
			?>
		</div>

		<?php if ( $data->extra_actions_html ) : ?>
			<div class="anwp-flex-sm-1 d-flex flex-row align-items-end justify-content-end mt-3 mt-sm-0">
				<?php echo $data->extra_actions_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
	</div>

	<a class="anwp-link-cover anwp-link-without-effects anwp-cursor-pointer" href="<?php echo esc_url( $data->permalink ); ?>"></a>
</div>
