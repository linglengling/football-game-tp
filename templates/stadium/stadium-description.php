<?php
/**
 * The Template for displaying Stadium >> Description Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/stadium/stadium-description.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.10.0
 * @version       0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'stadium_id' => '',
	]
);

$post_content = get_post_meta( $data->stadium_id, '_anwpfl_description', true );

if ( ! $post_content ) {
	return;
}
?>
<div class="stadium-description anwp-section anwp-text-base">
	<?php echo do_shortcode( wp_kses_post( wpautop( $post_content ) ) ); ?>
</div>
