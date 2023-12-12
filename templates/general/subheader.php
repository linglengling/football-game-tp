<?php
/**
 * The Template for displaying Header - 2nd Level.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/general/subheader.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.14.0
 *
 * @version       0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'text'  => '',
		'class' => '',
	]
);

if ( empty( $data->text ) ) {
	return;
}
?>
<div class="anwp-fl-block-subheader anwp-text-uppercase anwp-text-lg anwp-bg-light p-1 <?php echo esc_attr( $data->class ); ?>">
	<?php echo wp_kses_post( $data->text ); ?>
</div>
