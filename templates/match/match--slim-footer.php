<?php
/**
 * The Template for displaying Match (slim version).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match--slim.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.6.1
 *
 * @version       0.14.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'show_match_datetime' => true,
		'kickoff'             => '',
		'kickoff_c'           => '',
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
		'datetime_tz'         => true,
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
 * Hook: anwpfl/tmpl-match-slim/extra_action
 *
 * @param object $data
 *
 * @since 0.10.3
 */
$data->extra_actions_html = apply_filters( 'anwpfl/tmpl-match-slim/extra_action', $data->extra_actions_html, $data );

ob_start();
/**
 * Hook: anwpfl/tmpl-match-slim/bottom
 *
 * @param object $data
 */
do_action( 'anwpfl/tmpl-match-slim/bottom', $data );

$match_slim_bottom_hook = ob_get_clean();

$main_ref_id = anwp_football_leagues()->referee->get_game_referee_main( $data->match_id );

$bottom_line_html = '';

if ( ! empty( $bottom_line_options ) && is_array( $bottom_line_options ) && ! $bottom_line_ref_only ) {

	// Referee
	if ( in_array( 'referee', $bottom_line_options, true ) ) {
		$referee_id   = get_post_meta( $data->match_id, '_anwpfl_referee', true );
		$referee_name = absint( $referee_id ) ? get_the_title( $referee_id ) : '';

		if ( $referee_name ) {
			$bottom_line_html .= '<span class="match-slim__separated anwp-text-nowrap"><span class="match-slim__separated-inner">';
			$bottom_line_html .= esc_html( AnWPFL_Text::get_value( 'match__match__referee', __( 'Referee', 'anwp-football-leagues' ) ) ) . ': </span>';
			$bottom_line_html .= esc_html( $referee_name ) . '</span>';
		}
	}

	// Referee Assistants
	if ( in_array( 'referee_assistants', $bottom_line_options, true ) ) {

		$assistant_1_id   = get_post_meta( $data->match_id, '_anwpfl_assistant_1', true );
		$assistant_1_name = absint( $assistant_1_id ) ? get_the_title( $assistant_1_id ) : '';

		if ( $assistant_1_name ) {
			$bottom_line_html .= '<span class="match-slim__separated anwp-text-nowrap"><span class="match-slim__separated-inner">';
			$bottom_line_html .= esc_html( AnWPFL_Text::get_value( 'match__referees__assistant', __( 'Assistant Referee', 'anwp-football-leagues' ) ) ) . ' 1: </span>';
			$bottom_line_html .= esc_html( $assistant_1_name ) . '</span>';
		}

		$assistant_2_id   = get_post_meta( $data->match_id, '_anwpfl_assistant_2', true );
		$assistant_2_name = absint( $assistant_2_id ) ? get_the_title( $assistant_2_id ) : '';

		if ( $assistant_2_name ) {
			$bottom_line_html .= '<span class="match-slim__separated anwp-text-nowrap"><span class="match-slim__separated-inner">';
			$bottom_line_html .= esc_html( AnWPFL_Text::get_value( 'match__referees__assistant', __( 'Assistant Referee', 'anwp-football-leagues' ) ) ) . ' 2: </span>';
			$bottom_line_html .= esc_html( $assistant_2_name ) . '</span>';
		}
	}

	// Fourth official
	if ( in_array( 'referee_fourth', $bottom_line_options, true ) ) {
		$referee_fourth_id   = get_post_meta( $data->match_id, '_anwpfl_referee_fourth', true );
		$referee_fourth_name = absint( $referee_fourth_id ) ? get_the_title( $referee_fourth_id ) : '';

		if ( $referee_fourth_name ) {
			$bottom_line_html .= '<span class="match-slim__separated anwp-text-nowrap"><span class="match-slim__separated-inner">';
			$bottom_line_html .= esc_html( AnWPFL_Text::get_value( 'match__referees__fourth_official', __( 'Fourth official', 'anwp-football-leagues' ) ) ) . ': </span>';
			$bottom_line_html .= esc_html( $referee_fourth_name ) . '</span>';
		}
	}
}

$time_result = '';

