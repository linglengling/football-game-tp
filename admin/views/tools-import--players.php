<?php
/**
 * Tools page for AnWP Football Leagues
 *
 * @link       https://anwp.pro
 * @since      0.8.2
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

$custom_fields_players = AnWPFL_Options::get_value( 'player_custom_fields' );

/*
|--------------------------------------------------------------------
| Prepare player columns
|--------------------------------------------------------------------
*/
$columns_player = [
	[
		'slug'  => 'player_name',
		'title' => __( 'Player Name', 'anwp-football-leagues' ) . ' *',
		'attr'  => 'disabled checked',
	],
	[
		'slug'  => 'short_name',
		'title' => __( 'Short Name', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'full_name',
		'title' => __( 'Full Name', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'weight',
		'title' => __( 'Weight (kg)', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'height',
		'title' => __( 'Height (cm)', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'position',
		'title' => __( 'Position', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'current_club',
		'title' => __( 'Current Club', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'national_team',
		'title' => __( 'National Team', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'place_of_birth',
		'title' => __( 'Place of Birth', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'date_of_birth',
		'title' => __( 'Date of Birth', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'date_of_death',
		'title' => __( 'Date of death', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'country_of_birth',
		'title' => __( 'Country of Birth', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'bio',
		'title' => __( 'Bio', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'nationality_1',
		'title' => __( 'Nationality', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'nationality_2',
		'title' => __( 'Nationality 2', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'custom_title_1',
		'title' => __( 'Custom Field - Title', 'anwp-football-leagues' ) . ' #1',
	],
	[
		'slug'  => 'custom_value_1',
		'title' => __( 'Custom Field - Value', 'anwp-football-leagues' ) . ' #1',
	],
	[
		'slug'  => 'custom_title_2',
		'title' => __( 'Custom Field - Title', 'anwp-football-leagues' ) . ' #2',
	],
	[
		'slug'  => 'custom_value_2',
		'title' => __( 'Custom Field - Value', 'anwp-football-leagues' ) . ' #2',
	],
	[
		'slug'  => 'custom_title_3',
		'title' => __( 'Custom Field - Title', 'anwp-football-leagues' ) . ' #3',
	],
	[
		'slug'  => 'custom_value_3',
		'title' => __( 'Custom Field - Value', 'anwp-football-leagues' ) . ' #3',
	],
	[
		'slug'  => 'player_id',
		'title' => __( 'Player ID', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'player_external_id',
		'title' => __( 'Player External ID', 'anwp-football-leagues' ) . ' **',
	],
];

if ( ! empty( $custom_fields_players ) && is_array( $custom_fields_players ) ) {
	foreach ( $custom_fields_players as $custom_field ) {

		$columns_player[] = [
			'slug'  => 'cf__' . esc_html( $custom_field ),
			'title' => 'Custom Field: ' . esc_html( $custom_field ),
		];
	}
}
?>
<div class="my-3 p-2 border anwpfl-batch-import-filter-wrapper">
	<h5 class="my-1"><?php echo esc_html__( 'Columns order and visibility', 'anwp-football-leagues' ); ?> <a href="#" class="anwpfl-batch-import-update-settings ml-2"><?php echo esc_html__( 'apply new settings', 'anwp-football-leagues' ); ?></a></h5>

	<div class="anwp-overflow-x-auto">
		<div class="anwpfl-tools-sortable mt-2 d-flex ">

			<?php foreach ( $columns_player as $column_player ) : ?>
				<div class="my-1 mr-1 py-1 px-2 border border-secondary anwp-d-flex-not-important flex-column align-items-center">
					<svg class="anwp-icon anwp-icon--s24 anwp-icon--octi anwp-drag-handler">
						<use xlink:href="#icon-grabber"></use>
					</svg>
					<div class="my-2" style="writing-mode: vertical-rl;">
						<?php echo esc_html( $column_player['title'] ); ?>
					</div>
					<label data-slug="<?php echo esc_attr( $column_player['slug'] ); ?>" class="mt-auto anwp-cursor-pointer">
						<input class="d-none" type="checkbox" <?php echo esc_attr( ! empty( $column_player['attr'] ) ? $column_player['attr'] : '' ); ?>>
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

	<p>** Use "Player ID" or "Player External ID" to update existing players.</p>
	<ol>
		<li>"Player ID" has a higher priority. It is a WordPress Post ID value. If such a player exists in DB, data will be updated. If nothing found, a new player will be created.</li>
		<li>If you set "Player External ID", the import process will update a first player with such External ID or create a new one if nothing found.</li>
		<li>If you set them both ( "Player ID" and "Player External ID" ), the Player External ID will be ignored.</li>
	</ol>
</div>
<?php
$import_options = anwp_football_leagues()->data->get_import_options();

$jexcel_player_config = [
	'player_name'        => [
		'type'  => 'text',
		'title' => 'Player Name',
		'width' => 120,
	],
	'short_name'         => [
		'type'  => 'text',
		'title' => 'Player Short Name',
		'width' => 120,
	],
	'full_name'          => [
		'type'  => 'text',
		'title' => 'Player Full Name',
		'width' => 150,
	],
	'weight'             => [
		'type'  => 'numeric',
		'title' => 'Weight',
	],
	'height'             => [
		'type'  => 'numeric',
		'title' => 'Height',
	],
	'position'           => [
		'type'         => 'dropdown',
		'title'        => 'Position',
		'autocomplete' => true,
		'width'        => 150,
		'source'       => $import_options['positions'],
	],
	'current_club'       => [
		'type'         => 'dropdown',
		'source'       => $import_options['clubs'],
		'autocomplete' => true,
		'title'        => 'Current Club',
	],
	'national_team'      => [
		'type'         => 'dropdown',
		'source'       => $import_options['clubs'],
		'autocomplete' => true,
		'title'        => 'National Team',
	],
	'place_of_birth'     => [
		'type'  => 'text',
		'title' => 'Place of Birth',
	],
	'date_of_birth'      => [
		'type'  => 'numeric',
		'title' => 'Date of Birth (YYYY-MM-DD)',
		'mask'  => 'yyyy-mm-dd',
	],
	'date_of_death'      => [
		'type'  => 'numeric',
		'title' => 'Date of Death (YYYY-MM-DD)',
		'mask'  => 'yyyy-mm-dd',
	],
	'bio'                => [
		'type'  => 'text',
		'title' => 'Bio',
	],
	'nationality_1'      => [
		'source'       => $import_options['countries'],
		'type'         => 'dropdown',
		'title'        => 'Nationality',
		'autocomplete' => true,
	],
	'country_of_birth'   => [
		'source'       => $import_options['countries'],
		'type'         => 'dropdown',
		'title'        => 'Country of Birth',
		'autocomplete' => true,
	],
	'nationality_2'      => [
		'type'         => 'dropdown',
		'source'       => $import_options['countries'],
		'autocomplete' => true,
		'title'        => 'Nationality 2',
	],
	'custom_title_1'     => [
		'type'  => 'text',
		'title' => 'Custom - title 1',
	],
	'custom_title_2'     => [
		'type'  => 'text',
		'title' => 'Custom - title 2',
	],
	'custom_title_3'     => [
		'type'  => 'text',
		'title' => 'Custom - title 3',
	],
	'custom_value_1'     => [
		'type'  => 'text',
		'title' => 'Custom - Value 1',
	],
	'custom_value_2'     => [
		'type'  => 'text',
		'title' => 'Custom - Value 2',
	],
	'custom_value_3'     => [
		'type'  => 'text',
		'title' => 'Custom - Value 3',
	],
	'player_id'          => [
		'type'  => 'numeric',
		'title' => 'Player ID',
	],
	'player_external_id' => [
		'type'  => 'numeric',
		'title' => 'Player External ID',
	],
];

if ( ! empty( $custom_fields_players ) && is_array( $custom_fields_players ) ) {
	foreach ( $custom_fields_players as $custom_field ) {
		$jexcel_player_config[ 'cf__' . $custom_field ] = [
			'type'  => 'text',
			'title' => $custom_field,
		];
	}
}
?>
<script>
	var anwpImportOptions = <?php echo wp_json_encode( anwp_football_leagues()->data->get_import_options() ); ?>;
	var anwpToolColumns   = <?php echo wp_json_encode( $jexcel_player_config ); ?>;
</script>
