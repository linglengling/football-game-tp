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

$data_columns = [
	[
		'slug'  => 'match_id',
		'title' => 'Match ID',
		'attr'  => 'checked',
	],
	[
		'slug'  => 'match_external_id',
		'title' => 'Match External ID',
	],
	[
		'slug'  => 'player_id',
		'title' => __( 'Player ID', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'player_external_id',
		'title' => __( 'Player External ID', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'player_temp',
		'title' => __( 'Temporary Player', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'club_id',
		'title' => __( 'Club ID', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'club_external_id',
		'title' => __( 'Club External ID', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'starting',
		'title' => __( 'Starting XI', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'number',
		'title' => 'Number',
	],
	[
		'slug'  => 'is_captain',
		'title' => __( 'Is Captain', 'anwp-football-leagues' ) . '*',
	],
];
?>
<div class="my-3 p-2 border anwpfl-batch-import-filter-wrapper">
	<h5 class="my-1"><?php echo esc_html__( 'Columns order and visibility', 'anwp-football-leagues' ); ?> <a href="#" class="anwpfl-batch-import-update-settings ml-2"><?php echo esc_html__( 'apply new settings', 'anwp-football-leagues' ); ?></a></h5>

	<div class="anwp-overflow-x-auto">
		<div class="anwpfl-tools-sortable mt-2 d-flex">
			<?php foreach ( $data_columns as $column ) : ?>
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

	<p>* 1 - yes, 0 - no.</p>
	<p class="my-1">Recommended to import lineups on the last step because it has player statistics recalculation (goals, cards)</p>
</div>
<script>
	var anwpToolColumns = {
		'match_id': {
			type: 'numeric',
			title: 'Match ID'
		},
		'match_external_id': {
			type: 'text',
			title: 'Match External ID'
		},
		'player_id': {
			type: 'numeric',
			title: 'Player ID'
		},
		'player_external_id': {
			type: 'text',
			title: 'Player External ID'
		},
		'player_temp': {
			type: 'text',
			title: 'Temporary Player'
		},
		'club_id': {
			type: 'numeric',
			title: 'Club ID'
		},
		'club_external_id': {
			type: 'text',
			title: 'Club External ID'
		},
		'starting': {
			type: 'dropdown',
			title: 'Starting XI *',
			width: 120,
			source: [ '1', '0' ]
		},
		'number': {
			type: 'numeric',
			title: 'Number'
		},
		'is_captain': {
			type: 'dropdown',
			title: 'Is Captain *',
			width: 120,
			source: [ '1', '0' ]
		},
	};
</script>
