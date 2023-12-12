<?php
/**
 * The Template for displaying Match Countdown.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-countdown.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.15.0
 *
 * @version       0.15.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( 'hide' === anwp_football_leagues()->customizer->get_value( 'match', 'fixture_flip_countdown' ) ) {
	return;
}

$data = (object) wp_parse_args(
	$data,
	[
		'kickoff'        => '',
		'kickoff_c'      => '',
		'special_status' => '',
		'context'        => '',
		'label_size'     => '',
		'value_size'     => '',
	]
);

if ( '0000-00-00 00:00:00' === $data->kickoff || ! $data->kickoff || in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) {
	return;
}

$kickoff_diff = ( date_i18n( 'U', get_date_from_gmt( $data->kickoff, 'U' ) ) - date_i18n( 'U' ) ) > 0 ? date_i18n( 'U', get_date_from_gmt( $data->kickoff, 'U' ) ) - date_i18n( 'U' ) : 0;

if ( $kickoff_diff > 0 ) :
	$label_style = '';
	$value_style = '';

	if ( absint( $data->label_size ) ) {
		$label_style .= 'font-size: ' . absint( $data->label_size ) . 'px;';
	}

	if ( absint( $data->value_size ) ) {
		$value_style .= 'font-size: ' . absint( $data->value_size ) . 'px;';
	}
	?>
	<div class="anwp-text-center <?php echo esc_attr( 'widget' === $data->context ? 'py-2' : 'py-3' ); ?> anwp-fl-game-countdown anwp-fl-game-countdown--<?php echo esc_attr( $data->context ); ?> d-none"
		data-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>">
		<div class="d-flex justify-content-center anwp-fl-game-countdown__inner">
			<div class="anwp-fl-game-countdown__item anwp-fl-game-countdown__days">
				<div class="anwp-fl-game-countdown__label" style="<?php echo esc_html( $label_style ); ?>"><?php echo esc_html( AnWPFL_Text::get_value( 'data__flip_countdown__days', esc_html_x( 'days', 'flip countdown', 'anwp-football-leagues' ) ) ); ?></div>
				<div class="anwp-fl-game-countdown__value anwp-fl-game-countdown__value-days" style="<?php echo esc_html( $value_style ); ?>"></div>
			</div>
			<div class="anwp-fl-game-countdown__separator"></div>
			<div class="anwp-fl-game-countdown__item anwp-fl-game-countdown__hours">
				<div class="anwp-fl-game-countdown__label" style="<?php echo esc_html( $label_style ); ?>"><?php echo esc_html( AnWPFL_Text::get_value( 'data__flip_countdown__hours', esc_html_x( 'hours', 'flip countdown', 'anwp-football-leagues' ) ) ); ?></div>
				<div class="anwp-fl-game-countdown__value anwp-fl-game-countdown__value-hours" style="<?php echo esc_html( $value_style ); ?>"></div>
			</div>
			<div class="anwp-fl-game-countdown__separator"></div>
			<div class="anwp-fl-game-countdown__item anwp-fl-game-countdown__minutes">
				<div class="anwp-fl-game-countdown__label" style="<?php echo esc_html( $label_style ); ?>"><?php echo esc_html( AnWPFL_Text::get_value( 'data__flip_countdown__minutes', esc_html_x( 'minutes', 'flip countdown', 'anwp-football-leagues' ) ) ); ?></div>
				<div class="anwp-fl-game-countdown__value anwp-fl-game-countdown__value-minutes" style="<?php echo esc_html( $value_style ); ?>"></div>
			</div>
			<div class="anwp-fl-game-countdown__separator"></div>
			<div class="anwp-fl-game-countdown__item anwp-fl-game-countdown__seconds">
				<div class="anwp-fl-game-countdown__label" style="<?php echo esc_html( $label_style ); ?>"><?php echo esc_html( AnWPFL_Text::get_value( 'data__flip_countdown__seconds', esc_html_x( 'seconds', 'flip countdown', 'anwp-football-leagues' ) ) ); ?></div>
				<div class="anwp-fl-game-countdown__value anwp-fl-game-countdown__value-seconds" style="<?php echo esc_html( $value_style ); ?>"></div>
			</div>
		</div>
	</div>
<?php endif; ?>
