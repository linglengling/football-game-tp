<?php
/**
 * The Template for displaying Widget :: Cards.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/widget-cards.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.7.3
 *
 * @version       0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data->context = 'widget';
$data->layout  = 'mini';

echo anwp_football_leagues()->template->shortcode_loader( 'cards', (array) $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// Prevent errors with new params
$data = (object) wp_parse_args(
	$data,
	[
		'link_text'   => '',
		'link_target' => '',
	]
);

if ( ! empty( $data->link_text ) && ! empty( $data->link_target ) ) : ?>
	<div class="anwp-b-wrap mt-2 position-relative anwp-fl-btn-outline anwp-text-sm w-100 widget-cards__link">
		<?php echo esc_html( $data->link_text ); ?>
		<a class="anwp-link-cover anwp-link-without-effects" target="_blank" href="<?php echo esc_url( get_permalink( (int) $data->link_target ) ); ?>"></a>
	</div>
	<?php
endif;
