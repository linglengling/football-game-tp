<?php
/**
 * Customize page for AnWP Football Leagues
 *
 * @link       https://anwp.pro
 * @since      0.14.0
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

$is_mode_active = 'no' !== get_option( 'anwp_fl_customizer_mode' );
?>
<div class="anwp-b-wrap" style="max-width: 1400px; margin-top: 30px;">
	<div class="postbox">
		<div class="inside">
			<h1 class="text-left text-uppercase m-3 anwp-text-gray-600"><?php echo esc_html__( 'Customize Options', 'anwp-football-leagues' ); ?></h1>
			<div class="d-flex mt-4">
				<div class="anwp-flex-1 py-3 px-4 m-3 anwp-border anwp-border-gray-300" style="border-width: 2px; border-radius: 2px;">

					<h2 class="mt-0 mb-4 anwp-text-teal-600 anwp-text-lg anwp-text-center">Easily change 🖌 colors, 🔠 fonts, 📐 sizes and 🎨 backgrounds making changes in just a few 🖱🖱 clicks.</h2>

					<div class="my-4 anwp-text-base anwp-text-teal-800 p-2 anwp-bg-teal-100 anwp-border anwp-border-teal-300">
						<div>1) install SiteOrigin CSS (free)</div>
						<div>2) activate Football Leagues Mode (activated by default)</div>
						<div>3) <a target="_blank" href="<?php echo esc_url_raw( admin_url( '/themes.php?page=so_custom_css' ) ); ?>">start customizing</a></div>
					</div>

					<div class="d-flex">
						<div class="anwp-flex-none">
							<img src="https://ps.w.org/so-css/assets/icon.svg?rev=2556879" class="anwp-object-contain anwp-w-100 anwp-h-100 mr-3">
						</div>
						<div class="d-flex flex-column">
							<h3 class="mt-0 mb-1 anwp-text-blue-700">SiteOrigin CSS + Special Football Leagues Mode *</h3>
							<p class="my-1">SiteOrigin CSS is the simple yet powerful CSS editor for WordPress. It gives you visual controls that let you edit the look and feel of your site in real-time.</p>
							<p class="authors mt-1 mb-0 anwp-text-blue-600"><a target="_blank" href="https://wordpress.org/plugins/so-css/">SiteOrigin CSS at wp.org</a></p>
						</div>
					</div>

					<p class="anwp-text-xs my-3">* Special mode allow to select only recommended classes (selectors) from Football Leagues template files. See tutorial below for detailed info. If you want to style other site parts, disable Football Leagues Mode.</p>

					<?php
					if ( ! defined( 'SOCSS_VERSION' ) && current_user_can( 'install_plugins' ) ) :
						if ( ! function_exists( 'get_plugins' ) ) {
							require_once ABSPATH . 'wp-admin/includes/plugin.php';
						}

						$all_plugins      = get_plugins();
						$plugin_installed = isset( $all_plugins['so-css/so-css.php'] ) || isset( $all_plugins['so-css2/so-css.php'] );

						if ( $plugin_installed && current_user_can( 'activate_plugins' ) ) :
							?>
							<a href="<?php echo esc_url( wp_nonce_url( 'plugins.php?action=activate&plugin=' . rawurlencode( 'so-css/so-css.php' ), 'activate-plugin_so-css/so-css.php' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Activate SiteOrigin CSS', 'anwp-football-leagues' ); ?></a>
						<?php elseif ( current_user_can( 'install_plugins' ) ) : ?>
							<a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=so-css' ), 'install-plugin_so-css' ) ); ?>" class="button button-primary"><?php echo esc_html__( 'Install SiteOrigin CSS', 'anwp-football-leagues' ); ?></a>
						<?php endif; ?>
					<?php endif; ?>
					<hr class="mb-2 mt-4">
					<div class="d-flex flex-wrap align-items-center anwp-bg-gray-100 p-2">
						<span class="anwp-text-sm">Football Leagues Mode: </span>
						<span class="px-2 text-white ml-2 anwp-flex-none d-inline-block mr-auto <?php echo $is_mode_active ? 'anwp-bg-green-600' : 'anwp-bg-red-600'; ?>" data-text-active="Active" data-text-disabled="Disabled" id="anwp-customize-mode-status" data-mode-active="<?php echo $is_mode_active ? 'yes' : 'no'; ?>">
							<?php echo $is_mode_active ? 'Active' : 'Disabled'; ?>
						</span>
						<span class="spinner mr-1 mt-0"></span>
						<button class="button button-secondary d-flex align-items-center ml-2" id="anwp-customize-change-btn" type="button">
							Change
						</button>
					</div>

					<h3 style="margin-top: 20px; margin-bottom: 5px;">Tutorials:</h3>
					<a target="_blank" href="https://anwppro.userecho.com/en/knowledge-bases/2/articles/1959-customize-football-leagues-with-siteorigin-css-special-mode-wordpress-plugin">
						- Customize Football Leagues with SiteOrigin CSS
					</a>
				</div>
				<div class="anwp-flex-1 py-3 px-4 m-3 anwp-border anwp-border-gray-300" style="border-width: 2px; border-radius: 2px;">

					<h2 class="mt-0 mb-4 anwp-text-teal-600 anwp-text-lg anwp-text-center">Change default font sizes, background colors and other display options in Customizer.</h2>

					<a target="_blank" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=anwp_fl_panel' ) ); ?>"
						class="anwp-text-center button button-secondary d-block ml-2 w-50 mx-auto" id="anwp-customizer-go" type="button" style="font-size: 16px;">
						Open Customizer
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	(function( $ ) {
		'use strict';

		$( function() {

			var $badgeMode    = $( '#anwp-customize-mode-status' );
			var $btnChange    = $( '#anwp-customize-change-btn' );
			var activeRequest = false;

			$btnChange.on( 'click', function( e ) {

				e.preventDefault();

				if ( activeRequest ) {
					return;
				}

				activeRequest = true;
				$btnChange.siblings( 'span.spinner' ).addClass( 'is-active' );
				$btnChange.prop( 'disabled', true );

				jQuery.ajax( {
					dataType: 'json',
					method: 'POST',
					data: { 'mode_active': $badgeMode.data( 'mode-active' ) },
					beforeSend: function( xhr ) {
						xhr.setRequestHeader( 'X-WP-Nonce', anwp.rest_nonce );
					}.bind( this ),
					url: anwp.rest_root + 'anwpfl/v1/customize/toggle-mode/'
				} ).done( function() {

					if( 'yes' === $badgeMode.data( 'mode-active' ) ) {
						$badgeMode.data( 'mode-active', 'no' );
						$badgeMode.removeClass( 'anwp-bg-green-600' );
						$badgeMode.addClass( 'anwp-bg-red-600' );
						$badgeMode.text( $badgeMode.data( 'text-disabled' ) );
					} else {
						$badgeMode.data( 'mode-active', 'yes' );
						$badgeMode.removeClass( 'anwp-bg-red-600' );
						$badgeMode.addClass( 'anwp-bg-green-600' );
						$badgeMode.text( $badgeMode.data( 'text-active' ) );
					}

				} ).fail( function( response ) {
					toastr.error( response.responseJSON.message ? response.responseJSON.message : 'Error' );
				} ).always( function() {
					activeRequest = false;
					$btnChange.siblings( 'span.spinner' ).removeClass( 'is-active' );
					$btnChange.prop( 'disabled', false );
				} );
			} );
		} );
	}( jQuery ));
</script>
