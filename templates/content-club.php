<?php
/**
 * The Template for displaying club content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/content-club.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.3.0
 * @version       0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Prepare tmpl data
$club   = get_post();
$prefix = '_anwpfl_';
$data   = [];

$fields = [
	'logo_big',
	'description',
	'city',
	'nationality',
	'address',
	'website',
	'founded',
	'stadium',
	'club_kit',
	'twitter',
	'youtube',
	'facebook',
	'instagram',
	'vk',
	'tiktok',
	'linkedin',
];

foreach ( $fields as $field ) {
	$data[ $field ] = $club->{$prefix . $field};
}

/**
 * Filter: anwpfl/tmpl-club/data_fields
 *
 * @since 0.7.5
 *
 * @param array   $data
 * @param WP_Post $club
 */
$data = apply_filters( 'anwpfl/tmpl-club/data_fields', $data, $club );


$data['club_id']   = $club->ID;
$data['season_id'] = anwp_football_leagues()->helper->get_season_id_maybe( $_GET, anwp_football_leagues()->get_active_club_season( $club->ID ) ); // phpcs:ignore WordPress.Security.NonceVerification

/**
 * Hook: anwpfl/tmpl-club/before_wrapper
 *
 * @since 0.7.5
 *
 * @param WP_Post $club
 */
do_action( 'anwpfl/tmpl-club/before_wrapper', $club );
?>
<div class="anwp-b-wrap club club-id-<?php echo esc_attr( $club->ID ); ?>">
	<?php
	$club_sections = [
		'header',
		'description',
		'fixtures',
		'latest',
		'squad',
		'gallery',
	];

	/**
	 * Filter: anwpfl/tmpl-club/sections
	 *
	 * @since 0.8.4
	 *
	 * @param array   $club_sections
	 * @param array   $data
	 */
	$club_sections = apply_filters( 'anwpfl/tmpl-club/sections', $club_sections, $data );

	foreach ( $club_sections as $section ) {
		anwp_football_leagues()->load_partial( $data, 'club/club-' . sanitize_key( $section ) );
	}
	?>
</div>
<?php
/**
 * Hook: anwpfl/tmpl-club/after_wrapper
 *
 * @since 0.7.5
 *
 * @param WP_Post $club
 */
do_action( 'anwpfl/tmpl-club/after_wrapper', $club );
