<?php
/**
 * The Template for displaying Match >> Summary Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-summary.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.10.23
 * @version       0.14.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'finished' => '',
		'summary'  => '',
		'header'   => true,
	]
);

if ( empty( $data->summary ) ) {
	return;
}

/**
 * Hook: anwpfl/tmpl-match/summary_before
 *
 * @param object $data Match data
 *
 * @since 0.7.5
 */
do_action( 'anwpfl/tmpl-match/summary_before', $data );
?>
<div class="match-summary anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {

		$block_text = absint( $data->finished )
			? esc_html( AnWPFL_Text::get_value( 'match__summary__match_summary', __( 'Match Summary', 'anwp-football-leagues' ) ) )
			: esc_html( AnWPFL_Text::get_value( 'match__summary__match_preview', __( 'Match Preview', 'anwp-football-leagues' ) ) );

		anwp_football_leagues()->load_partial(
			[
				'text' => $block_text,
			],
			'general/header'
		);
	}
	?>

	<div class="match__summary anwp-text-base">
		<?php echo do_shortcode( wpautop( $data->summary ) ); ?>
	</div>
</div>