switch ( intval( $data->extra ) ) {
	case 1:
		$time_result = esc_html( AnWPFL_Text::get_value( 'match__match__aet', _x( 'AET', 'Abbr: after extra time', 'anwp-football-leagues' ) ) );
		break;
	case 2:
		$time_result = esc_html( AnWPFL_Text::get_value( 'match__match__penalties', _x( 'Penalties', 'on penalties', 'anwp-football-leagues' ) ) );
		$time_result .= ' ' . $data->home_goals_p . '-' . $data->away_goals_p;
		break;
}

if ( empty( $data->extra_actions_html ) && empty( $data->outcome_id ) && empty( $stadium_title ) && empty( $bottom_line_ref_only && $main_ref_id ) && empty( $match_slim_bottom_hook ) && empty( $bottom_line_html ) && empty( $time_result ) && empty( $data->aggtext ) && ! in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) {
	return;
}
?>
<div class="match-slim__footer d-sm-flex mt-1 mt-sm-0 anwp-opacity-80 anwp-leading-1">
	<?php if ( $data->extra_actions_html || $data->outcome_id ) : ?>
		<div class="anwp-flex-1 d-none d-sm-block">&nbsp;</div>
	<?php endif; ?>

	<div class="anwp-flex-auto mt-1 mt-sm-0">
		<?php if ( $bottom_line_ref_only && $stadium_title ) : ?>
			<div class="d-flex flex-wrap align-items-center justify-content-center match-slim__stadium-referee">
		<?php endif; ?>

		<?php if ( $stadium_title ) : ?>
			<div class="match-slim__stadium anwp-text-xs d-flex align-items-center justify-content-center mt-1">
				<svg class="anwp-icon anwp-icon--octi mr-2 anwp-w-20 anwp-fill-current">
					<use xlink:href="#icon-stadium"></use>
				</svg>
				<?php echo esc_html( $stadium_title ); ?>
			</div>
		<?php endif; ?>

		<?php
		if ( $bottom_line_ref_only && $main_ref_id ) :
			$referee_obj = anwp_football_leagues()->referee->get_referee( $main_ref_id );
			?>
			<div class="match-slim__referees anwp-text-xs d-flex align-items-center justify-content-center mt-1">
				<svg class="anwp-icon anwp-icon--octi mr-1 anwp-w-20 anwp-fill-current">
					<use xlink:href="#icon-whistle"></use>
				</svg>
				<?php echo esc_html( $referee_obj ? $referee_obj->name : '' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $bottom_line_ref_only && $stadium_title ) : ?>
			</div>
		<?php endif; ?>

		<?php if ( $bottom_line_html ) : ?>
			<div class="match-slim__referees anwp-text-center mt-1 anwp-text-xs d-flex flex-wrap anwp-text-center justify-content-center">
				<?php echo $bottom_line_html; // phpcs:ignore ?>
			</div>
			<?php
		endif;

		if ( $time_result || $data->aggtext || in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) :
			?>
			<div class="match-slim__bottom-special anwp-text-center mt-1 anwp-text-sm anwp-leading-1-25">
				<?php
				if ( in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) {
					echo '<span class="match-slim__separated anwp-text-nowrap">' . esc_html( anwp_football_leagues()->data->get_value_by_key( $data->special_status, 'special_status' ) ) . '</span>';
				}

				if ( $time_result ) {
					echo '<span class="match-slim__separated anwp-text-nowrap">' . esc_html( $time_result ) . '</span>';
				}

				if ( $data->aggtext ) {
					echo '<span class="match-slim__separated anwp-text-nowrap">' . esc_html( $data->aggtext ) . '</span>';
				}
				?>
			</div>
		<?php endif; ?>

		<?php echo $match_slim_bottom_hook; // phpcs:ignore ?>
	</div>

	<?php if ( $data->extra_actions_html || $data->outcome_id ) : ?>
		<div class="anwp-flex-sm-1 d-flex flex-row align-items-end justify-content-end mt-1 mt-sm-0">
			<?php echo $data->extra_actions_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<?php if ( $data->outcome_id ) : ?>
				<div class="match-slim__outcome anwp-leading-1">
					<?php echo anwp_football_leagues()->match->get_match_outcome_label( $data );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
