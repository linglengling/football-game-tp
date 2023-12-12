<?php
/**
 * The Template for displaying Player >> Stats Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/player/player-stats.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.8.3
 *
 * @version       0.14.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $data->current_season_id ) && 'all' === $data->current_season_id ) {
	anwp_football_leagues()->load_partial( $data, 'player/player-stats-all' );

	return;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'player_id'           => '',
		'current_season_id'   => '',
		'competition_matches' => '',
		'card_icons'          => '',
		'position_code'       => '',
		'header'              => true,
	]
);

$manual_stats = anwp_football_leagues()->player->get_manual_stats( $data->player_id, $data->current_season_id );

if ( empty( $manual_stats ) && empty( $data->competition_matches ) ) {
	return;
}

// Prepare wrapper classes
$col_span = 'g' === $data->position_code ? 9 : 10;
?>
<div class="player-stats anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'player__stats__stats_totals', __( 'Stats Totals', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<div class="player-stats__wrapper anwp-grid-table anwp-grid-table--aligned anwp-grid-table--bordered anwp-text-base anwp-border-light anwp-overflow-x-auto"
		style="--player-stats-cols: <?php echo absint( $col_span ); ?>;">

		<div class="anwp-grid-table__th" data-toggle="anwp-tooltip"
			data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__stats__played_matches', __( 'Played Matches', 'anwp-football-leagues' ) ) ); ?>">
			<svg class="anwp-icon--s20 anwp-icon--trans">
				<use xlink:href="#icon-field"></use>
			</svg>
		</div>
		<div class="anwp-grid-table__th" data-toggle="anwp-tooltip"
			data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__stats__started', __( 'Started', 'anwp-football-leagues' ) ) ); ?>">
			<svg class="anwp-icon--s20 anwp-icon--trans">
				<use xlink:href="#icon-field-shirt"></use>
			</svg>
		</div>
		<div class="anwp-grid-table__th" data-toggle="anwp-tooltip"
			data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__stats__substituted_in', __( 'Substituted In', 'anwp-football-leagues' ) ) ); ?>">
			<svg class="anwp-icon--s20 anwp-icon--trans">
				<use xlink:href="#icon-field-shirt-in"></use>
			</svg>
		</div>
		<div class="anwp-grid-table__th" data-toggle="anwp-tooltip"
			data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__stats__minutes', __( 'Minutes', 'anwp-football-leagues' ) ) ); ?>">
			<svg class="anwp-icon--s20 anwp-icon--gray-900">
				<use xlink:href="#icon-watch"></use>
			</svg>
		</div>
		<div class="anwp-grid-table__th">
			<?php echo $data->card_icons['y']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<div class="anwp-grid-table__th">
			<?php echo $data->card_icons['yr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<div class="anwp-grid-table__th">
			<?php echo $data->card_icons['r']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<?php if ( 'g' === $data->position_code ) : ?>
			<div class="anwp-grid-table__th" data-toggle="anwp-tooltip"
				data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__stats__goals_conceded', __( 'Goals Conceded', 'anwp-football-leagues' ) ) ); ?>">
				<svg class="icon__ball icon__ball--conceded">
					<use xlink:href="#icon-ball"></use>
				</svg>
			</div>
			<div class="anwp-grid-table__th" data-toggle="anwp-tooltip"
				data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__stats__clean_sheets', __( 'Clean Sheets', 'anwp-football-leagues' ) ) ); ?>">
				<svg class="icon__ball">
					<use xlink:href="#icon-ball_canceled"></use>
				</svg>
			</div>
		<?php else : ?>
			<div class="anwp-grid-table__th" data-toggle="anwp-tooltip"
				data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__stats__goals_from_penalty', __( 'Goals (from penalty)', 'anwp-football-leagues' ) ) ); ?>">
				<svg class="icon__ball anwp-icon--stats-goal">
					<use xlink:href="#icon-ball"></use>
				</svg>
			</div>
			<div class="anwp-grid-table__th" data-toggle="anwp-tooltip"
				data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__stats__assists', __( 'Assists', 'anwp-football-leagues' ) ) ); ?>">
				<svg class="icon__ball anwp-opacity-50">
					<use xlink:href="#icon-ball"></use>
				</svg>
			</div>
			<div class="anwp-grid-table__th" data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_html( AnWPFL_Text::get_value( 'player__stats__own_goals', __( 'Own Goals', 'anwp-football-leagues' ) ) ); ?>">
				<svg class="icon__ball icon__ball--own">
					<use xlink:href="#icon-ball"></use>
				</svg>
			</div>
		<?php endif; ?>

		<?php foreach ( $data->competition_matches as $competition ) : ?>
			<div class="anwp-grid-table__td player-stats__competition anwp-bg-light anwp-text-sm">
				<span><?php echo esc_html( $competition['title'] ); ?></span>
			</div>
			<div class="anwp-grid-table__td player-stats__played">
				<?php echo (int) ( $competition['totals']['started'] + $competition['totals']['sub_in'] ); ?>
			</div>
			<div class="anwp-grid-table__td player-stats__started">
				<?php echo (int) $competition['totals']['started']; ?>
			</div>
			<div class="anwp-grid-table__td player-stats__sub_in">
				<?php echo (int) $competition['totals']['sub_in']; ?>
			</div>
			<div class="anwp-grid-table__td player-stats__minutes">
				<?php echo (int) $competition['totals']['minutes']; ?>′
			</div>
			<div class="anwp-grid-table__td player-stats__card_y">
				<?php echo (int) $competition['totals']['card_y']; ?>
			</div>
			<div class="anwp-grid-table__td player-stats__card_yr">
				<?php echo (int) $competition['totals']['card_yr']; ?>
			</div>
			<div class="anwp-grid-table__td player-stats__card_r">
				<?php echo (int) $competition['totals']['card_r']; ?>
			</div>

			<?php if ( 'g' === $data->position_code ) : ?>
				<div class="anwp-grid-table__td player-stats__goals_conceded">
					<?php echo (int) $competition['totals']['goals_conceded']; ?>
				</div>
				<div class="anwp-grid-table__td player-stats__clean_sheets">
					<?php echo (int) $competition['totals']['clean_sheets']; ?>
				</div>
			<?php else : ?>
				<div class="anwp-grid-table__td player-stats__goals">
					<?php echo (int) $competition['totals']['goals']; ?> (<?php echo (int) $competition['totals']['goals_penalty']; ?>)
				</div>
				<div class="anwp-grid-table__td player-stats__assist">
					<?php echo (int) $competition['totals']['assist']; ?>
				</div>
				<div class="anwp-grid-table__td player-stats__goals_own">
					<?php echo (int) $competition['totals']['goals_own']; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>

		<?php foreach ( $manual_stats as $manual_stat ) : ?>

			<div class="anwp-grid-table__td player-stats__competition anwp-bg-light anwp-text-sm">
				<span>
					<?php
					if ( 'new' === $manual_stat->competition_type ) {
						echo esc_html( $manual_stat->competition_text );
					} elseif ( 'id' === $manual_stat->competition_type ) {
						echo esc_html( anwp_football_leagues()->competition->get_competition( $manual_stat->competition_id )->title );
					}
					?>
				</span>
			</div>
			<div class="anwp-grid-table__td player-stats__played">
				<?php echo (int) $manual_stat->started + (int) $manual_stat->sub_in; ?>
			</div>
			<div class="anwp-grid-table__td player-stats__started">
				<?php echo (int) $manual_stat->started; ?>
			</div>
			<div class="anwp-grid-table__td player-stats__sub_in">
				<?php echo (int) $manual_stat->sub_in; ?>
			</div>
			<div class="anwp-grid-table__td player-stats__minutes">
				<?php echo (int) $manual_stat->minutes; ?>′
			</div>
			<div class="anwp-grid-table__td player-stats__card_y">
				<?php echo (int) $manual_stat->card_y; ?>
			</div>
			<div class="anwp-grid-table__td player-stats__card_yr">
				<?php echo (int) $manual_stat->card_yr; ?>
			</div>
			<div class="anwp-grid-table__td player-stats__card_r">
				<?php echo (int) $manual_stat->card_r; ?>
			</div>

			<?php if ( 'g' === $data->position_code ) : ?>
				<div class="anwp-grid-table__td player-stats__goals_conceded">
					<?php echo (int) $manual_stat->goals_conceded; ?>
				</div>
				<div class="anwp-grid-table__td player-stats__clean_sheets">
					<?php echo (int) $manual_stat->clean_sheets; ?>
				</div>
			<?php else : ?>
				<div class="anwp-grid-table__td player-stats__goals">
					<?php echo (int) $manual_stat->goals; ?> (<?php echo (int) $manual_stat->goals_penalty; ?>)
				</div>
				<div class="anwp-grid-table__td player-stats__assist">
					<?php echo (int) $manual_stat->assists; ?>
				</div>
				<div class="anwp-grid-table__td player-stats__goals_own">
					<?php echo (int) $manual_stat->own_goals; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>

		<?php
		if ( count( $data->competition_matches ) > 1 || $manual_stats ) :
			/*
			|--------------------------------------------------------------------
			| Prepare and calculate totals
			|--------------------------------------------------------------------
			*/
			$stat_totals = [
				'started'        => 0,
				'sub_in'         => 0,
				'minutes'        => 0,
				'card_y'         => 0,
				'card_yr'        => 0,
				'card_r'         => 0,
				'goals_conceded' => 0,
				'clean_sheets'   => 0,
				'goals'          => 0,
				'goals_penalty'  => 0,
				'assist'         => 0,
				'goals_own'      => 0,
			];

			if ( count( $data->competition_matches ) ) :
				foreach ( $data->competition_matches as $t_competition ) :
					$stat_totals['started']        += $t_competition['totals']['started'];
					$stat_totals['sub_in']         += $t_competition['totals']['sub_in'];
					$stat_totals['minutes']        += $t_competition['totals']['minutes'];
					$stat_totals['card_y']         += $t_competition['totals']['card_y'];
					$stat_totals['card_yr']        += $t_competition['totals']['card_yr'];
					$stat_totals['card_r']         += $t_competition['totals']['card_r'];
					$stat_totals['goals_conceded'] += $t_competition['totals']['goals_conceded'];
					$stat_totals['clean_sheets']   += $t_competition['totals']['clean_sheets'];
					$stat_totals['goals']          += $t_competition['totals']['goals'];
					$stat_totals['goals_penalty']  += $t_competition['totals']['goals_penalty'];
					$stat_totals['assist']         += $t_competition['totals']['assist'];
					$stat_totals['goals_own']      += $t_competition['totals']['goals_own'];
				endforeach;
			endif;

			if ( count( $manual_stats ) ) :
				foreach ( $manual_stats as $manual_stat ) :
					$stat_totals['started']        += absint( $manual_stat->started );
					$stat_totals['sub_in']         += absint( $manual_stat->sub_in );
					$stat_totals['minutes']        += absint( $manual_stat->minutes );
					$stat_totals['card_y']         += absint( $manual_stat->card_y );
					$stat_totals['card_yr']        += absint( $manual_stat->card_yr );
					$stat_totals['card_r']         += absint( $manual_stat->card_r );
					$stat_totals['goals_conceded'] += absint( $manual_stat->goals_conceded );
					$stat_totals['clean_sheets']   += absint( $manual_stat->clean_sheets );
					$stat_totals['goals']          += absint( $manual_stat->goals );
					$stat_totals['goals_penalty']  += absint( $manual_stat->goals_penalty );
					$stat_totals['assist']         += absint( $manual_stat->assists );
					$stat_totals['goals_own']      += absint( $manual_stat->own_goals );
				endforeach;
			endif;
			?>
			<div class="anwp-grid-table__td player-stats__competition_totals anwp-bg-light anwp-text-sm">
				<?php echo esc_html( AnWPFL_Text::get_value( 'player__stats__totals', __( 'Totals', 'anwp-football-leagues' ) ) ); ?>:
			</div>
			<div class="player-stats__totals anwp-grid-table__td player-stats__played">
				<?php echo (int) ( $stat_totals['started'] + $stat_totals['sub_in'] ); ?>
			</div>
			<div class="player-stats__totals anwp-grid-table__td player-stats__started">
				<?php echo (int) $stat_totals['started']; ?>
			</div>
			<div class="player-stats__totals anwp-grid-table__td player-stats__sub_in">
				<?php echo (int) $stat_totals['sub_in']; ?>
			</div>
			<div class="player-stats__totals anwp-grid-table__td player-stats__minutes">
				<?php echo (int) $stat_totals['minutes']; ?>′
			</div>
			<div class="player-stats__totals anwp-grid-table__td player-stats__card_y">
				<?php echo (int) $stat_totals['card_y']; ?>
			</div>
			<div class="player-stats__totals anwp-grid-table__td player-stats__card_yr">
				<?php echo (int) $stat_totals['card_yr']; ?>
			</div>
			<div class="player-stats__totals anwp-grid-table__td player-stats__card_r">
				<?php echo (int) $stat_totals['card_r']; ?>
			</div>

			<?php if ( 'g' === $data->position_code ) : ?>
				<div class="player-stats__totals anwp-grid-table__td player-stats__goals_conceded">
					<?php echo (int) $stat_totals['goals_conceded']; ?>
				</div>
				<div class="player-stats__totals anwp-grid-table__td player-stats__clean_sheets">
					<?php echo (int) $stat_totals['clean_sheets']; ?>
				</div>
			<?php else : ?>
				<div class="player-stats__totals anwp-grid-table__td player-stats__goals">
					<?php echo (int) $stat_totals['goals']; ?> (<?php echo (int) $stat_totals['goals_penalty']; ?>)
				</div>
				<div class="player-stats__totals anwp-grid-table__td player-stats__assist">
					<?php echo (int) $stat_totals['assist']; ?>
				</div>
				<div class="player-stats__totals anwp-grid-table__td player-stats__goals_own">
					<?php echo (int) $stat_totals['goals_own']; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
