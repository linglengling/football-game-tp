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
		'title' => __( 'Player In ID', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'player_external_id',
		'title' => __( 'Player In External ID', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'player_temp',
		'title' => __( 'Temporary Player In', 'anwp-football-leagues' ) . '*',
	],
	[
		'slug'  => 'player_out_id',
		'title' => __( 'Player Out ID', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'player_out_external_id',
		'title' => __( 'Player Out External ID', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'player_out_temp',
		'title' => __( 'Temporary Player Out', 'anwp-football-leagues' ) . '*',
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
		'slug'  => 'minute',
		'title' => __( 'Minute', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'minute_add',
		'title' => __( 'Minute Additional', 'anwp-football-leagues' ),
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
			title: 'Player In ID'
		},
		'player_external_id': {
			type: 'text',
			title: 'Player In External ID'
		},
		'player_temp': {
			type: 'text',
			title: 'Temporary Player In'
		},
		'player_out_id': {
			type: 'numeric',
			title: 'Player Out ID'
		},
		'player_out_external_id': {
			type: 'text',
			title: 'Player Out External ID'
		},
		'player_out_temp': {
			type: 'text',
			title: 'Temporary Player Out'
		},
		'club_id': {
			type: 'numeric',
			title: 'Club ID'
		},
		'club_external_id': {
			type: 'text',
			title: 'Club External ID'
		},
		'minute': {
			type: 'numeric',
			title: 'Minute'
		},
		'minute_add': {
			type: 'numeric',
			title: 'Minute Additional'
		},
	};
</script>
