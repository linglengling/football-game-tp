<?php
/**
 * The Template for displaying flag.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/general/flag.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.14.14
 *
 * @version       0.14.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'country_code' => '',
		'class'        => 'options__flag',
		'size'         => 16,
	]
);

if ( empty( $data->country_code ) ) {
	return;
}

if ( '___' === mb_substr( $data->country_code, 0, 3 ) ) :

	$custom_country = anwp_football_leagues()->data->get_custom_county_data( $data->country_code );

	if ( $custom_country ) :
		?>
		<div
			class="d-inline-block f<?php echo absint( $data->size ); ?> <?php echo esc_attr( $data->class ); ?>"
			data-toggle="anwp-tooltip"
			data-tippy-content="<?php echo esc_attr( $custom_country['title'] ); ?>">
			<img class="anwp-object-contain anwp-w-<?php echo 32 === absint( $data->size ) ? 30 : 15; ?> anwp-h-<?php echo 32 === absint( $data->size ) ? 30 : 15; ?>"
				src="<?php echo esc_url( $custom_country['image'] ); ?>" alt="<?php echo esc_attr( $custom_country['title'] ); ?>">

		</div>
	<?php endif; ?>
<?php elseif ( in_array( $data->country_code, [ '__World', '__Africa', '__Asia', '__Europe', '__NC_America', '__Oceania', '__South_America' ], true ) ) : ?>
	<div
		class="d-inline-block f<?php echo absint( $data->size ); ?> <?php echo esc_attr( $data->class ); ?>"
		data-toggle="anwp-tooltip"
		data-tippy-content="<?php echo esc_attr( anwp_football_leagues()->data->get_value_by_key( $data->country_code, 'country' ) ); ?>">
		<svg class="anwp-icon anwp-icon--octi anwp-icon--s<?php echo 32 === absint( $data->size ) ? 36 : 20; ?>">
			<use xlink:href="#icon-world-flag"></use>
		</svg>
	</div>
<?php else : ?>
	<div
		class="d-inline-block f<?php echo absint( $data->size ); ?> <?php echo esc_attr( $data->class ); ?>"
		data-toggle="anwp-tooltip"
		data-tippy-content="<?php echo esc_attr( anwp_football_leagues()->data->get_value_by_key( $data->country_code, 'country' ) ); ?>">
		<span class="flag <?php echo esc_attr( $data->country_code ); ?>"></span>
	</div>
	<?php
endif;
