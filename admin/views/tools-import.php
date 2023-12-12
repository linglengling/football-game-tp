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

// phpcs:ignore WordPress.Security.NonceVerification
$active_tool     = isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : 'players';
$tools_available = [
	'players'  => __( 'players', 'anwp-football-leagues' ),
	'staff'    => __( 'staff', 'anwp-football-leagues' ),
	'referees' => __( 'referees', 'anwp-football-leagues' ),
	'clubs'    => __( 'clubs', 'anwp-football-leagues' ),
	'stadiums' => __( 'stadiums', 'anwp-football-leagues' ),
	'matches'  => __( 'matches', 'anwp-football-leagues' ),
	'goals'    => __( 'goals', 'anwp-football-leagues' ),
	'cards'    => __( 'cards', 'anwp-football-leagues' ),
	'subs'     => __( 'substitutes', 'anwp-football-leagues' ),
	'lineups'  => __( 'lineups', 'anwp-football-leagues' ),
];
?>
<div class="alert alert-info my-4" role="alert">
	<div class="d-block mb-1 w-100">
		<?php echo esc_html__( 'Select import type. Then copy and paste data from your source into the table below.', 'anwp-football-leagues' ); ?>
	</div>
	<div class="d-flex align-items-center">
		<svg class="anwp-icon anwp-icon--s14 anwp-icon--octi mr-1"><use xlink:href="#icon-info"></use></svg>
		<a href="https://anwp.pro/football-leagues-documentation/batch-import-tool/" target="_blank"><?php echo esc_html__( 'How to Use data Import Tool', 'anwp-football-leagues' ); ?></a><br>
	</div>
</div>

<div class="mb-3">
	<?php foreach ( $tools_available as $tool_key => $tool_text ) : ?>
		<?php if ( 'players' !== $tool_key ) : ?>
			<small class="text-muted mx-1 d-inline-block">|</small>
		<?php endif; ?>
		<a class="text-decoration-none anwp-text-capitalize anwp-text-sm <?php echo esc_attr( $tool_key === $active_tool ? 'text-muted' : '' ); ?>"
			href="<?php echo esc_url( admin_url( 'admin.php?page=anwp-settings-tools&tool=' . $tool_key ) ); ?>"><?php echo esc_html( $tool_text ); ?></a>
	<?php endforeach; ?>
</div>
<?php if ( $active_tool && in_array( $active_tool, array_keys( $tools_available ), true ) ) : ?>
	<?php AnWP_Football_Leagues::include_file( 'admin/views/tools-import--' . $active_tool ); ?>

	<div id="anwpfl-batch-import-table"></div>

	<div class="anwpfl-batch-import-save-wrapper">
		<div class="anwpfl-batch-import-save-info mt-3"></div>
		<button id="anwpfl-batch-import-save-btn" type="button" class="button button-primary px-4">
			<?php echo esc_html__( 'Save Data', 'anwp-football-leagues' ); ?>
		</button>
		<img class="mx-2 anwp-request-spinner" src="<?php echo esc_url( admin_url() ); ?>images/loading.gif" style="width: 24px; height: 24px"/>
	</div>
	<script>
		(function( $ ) {
			'use strict';

			$( function() {

				var $wrapper = $( '#anwpfl-import-wrapper' );
				var anwpImportOptions = <?php echo wp_json_encode( anwp_football_leagues()->data->get_import_options() ); ?>;

				if ( ! $( '#anwpfl-batch-import-table' ).length || typeof jexcel === 'undefined' || typeof anwpToolColumns === 'undefined' ) {
					return;
				}

				var jExcelOptions = {
					data: [],
					allowToolbar: true,
					columnSorting: false,
					rowDrag: false,
					allowInsertRow: true,
					allowManualInsertRow: true,
					allowInsertColumn: false,
					allowManualInsertColumn: false,
					allowDeleteRow: false,
					allowDeletingAllRows: false,
					allowDeleteColumn: false,
					allowRenameColumn: false,
					defaultColWidth: '110px',
					rowResize: true,
					minDimensions: [ 1, 5 ],
					contextMenu: function() {
						return null;
					},
					columns: []
				};

				var container = document.getElementById( 'anwpfl-batch-import-table' );
				var tableData = jexcel( container, jExcelOptions );

				var btnSave       = $( '#anwpfl-batch-import-save-btn' );
				var infoSave      = $wrapper.find( '.anwpfl-batch-import-save-info' );
				var activeRequest = false;
				var dataColumns   = [];

				$wrapper.find( '.anwpfl-tools-sortable' ).sortable( {
					handle: '.anwp-drag-handler'
				} );

				function updateTableStructure(){

					container.innerHTML = '';
					infoSave.html( '' );
					dataColumns = [];

					$wrapper.find( '.anwpfl-batch-import-filter-wrapper .anwpfl-tools-sortable label' ).each( function() {
						var $this = $( this );

						if ( $this.find( 'input' ).prop( 'checked' ) ) {
							dataColumns.push( anwpToolColumns[ $this.data( 'slug' ) ] );
						}
					} );

					jExcelOptions.columns = dataColumns;
					jExcelOptions.data    = [];

					tableData = jexcel( container, jExcelOptions );
				}

				updateTableStructure();

				$wrapper.on( 'click', '.anwpfl-batch-import-update-settings', function( e ) {
					e.preventDefault();

					updateTableStructure();

					toastr.success( 'New settings have been applied' );
				} );

				// Save data
				btnSave.on( 'click', function( e ) {

					var importType = '<?php echo esc_html( $active_tool ); ?>';

					e.preventDefault();

					// Check for active request and type
					if ( activeRequest || ! importType ) {
						return;
					}

					activeRequest = true;
					btnSave.addClass( 'anwp-request-active' );

					var data = {
						table: tableData.getData(),
						headers: []
					};

					$wrapper.find( '.anwpfl-batch-import-filter-wrapper .anwpfl-tools-sortable label' ).each( function() {
						var $this = $( this );

						if ( $this.find( 'input' ).prop( 'checked' ) ) {
							data.headers.push( $this.data( 'slug' ) );
						}
					} );

					jQuery.ajax( {
						dataType: 'json',
						method: 'POST',
						data: data,
						beforeSend: function( xhr ) {
							xhr.setRequestHeader( 'X-WP-Nonce', anwpImportOptions.rest_nonce );
						}.bind( this ),
						url: anwpImportOptions.rest_root + 'anwpfl/v1/import/' + importType
					} ).done( function( responseText ) {
						toastr.success( responseText );
						updateTableStructure();
					} ).fail( function( response ) {
						toastr.error( response.responseJSON.message ? response.responseJSON.message : 'Error' );
					} ).always( function() {
						activeRequest = false;
						btnSave.removeClass( 'anwp-request-active' );
					} );
				} );
			} );
		}( jQuery ));
	</script>
	<?php
endif;
