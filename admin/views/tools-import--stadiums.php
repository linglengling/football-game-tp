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

$custom_fields_stadium = AnWPFL_Options::get_value( 'stadium_custom_fields' );

/*
|--------------------------------------------------------------------
| Prepare stadium columns
|--------------------------------------------------------------------
*/
$columns_stadium = [
	[
		'slug'  => 'stadium_title',
		'title' => __( 'Stadium Title', 'anwp-football-leagues' ) . ' *',
		'attr'  => 'disabled checked',
	],
	[
		'slug'  => 'address',
		'title' => __( 'Address', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'city',
		'title' => __( 'City', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'website',
		'title' => __( 'Website', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'capacity',
		'title' => __( 'Capacity', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'opened',
		'title' => __( 'Opened', 'anwp-football-leagues' ),
	],

	[
		'slug'  => 'surface',
		'title' => __( 'Surface', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'description',
		'title' => __( 'Description', 'anwp-football-leagues' ),
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
		'slug'  => 'stadium_id',
		'title' => __( 'Stadium ID', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'stadium_external_id',
		'title' => __( 'Stadium External ID', 'anwp-football-leagues' ) . ' **',
	],
];

if ( ! empty( $custom_fields_stadium ) && is_array( $custom_fields_stadium ) ) {
	foreach ( $custom_fields_stadium as $custom_field ) {

		$columns_stadium[] = [
			'slug'  => 'cf__' . esc_html( $custom_field ),
			'title' => 'Custom Field: ' . esc_html( $custom_field ),
		];
	}
}
?>
<div class="my-3 p-2 border anwpfl-batch-import-filter-wrapper" data-type="stadiums">
	<h5 class="my-1"><?php echo esc_html__( 'Columns order and visibility', 'anwp-football-leagues' ); ?> <a href="#" class="anwpfl-batch-import-update-settings ml-2"><?php echo esc_html__( 'apply new settings', 'anwp-football-leagues' ); ?></a></h5>

	<div class="anwp-overflow-x-auto">
		<div class="anwpfl-tools-sortable mt-2 d-flex">
			<?php foreach ( $columns_stadium as $column_stadium ) : ?>
				<div class="my-1 mr-1 py-1 px-2 border border-secondary anwp-d-flex-not-important flex-column align-items-center">
					<svg class="anwp-icon anwp-icon--s24 anwp-icon--octi anwp-drag-handler">
						<use xlink:href="#icon-grabber"></use>
					</svg>
					<div class="my-2" style="writing-mode: vertical-rl;">
						<?php echo esc_html( $column_stadium['title'] ); ?>
					</div>
					<label data-slug="<?php echo esc_attr( $column_stadium['slug'] ); ?>" class="mt-auto anwp-cursor-pointer">
						<input class="d-none" type="checkbox" <?php echo esc_attr( ! empty( $column_stadium['attr'] ) ? $column_stadium['attr'] : '' ); ?>>
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

	<p>** Use "Stadium ID" or "Stadium External ID" to update existing Stadiums.</p>
	<ol>
		<li>"Stadium ID" has a higher priority. It is a WordPress Post ID value. If such a Stadium exists in DB, data will be updated. If nothing found, a new Stadium will be created.</li>
		<li>If you set "Stadium External ID", the import process will update a first Stadium with such External ID or create a new one if nothing found.</li>
		<li>If you set them both ( "Stadium ID" and "Stadium External ID" ), the Stadium External ID will be ignored.</li>
	</ol>
</div>

<?php
$import_options = anwp_football_leagues()->data->get_import_options();

$jexcel_stadium_config = [
	'stadium_title'       => [
		'type'  => 'text',
		'title' => 'Stadium Title',
	],
	'address'             => [
		'type'  => 'text',
		'title' => 'Address',
	],
	'city'                => [
		'type'  => 'text',
		'title' => 'City',
	],
	'website'             => [
		'type'  => 'text',
		'title' => 'Website',
	],
	'capacity'            => [
		'type'  => 'text',
		'title' => 'Capacity',
	],
	'opened'              => [
		'type'  => 'text',
		'title' => 'Opened',
	],
	'surface'             => [
		'type'  => 'text',
		'title' => 'Surface',
	],
	'description'         => [
		'type'  => 'text',
		'title' => 'Description',
	],
	'custom_title_1'      => [
		'type'  => 'text',
		'title' => 'Custom - title 1',
	],
	'custom_title_2'      => [
		'type'  => 'text',
		'title' => 'Custom - title 2',
	],
	'custom_title_3'      => [
		'type'  => 'text',
		'title' => 'Custom - title 3',
	],
	'custom_value_1'      => [
		'type'  => 'text',
		'title' => 'Custom - Value 1',
	],
	'custom_value_2'      => [
		'type'  => 'text',
		'title' => 'Custom - Value 2',
	],
	'custom_value_3'      => [
		'type'  => 'text',
		'title' => 'Custom - Value 3',
	],
	'stadium_id'          => [
		'type'  => 'numeric',
		'title' => 'Stadium ID',
	],
	'stadium_external_id' => [
		'type'  => 'numeric',
		'title' => 'Stadium External ID',
	],
];
?>
<script>
	var anwpImportOptions = <?php echo wp_json_encode( anwp_football_leagues()->data->get_import_options() ); ?>;
	var anwpToolColumns   = <?php echo wp_json_encode( $jexcel_stadium_config ); ?>;
</script>
