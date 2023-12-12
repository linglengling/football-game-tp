<?php
/**
 * The Template for displaying Stadium >> Fixtures Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/stadium/stadium-fixtures.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.14.0
 *
 * @version       0.14.1
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

if ( ! intval( $data->stadium_id ) ) {
	return;
}

$matches_args = [
	'stadium_id'   => $data->stadium_id,
	'type'         => 'fixture',
	'sort_by_date' => 'asc',
];

$shortcode_html = anwp_football_leagues()->template->shortcode_loader( 'matches', $matches_args );

if ( empty( $shortcode_html ) ) {
	return;
}
?>
<div class="stadium-fixtures anwp-section">
	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'stadium__content__fixtures', __( 'Fixtures', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $shortcode_html;
	?>
</div>
