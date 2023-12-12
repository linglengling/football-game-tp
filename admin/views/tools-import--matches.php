<?php
/**
 * Tools page for AnWP Football Leagues
 *
 * @link       https://anwp.pro
 * @since      0.14.2
 *
 * @package    AnWP_Football_Leagues
 * @subpackage AnWP_Football_Leagues/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'anwp-football-leagues' ) );
}

$columns_matches = [
	[
		'slug'  => 'competition_id',
		'title' => __( 'Competition ID', 'anwp-football-leagues' ) . ' *',
		'attr'  => 'disabled checked',
	],
	[
		'slug'  => 'round',
		'title' => __( 'Round ID', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'club_home_id',
		'title' => __( 'Club Home ID', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'club_home_external_id',
		'title' => __( 'Club Home External ID', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'club_away_id',
		'title' => __( 'Club Away ID', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'club_away_external_id',
		'title' => __( 'Club Away External ID', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'kickoff',
		'title' => __( 'Kickoff (YYYY-MM-DD HH:MM)', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'matchweek',
		'title' => __( 'MatchWeek', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'goals_h',
		'title' => 'Goals - Home',
		'attr'  => 'checked',
	],
	[
		'slug'  => 'goals_a',
		'title' => 'Goals - Away',
		'attr'  => 'checked',
	],
	[
		'slug'  => 'ht_goals_h',
		'title' => 'HalfTime Goals - Home',
	],
	[
		'slug'  => 'ht_goals_a',
		'title' => 'HalfTime Goals - Away',
	],
	[
		'slug'  => 'ft_goals_h',
		'title' => 'FullTime Goals - Home',
	],
	[
		'slug'  => 'ft_goals_a',
		'title' => 'FullTime Goals - Away',
	],
	[
		'slug'  => 'extra_goals_h',
		'title' => 'Extra Goals - Home',
	],
	[
		'slug'  => 'extra_goals_a',
		'title' => 'Extra Goals - Away',
	],
	[
		'slug'  => 'pen_goals_h',
		'title' => 'Penalty Goals - Home',
	],
	[
		'slug'  => 'pen_goals_a',
		'title' => 'Penalty Goals - Away',
	],
	[
		'slug'  => 'agg_text',
		'title' => 'Aggregate Text',
	],
	[
		'slug'  => 'stadium_id',
		'title' => 'Stadium ID',
	],
	[
		'slug'  => 'stadium_external_id',
		'title' => 'Stadium External ID',
	],
	[
		'slug'  => 'attendance',
		'title' => 'Attendance',
	],
	[
		'slug'  => 'referee_id',
		'title' => 'Referee ID',
	],
	[
		'slug'  => 'referee_external_id',
		'title' => 'Referee External ID',
	],
	[
		'slug'  => 'assistant_1_id',
		'title' => 'Ref Assistant 1 ID',
	],
	[
		'slug'  => 'assistant_1_external_id',
		'title' => 'Ref Assistant 1 External ID',
	],
	[
		'slug'  => 'assistant_2_id',
		'title' => 'Ref Assistant 2 ID',
	],
	[
		'slug'  => 'assistant_2_external_id',
		'title' => 'Ref Assistant 2 External ID',
	],
	[
		'slug'  => 'referee_fourth_id',
		'title' => 'Fourth Official ID',
	],
	[
		'slug'  => 'referee_fourth_external_id',
		'title' => 'Fourth Official External ID',
	],
	[
		'slug'  => 'match_summary',
		'title' => 'Match Summary',
	],
	[
		'slug'  => 'match_external_id',
		'title' => 'Match External ID',
	],
];
?>
<div class="my-3 p-2 border anwpfl-batch-import-filter-wrapper">
	<h5 class="my-1"><?php echo esc_html__( 'Columns order and visibility', 'anwp-football-leagues' ); ?> <a href="#" class="anwpfl-batch-import-update-settings ml-2"><?php echo esc_html__( 'apply new settings', 'anwp-football-leagues' ); ?></a></h5>

	<div class="anwp-overflow-x-auto">
		<div class="anwpfl-tools-sortable mt-2 d-flex">
			<?php foreach ( $columns_matches as $column ) : ?>
				<div class="my-1 mr-1 py-1 px-2 border border-secondary anwp-d-flex-not-important flex-column align-items-center">
					<svg class="anwp-icon anwp-icon--s24 anwp-icon--octi anwp-drag-handler">
						<use xlink:href="#icon-grabber"></use>
					</svg>
					<div class="my-2" style="writing-mode: vertical-rl;">
						<?php echo esc_html( $column['title'] ); ?>
					</div>
					<label data-slug="<?php echo esc_attr( $column['slug'] ); ?>" class="mt-auto anwp-cursor-pointer">
						<input class="d-none" type="checkbox" <?php echo esc_attr( ! empty( $column['attr'] ) ? $column['attr'] : '' ); ?>>
						<svg class="anwp-icon anwp-icon--s24 anwp-icon--octi anwp-checkbox-icon--checked">
							<use xlink:href="#icon-eye"></use>
						</svg>
						<svg class="anwp-icon anwp-icon--s24 anwp-icon--feather anwp-checkbox-icon--unchecked">
							<use xlink:href="#icon-eye-off"></use>
						</svg>
					</label>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<p>*Before starting data import you should create Competition with proper groups/ties structure (with teams).</p>
</div>
<script>
	var anwpImportOptions = <?php echo wp_json_encode( anwp_football_leagues()->data->get_import_options() ); ?>;
	var anwpToolColumns = {
		'competition_id': {
			type: 'numeric',
			title: 'Competition ID'
		},
		'round': {
			type: 'text',
			title: 'Round ID'
		},
		'club_home_id': {
			type: 'numeric',
			title: 'Club Home ID'
		},
		'club_home_external_id': {
			type: 'numeric',
			title: 'Club Home External ID'
		},
		'club_away_id': {
			type: 'numeric',
			title: 'Club Away ID'
		},
		'club_away_external_id': {
			type: 'numeric',
			title: 'Club Away External ID'
		},
		'kickoff': {
			type: 'numeric',
			title: 'Kickoff (YYYY-MM-DD HH:MM)',
			width: 140,
			mask: 'yyyy-mm-dd hh24:mi'
		},
		'matchweek': {
			type: 'numeric',
			title: 'MatchWeek'
		},
		'goals_h': {
			type: 'numeric',
			title: 'Goals - H'
		},
		'goals_a': {
			type: 'numeric',
			title: 'Goals - A'
		},
		'ht_goals_h': {
			type: 'numeric',
			title: 'HT Goals - H'
		},
		'ht_goals_a': {
			type: 'numeric',
			title: 'HT Goals - A'
		},
		'ft_goals_h': {
			type: 'numeric',
			title: 'FT Goals - H'
		},
		'ft_goals_a': {
			type: 'numeric',
			title: 'FT Goals - A'
		},
		'extra_goals_h': {
			type: 'numeric',
			title: 'Extra Goals - H'
		},
		'extra_goals_a': {
			type: 'numeric',
			title: 'Extra Goals - A'
		},
		'pen_goals_h': {
			type: 'numeric',
			title: 'Pen Goals - H'
		},
		'pen_goals_a': {
			type: 'numeric',
			title: 'Pen Goals - A'
		},
		'agg_text': {
			type: 'text',
			title: 'Aggregate Text'
		},
		'stadium_id': {
			type: 'numeric',
			title: 'Stadium ID'
		},
		'stadium_external_id': {
			type: 'numeric',
			title: 'Stadium External ID'
		},
		'attendance': {
			type: 'text',
			title: 'Attendance'
		},
		'referee_id': {
			type: 'numeric',
			title: 'Ref ID'
		},
		'referee_external_id': {
			type: 'numeric',
			title: 'Ref External ID'
		},
		'assistant_1_id': {
			type: 'numeric',
			title: 'Ref Assistant 1 ID'
		},
		'assistant_1_external_id': {
			type: 'numeric',
			title: 'Ref Assistant 1 External ID'
		},
		'assistant_2_id': {
			type: 'numeric',
			title: 'Ref Assistant 2 ID'
		},
		'assistant_2_external_id': {
			type: 'numeric',
			title: 'Ref Assistant 2 External ID'
		},
		'referee_fourth_id': {
			type: 'numeric',
			title: 'Fourth Official ID'
		},
		'referee_fourth_external_id': {
			type: 'numeric',
			title: 'Fourth Official External ID'
		},
		'match_summary': {
			type: 'text',
			title: 'Match Summary'
		},
		'match_external_id': {
			type: 'numeric',
			title: 'Match External ID'
		},
	};
</script>
