<?php
/**
 * Plugin Name: TP Football Leagues
 * Plugin URI:  
 * Description: Create and manage your own football club, competition, league or soccer news website. Knockout and round-robin stages, player profiles, standing tables and much more.
 * Version:     0.15.2
 * Author:      tp
 * Author URI:  
 * License:     GPLv2+
 * Requires PHP: 5.6
 * Text Domain: anwp-football-leagues
 * Domain Path: /languages
 */



/**
 * Built using generator-plugin-wp (https://github.com/WebDevStudios/generator-plugin-wp)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

define( 'ANWP_FL_VERSION', '0.15.2' );

require_once plugin_dir_path( __FILE__ ) . 'wm_translate.php';


// Check for required PHP version
if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
	add_action( 'admin_notices', 'anwpfl_requirements_not_met_notice' );
} else {

	// Require the main plugin class
	require_once plugin_dir_path( __FILE__ ) . 'class-anwp-football-leagues.php';

	// Kick it off.
	add_action( 'plugins_loaded', array( anwp_football_leagues(), 'hooks' ) );

	// Activation and deactivation.
	register_activation_hook( __FILE__, array( anwp_football_leagues(), 'activate' ) );
	register_deactivation_hook( __FILE__, array( anwp_football_leagues(), 'deactivate' ) );
}

/**
 * Adds a notice to the dashboard if the plugin requirements are not met.
 *
 * @since  0.2.0
 * @return void
 */
function anwpfl_requirements_not_met_notice() {

	// Compile default message.
	$default_message = esc_html__( 'Football Leagues by AnWPPro is missing requirements and currently NOT ACTIVE. Please make sure all requirements are available.', 'anwp-football-leagues' );

	// Default details.
	$details = '';

	if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
		/* translators: %s minimum PHP version */
		$details .= '<small>' . sprintf( esc_html__( 'Football Leagues by AnWPPro cannot run on PHP versions older than %s. Please contact your hosting provider to update your site.', 'anwp-football-leagues' ), '5.6.0' ) . '</small><br />';
	}

	// Output errors.
	?>
	<div id="message" class="error">
		<p><?php echo wp_kses_post( $default_message ); ?></p>
		<?php echo wp_kses_post( $details ); ?>
	</div>
	<?php
}

/**
 * Grab the AnWP_Football_Leagues object and return it.
 * Wrapper for AnWP_Football_Leagues::get_instance().
 *
 * @since  0.1.0
 * @return AnWP_Football_Leagues  Singleton instance of plugin class.
 */
function anwp_football_leagues() {
	return AnWP_Football_Leagues::get_instance();
}
