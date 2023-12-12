<?php
/**
 * The Template for displaying staff content.
 * Content only (without title and comments).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/content-staff.php.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.7.0
 *
 * @version       0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Prepare data
$staff_id = get_the_ID();

if ( empty( $staff_id ) ) {
	return;
}

$staff_data = [
	'staff_id' => $staff_id,
];

/**
 * Hook: anwpfl/tmpl-staff/before_wrapper
 *
 * @since 0.7.5
 *
 * @param int $staff_id
 */
do_action( 'anwpfl/tmpl-staff/before_wrapper', $staff_id );
?>
<div class="anwp-b-wrap staff staff__inner">
	<?php
	$staff_sections = [
		'header',
		'description',
		'history',
	];

	/**
	 * Filter: anwpfl/tmpl-staff/sections
	 *
	 * @param array $staff_sections
	 * @param int   $staff_id
	 *
	 * @since 0.14.0
	 *
	 */
	$staff_sections = apply_filters( 'anwpfl/tmpl-staff/sections', $staff_sections, $staff_id );

	foreach ( $staff_sections as $section ) {
		anwp_football_leagues()->load_partial( $staff_data, 'staff/staff-' . sanitize_key( $section ) );
	}
	?>
</div>
<?php
/**
 * Hook: anwpfl/tmpl-staff/after_wrapper
 *
 * @since 0.7.5
 *
 * @param int $staff_id
 */
do_action( 'anwpfl/tmpl-staff/after_wrapper', $staff_id );
