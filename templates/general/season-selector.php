<?php
/**
 * The Template for displaying Season Selector
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/general/season-selector.php.
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
		'class'            => '',
		'selector_context' => '',
		'selector_id'      => '',
		'selector_class'   => '',
		'season_id'        => '',
	]
);

$season_id = $data->season_id;

if ( empty( $season_id ) ) {
	return '';
}
?>
<div class="anwp-fl-season-selector <?php echo esc_attr( $data->class ); ?>">
	<?php
	$dropdown_filter = [
		'context' => $data->selector_context,
		'id'      => $data->selector_id,
	];

	anwp_football_leagues()->helper->season_dropdown( $season_id, true, $data->selector_class, $dropdown_filter );
	?>
</div>
