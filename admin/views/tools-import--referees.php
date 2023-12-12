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

$custom_fields_referee = AnWPFL_Options::get_value( 'referee_custom_fields' );

/*
|--------------------------------------------------------------------
| Prepare referee columns
|--------------------------------------------------------------------
*/
$columns_referee = [
	[
		'slug'  => 'referee_name',
		'title' => __( 'Referee Name', 'anwp-football-leagues' ) . ' *',
		'attr'  => 'disabled checked',
	],
	[
		'slug'  => 'short_name',
		'title' => __( 'Short Name', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'job_title',
		'title' => __( 'Job Title', 'anwp-football-leagues' ),
		'attr'  => 'checked',
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
		'slug'  => 'nationality_1',
		'title' => __( 'Nationality', 'anwp-football-leagues' ) . ' #1',
	],
	[
		'slug'  => 'nationality_2',
		'title' => __( 'Nationality', 'anwp-football-leagues' ) . ' #2',
	],
	[
		'slug'  => 'referee_id',
		'title' => __( 'Referee ID', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'referee_external_id',
		'title' => __( 'Referee External ID', 'anwp-football-leagues' ) . ' **',
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
];

if ( ! empty( $custom_fields_referee ) && is_array( $custom_fields_referee ) ) {
	foreach ( $custom_fields_referee as $custom_field ) {

		$columns_referee[] = [
			'slug'  => 'cf__' . esc_html( $custom_field ),
			'title' => 'Custom Field: ' . esc_html( $custom_field ),
		];
	}
}
?>
<div class="my-3 p-2 border anwpfl-batch-import-filter-wrapper">
	<h5 class="my-1">
		<?php echo esc_html__( 'Columns order and visibility', 'anwp-football-leagues' ); ?>
		<a href="#" class="anwpfl-batch-import-update-settings ml-2"><?php echo esc_html__( 'apply new settings', 'anwp-football-leagues' ); ?></a>
	</h5>

	<div class="anwp-overflow-x-auto">
		<div class="anwpfl-tools-sortable mt-2 d-flex">

			<?php foreach ( $columns_referee as $column_referee ) : ?>
				<div class="my-1 mr-1 py-1 px-2 border border-secondary anwp-d-flex-not-important flex-column align-items-center">
					<svg class="anwp-icon anwp-icon--s24 anwp-icon--octi anwp-drag-handler">
						<use xlink:href="#icon-grabber"></use>
					</svg>
					<div class="my-2" style="writing-mode: vertical-rl;">
						<?php echo esc_html( $column_referee['title'] ); ?>
					</div>
					<label data-slug="<?php echo esc_attr( $column_referee['slug'] ); ?>" class="mt-auto anwp-cursor-pointer">
						<input class="d-none" type="checkbox" <?php echo esc_attr( ! empty( $column_referee['attr'] ) ? $column_referee['attr'] : '' ); ?>>
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
	<p>** Use "Referee ID" or "Referee External ID" to update existing Referees.</p>
	<ol>
		<li>"Referee ID" has a higher priority. It is a WordPress Post ID value. If such a Referee exists in DB, data will be updated. If nothing found, a new Referee will be created.</li>
		<li>If you set "Referee External ID", the import process will update a first Referee with such External ID or will create a new one if nothing found.</li>
		<li>If you set them both ( "Referee ID" and "Referee External ID" ), the Referee External ID will be ignored.</li>
	</ol>
</div>
<?php
$jexcel_referee_config = [
	'referee_name'        => [
		'type'  => 'text',
		'title' => 'Referee Name',
		'width' => 120,
	],
	'short_name'          => [
		'type'  => 'text',
		'title' => 'Referee Short Name',
		'width' => 120,
	],
	'job_title'           => [
		'type'  => 'text',
		'title' => 'Job Title',
		'width' => 120,
	],
	'place_of_birth'      => [
		'type'  => 'text',
		'title' => 'Place of Birth',
	],
	'date_of_birth'       => [
		'type'  => 'numeric',
		'title' => 'Date of Birth (YYYY-MM-DD)',
		'mask'  => 'yyyy-mm-dd',
	],
	'nationality_1'       => [
		'source'       => array_values( anwp_football_leagues()->data->cb_get_countries() ),
		'type'         => 'dropdown',
		'title'        => 'Nationality',
		'autocomplete' => true,
	],
	'nationality_2'       => [
		'type'         => 'dropdown',
		'source'       => array_values( anwp_football_leagues()->data->cb_get_countries() ),
		'autocomplete' => true,
		'title'        => 'Nationality 2',
	],
	'referee_id'          => [
		'type'  => 'numeric',
		'title' => 'Referee ID',
	],
	'referee_external_id' => [
		'type'  => 'numeric',
		'title' => 'Referee External ID',
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
];

if ( ! empty( $custom_fields_referee ) && is_array( $custom_fields_referee ) ) {
	foreach ( $custom_fields_referee as $custom_field ) {
		$jexcel_referee_config[ 'cf__' . $custom_field ] = [
			'type'  => 'text',
			'title' => $custom_field,
		];
	}
}
?>
<script>
	var anwpImportOptions = <?php echo wp_json_encode( anwp_football_leagues()->data->get_import_options() ); ?>;
	var anwpToolColumns   = <?php echo wp_json_encode( $jexcel_referee_config ); ?>;
</script>
