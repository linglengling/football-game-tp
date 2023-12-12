<?php
/**
 * The Template for displaying stadium content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/content-stadium.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.3.0
 *
 * @version       0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
// Prepare data
$stadium_id = get_the_ID();

$stadium_data = [
	'stadium_id' => $stadium_id,
	'season_id'  => anwp_football_leagues()->helper->get_season_id_maybe( $_GET, anwp_football_leagues()->get_active_stadium_season( $stadium_id ) ), // phpcs:ignore WordPress.Security.NonceVerification
];
?>
<div class="anwp-b-wrap stadium stadium-id-<?php echo esc_attr( $stadium_id ); ?>">
	<?php
	$stadium_sections = [
		'header',
		'description',
		'fixtures',
		'latest',
		'gallery',
		'map',
	];

	/**
	 * Filter: anwpfl/tmpl-stadium/sections
	 *
	 * @since 0.14.0
	 *
	 * @param array $stadium_sections
	 * @param int   $stadium_id
	 */
	$stadium_sections = apply_filters( 'anwpfl/tmpl-stadium/sections', $stadium_sections, $stadium_id );

	foreach ( $stadium_sections as $section ) {
		anwp_football_leagues()->load_partial( $stadium_data, 'stadium/stadium-' . sanitize_key( $section ) );
	}
	?>
</div>
<?php
/**
 * Hook: anwpfl/tmpl-stadium/after_wrapper
 *
 * @since 0.7.5
 *
 * @param WP_Post $stadium
 */
do_action( 'anwpfl/tmpl-stadium/after_wrapper', get_post( $stadium_id ) );
