<?php
/**
 * The Template for displaying Match >> Stats Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.9.0
 *
 * @version       0.14.4
 */
// phpcs:disable WordPress.NamingConventions.ValidVariableName

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'kickoff'         => '',
		'match_date'      => '',
		'match_time'      => '',
		'home_club'       => '',
		'away_club'       => '',
		'club_home_title' => '',
		'club_away_title' => '',
		'club_home_link'  => '',
		'club_away_link'  => '',
		'club_home_logo'  => '',
		'club_away_logo'  => '',
		'match_id'        => '',
		'season_id'       => '',
		'finished'        => '',
		'home_goals'      => '',
		'away_goals'      => '',
		'match_week'      => '',
		'stadium_id'      => '',
		'competition_id'  => '',
		'main_stage_id'   => '',
		'stage_title'     => '',
		'events'          => [],
		'stats'           => [],
		'line_up_home'    => '',
		'line_up_away'    => '',
		'subs_home'       => '',
		'subs_away'       => '',
		'header'          => true,
	]
);

$stats = $data->stats;

if ( empty( $stats ) ) {
	return '';
}

$color_home = get_post_meta( $data->home_club, '_anwpfl_main_color', true );
$color_away = get_post_meta( $data->away_club, '_anwpfl_main_color', true );

if ( empty( $color_home ) ) {
	$color_home = '#0085ba';
}

if ( empty( $color_away ) ) {
	$color_away = '#dc3545';
}

/**
 * Hook: anwpfl/tmpl-match/stats_before
 *
 * @param object $data Match data
 *
 * @since 0.7.5
 */
do_action( 'anwpfl/tmpl-match/stats_before', $data );

$stats_array = [
	[
		'stat'  => 'shots',
		'h'     => 'shotsH',
		'a'     => 'shotsA',
		'text'  => AnWPFL_Text::get_value( 'match__stats__shots', __( 'Shots', 'anwp-football-leagues' ) ),
		'multi' => 2,
	],
	[
		'stat'  => 'shotsOnGoals',
		'h'     => 'shotsOnGoalsH',
		'a'     => 'shotsOnGoalsA',
		'text'  => AnWPFL_Text::get_value( 'match__stats__shots_on_target', __( 'Shots on Target', 'anwp-football-leagues' ) ),
		'multi' => 2,
	],
	[
		'stat'  => 'fouls',
		'h'     => 'foulsH',
		'a'     => 'foulsA',
		'text'  => AnWPFL_Text::get_value( 'match__stats__fouls', __( 'Fouls', 'anwp-football-leagues' ) ),
		'multi' => 2,
	],
	[
		'stat'  => 'corners',
		'h'     => 'cornersH',
		'a'     => 'cornersA',
		'text'  => AnWPFL_Text::get_value( 'match__stats__corners', __( 'Corners', 'anwp-football-leagues' ) ),
		'multi' => 4,
	],
	[
		'stat'  => 'offsides',
		'h'     => 'offsidesH',
		'a'     => 'offsidesA',
		'text'  => AnWPFL_Text::get_value( 'match__stats__offsides', __( 'Offsides', 'anwp-football-leagues' ) ),
		'multi' => 4,
	],
	[
		'stat'  => 'possession',
		'h'     => 'possessionH',
		'a'     => 'possessionA',
		'text'  => AnWPFL_Text::get_value( 'match__stats__ball_possession', __( 'Ball Possession', 'anwp-football-leagues' ) ),
		'multi' => 1,
	],
	[
		'stat'  => 'yellowCards',
		'h'     => 'yellowCardsH',
		'a'     => 'yellowCardsA',
		'text'  => AnWPFL_Text::get_value( 'match__stats__yellow_cards', __( 'Yellow Cards', 'anwp-football-leagues' ) ),
		'multi' => 10,
	],
	[
		'stat'  => 'yellow2RCards',
		'h'     => 'yellow2RCardsH',
		'a'     => 'yellow2RCardsA',
		'text'  => AnWPFL_Text::get_value( 'match__stats__2_d_yellow_red_cards', __( '2d Yellow > Red Cards', 'anwp-football-leagues' ) ),
		'multi' => 10,
	],
	[
		'stat'  => 'redCards',
		'h'     => 'redCardsH',
		'a'     => 'redCardsA',
		'text'  => AnWPFL_Text::get_value( 'match__stats__red_cards', __( 'Red Cards', 'anwp-football-leagues' ) ),
		'multi' => 10,
	],
];

ob_start();

foreach ( $stats_array as $stats_value ) :
	if ( isset( $stats->{$stats_value['h']} ) && isset( $stats->{$stats_value['a']} ) && ( '' !== $stats->{$stats_value['h']} || '' !== $stats->{$stats_value['a']} ) ) :
		?>
		<div class="match-stats__stat-wrapper anwp-fl-border-bottom anwp-border-light p-2 club-stats__<?php echo esc_attr( $stats_value['stat'] ); ?>">
			<div class="match-stats__stat-name anwp-text-center anwp-text-base"><?php echo esc_html( $stats_value['text'] ); ?></div>
			<div class="d-flex mt-1 match-stats__stat-row">
				<div class="match-stats__stat-value anwp-flex-none match__stats-number mx-1 anwp-text-base"><?php echo (int) $stats->{$stats_value['h']}; ?></div>
				<div class="anwp-flex-1 mx-1">
					<div class="match-stats__stat-bar d-flex anwp-overflow-hidden anwp-h-20 flex-row-reverse">
						<div class="match-stats__stat-bar-inner" style="width: <?php echo (int) $stats->{$stats_value['h']} * $stats_value['multi']; ?>%; background-color: <?php echo esc_attr( $color_home ); ?>"></div>
					</div>
				</div>
				<div class="anwp-flex-1 mx-1">
					<div class="match-stats__stat-bar d-flex anwp-overflow-hidden anwp-h-20">
						<div class="match-stats__stat-bar-inner" style="width: <?php echo (int) $stats->{$stats_value['a']} * $stats_value['multi']; ?>%; background-color: <?php echo esc_attr( $color_away ); ?>"></div>
					</div>
				</div>
				<div class="match-stats__stat-value anwp-flex-none match__stats-number mx-1 anwp-text-base"><?php echo (int) $stats->{$stats_value['a']}; ?></div>
			</div>
		</div>
		<?php
	endif;
endforeach;

$stats_output = ob_get_clean();

if ( empty( $stats_output ) ) {
	return '';
}
?>
<div class="anwp-section match-stats">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__stats__match_statistics', __( 'Match Statistics', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<div class="d-sm-flex match-stats__teams">
		<div class="anwp-flex-1 pr-2">
			<?php
			anwp_football_leagues()->load_partial(
				[
					'club_id' => $data->home_club,
					'class'   => 'mb-1',
				],
				'club/club-title'
			);
			?>
		</div>
		<div class="anwp-flex-1 pl-2">
			<?php
			anwp_football_leagues()->load_partial(
				[
					'club_id' => $data->away_club,
					'class'   => 'mb-1',
					'is_home' => false,
				],
				'club/club-title'
			);
			?>
		</div>
	</div>

	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $stats_output;
	?>
</div>
