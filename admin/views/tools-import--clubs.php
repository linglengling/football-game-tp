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

$custom_fields = AnWPFL_Options::get_value( 'club_custom_fields' );

$columns_club = [
	[
		'slug'  => 'club_title',
		'title' => __( 'Club Title', 'anwp-football-leagues' ) . ' *',
		'attr'  => 'disabled checked',
	],
	[
		'slug'  => 'abbreviation',
		'title' => __( 'Abbreviation', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'city',
		'title' => __( 'City', 'anwp-football-leagues' ),
		'attr'  => 'checked',
	],
	[
		'slug'  => 'country',
		'title' => __( 'Country', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'address',
		'title' => __( 'Address', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'website',
		'title' => __( 'Website', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'founded',
		'title' => __( 'Founded', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'is_national_team',
		'title' => __( 'National Team', 'anwp-football-leagues' ),
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
		'slug'  => 'club_id',
		'title' => __( 'Club ID', 'anwp-football-leagues' ),
	],
	[
		'slug'  => 'club_external_id',
		'title' => __( 'Club External ID', 'anwp-football-leagues' ) . ' **',
	],
];

if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
	foreach ( $custom_fields as $custom_field ) {

		$columns_club[] = [
			'slug'  => 'cf__' . esc_html( $custom_field ),
			'title' => 'Custom Field: ' . esc_html( $custom_field ),
		];
	}
}
?>
<div class="my-3 p-2 border anwpfl-batch-import-filter-wrapper">
	<h5 class="my-1"><?php echo esc_html__( 'Columns order and visibility', 'anwp-football-leagues' ); ?> <a href="#" class="anwpfl-batch-import-update-settings ml-2"><?php echo esc_html__( 'apply new settings', 'anwp-football-leagues' ); ?></a></h5>

	<div class="anwp-overflow-x-auto">
		<div class="anwpfl-tools-sortable mt-2 d-flex">
			<?php foreach ( $columns_club as $column_club ) : ?>
				<div class="my-1 mr-1 py-1 px-2 border border-secondary anwp-d-flex-not-important flex-column align-items-center">
					<svg class="anwp-icon anwp-icon--s24 anwp-icon--octi anwp-drag-handler">
						<use xlink:href="#icon-grabber"></use>
					</svg>
					<div class="my-2" style="writing-mode: vertical-rl;">
						<?php echo esc_html( $column_club['title'] ); ?>
					</div>
					<label data-slug="<?php echo esc_attr( $column_club['slug'] ); ?>" class="mt-auto anwp-cursor-pointer">
						<input class="d-none" type="checkbox" <?php echo esc_attr( ! empty( $column_club['attr'] ) ? $column_club['attr'] : '' ); ?>>
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

	<p>** Use "Club ID" or "Club External ID" to update existing clubs.</p>
	<ol>
		<li>"Club ID" has a higher priority. It is a WordPress Post ID value. If such a club exists in DB, data will be updated. If nothing found, a new club will be created.</li>
		<li>If you set "Club External ID", the import process will update a first club with such External ID or create a new one if nothing found.</li>
		<li>If you set them both ( "Club ID" and "Club External ID" ), the Club External ID will be ignored.</li>
	</ol>
</div>
<?php
$import_options     = anwp_football_leagues()->data->get_import_options();
$jexcel_club_config = [
	'club_title'       => [
		'type'  => 'text',
		'title' => 'Club Name',
	],
	'city'             => [
		'type'  => 'text',
		'title' => 'City',
	],
	'abbreviation'     => [
		'type'  => 'text',
		'title' => 'Club Abbreviation',
	],
	'country'          => [
		'title'        => 'Country',
		'type'         => 'dropdown',
		'autocomplete' => true,
		'source'       => $import_options['countries'],
	],
	'address'          => [
		'type'  => 'text',
		'title' => 'Address',
	],
	'website'          => [
		'type'  => 'text',
		'title' => 'Website',
	],
	'founded'          => [
		'type'  => 'text',
		'title' => 'Founded',
	],
	'is_national_team' => [
		'type'   => 'dropdown',
		'title'  => 'National Team',
		'width'  => 150,
		'source' => [ 'yes', 'no' ],
	],
	'club_id'          => [
		'type'  => 'numeric',
		'title' => 'Club ID',
	],
	'club_external_id' => [
		'type'  => 'numeric',
		'title' => 'Club External ID',
	],
	'custom_title_1'   => [
		'type'  => 'text',
		'title' => 'Custom - title 1',
	],
	'custom_title_2'   => [
		'type'  => 'text',
		'title' => 'Custom - title 2',
	],
	'custom_title_3'   => [
		'type'  => 'text',
		'title' => 'Custom - title 3',
	],
	'custom_value_1'   => [
		'type'  => 'text',
		'title' => 'Custom - Value 1',
	],
	'custom_value_2'   => [
		'type'  => 'text',
		'title' => 'Custom - Value 2',
	],
	'custom_value_3'   => [
		'type'  => 'text',
		'title' => 'Custom - Value 3',
	],
];

if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
	foreach ( $custom_fields as $custom_field ) {
		$jexcel_club_config[ 'cf__' . $custom_field ] = [
			'type'  => 'text',
			'title' => $custom_field,
		];
	}
}
?>
<script>
	var anwpImportOptions = <?php echo wp_json_encode( $import_options ); ?>;
	var anwpToolColumns   = <?php echo wp_json_encode( $jexcel_club_config ); ?>;
</script>
