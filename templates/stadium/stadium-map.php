<?php
/**
 * The Template for displaying Stadium >> Map Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/stadium/stadium-map.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.14.0
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'stadium_id' => '',
		'header'     => true,
	]
);

$map_key = AnWPFL_Options::get_value( 'google_maps_api' );

if ( empty( $map_key ) ) {
	return;
}

$map_data = get_post_meta( $data->stadium_id, '_anwpfl_map', true );

if ( ! is_array( $map_data ) || empty( $map_data['lat'] ) || empty( $map_data['longitude'] ) ) {
	return;
}

$is_consent_required = 'yes' === anwp_football_leagues()->customizer->get_value( 'stadium', 'map_consent_required' );

if ( $is_consent_required ) {
	$cookies = wp_unslash( $_COOKIE );

	if ( isset( $cookies['__fl_map_consent_allow'] ) && 'yes' === $cookies['__fl_map_consent_allow'] ) {
		$is_consent_required = false;
	}
}

if ( ! $is_consent_required ) {
	$google_maps_api_key = '?key=' . $map_key;
	wp_enqueue_script( 'google-maps-api-3', '//maps.googleapis.com/maps/api/js' . $google_maps_api_key, [], 3, false );
}
?>
<div class="stadium-map anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'stadium__content__location', __( 'Location', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}

	if ( $is_consent_required ) :
		?>
		<div class="stadium-map__consent anwp-h-min-400 p-4 anwp-bg-light">
			<p class="anwp-text-center mt-3">
				<?php echo esc_html( anwp_football_leagues()->customizer->get_value( 'stadium', 'map_consent_text', 'Consent Text' ) ); ?>
			</p>
			<p class="anwp-text-center mt-1">
				<button id="anwp-fl-map-consent-allow" class="button" type="button">
					<?php echo esc_html( anwp_football_leagues()->customizer->get_value( 'stadium', 'map_consent_btn_text', 'Load Map' ) ); ?>
				</button>
			</p>
			<p class="anwp-text-center mt-3">
				<img src="<?php echo esc_url( AnWP_Football_Leagues::url( 'public/img/google-maps-placeholder.png' ) ); ?>" alt="map placeholder">
			</p>
		</div>
	<?php else : ?>
		<div id="map--stadium" class="map map--stadium" data-lat="<?php echo esc_attr( $map_data['lat'] ); ?>" data-longitude="<?php echo esc_attr( $map_data['longitude'] ); ?>"></div>
	<?php endif; ?>
</div>
