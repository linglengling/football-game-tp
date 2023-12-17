<?php
/**
 * The Template for displaying Match (slider version). Used most in widgets.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match--slider.php.
 *
 * @var object $data - Object with shortcode args.
 *
 * @author         Andrei Strekozov <anwp.pro>
 * @package        AnWP-Football-Leagues/Templates
 * @since          0.7.4
 *
 * @version        0.14.12
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'show_match_datetime' => true,
		'show_club_name'      => 1,
		'kickoff'             => '',
		'kickoff_c'           => '',
		'kickoff_orig'        => '',
		'match_date'          => '',
		'match_time'          => '',
		'home_club'           => '',
		'away_club'           => '',
		'club_home_abbr'      => '',
		'competition_id'      => '',
		'club_away_abbr'      => '',
		'club_home_logo'      => '',
		'club_away_logo'      => '',
		'club_home_link'      => '',
		'club_away_link'      => '',
		'match_id'            => '',
		'finished'            => '',
		'home_goals'          => '',
		'away_goals'          => '',
		'extra'               => '',
		'aggtext'             => '',
		'permalink'           => '',
		'special_status'      => '',
		'datetime_tz'         => true,
	]
);

if ( '0000-00-00 00:00:00' === $data->kickoff || ! $data->kickoff || in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) {
	return;
}

$kickoff_diff = ( date_i18n( 'U', get_date_from_gmt( $data->kickoff, 'U' ) ) - date_i18n( 'U' ) ) > 0 ? date_i18n( 'U', get_date_from_gmt( $data->kickoff, 'U' ) ) - date_i18n( 'U' ) : 0;

$translate = new WM_Translator();


$wm_date = new DateTime();
$wm_date->setTimestamp(strtotime($data->kickoff_orig));

if($translate->language == 'vi'){
	$wm_date->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
	$match_date = $wm_date->format('Y-m-d');
	$match_time = $wm_date->format('H:i A');
	$countdown = $wm_date->format('c');
}elseif($translate->language == 'pt-br'){
	$wm_date->setTimezone(new DateTimeZone('America/Sao_Paulo'));
	$match_date = $wm_date->format('Y-m-d');
	$match_time = $wm_date->format('H:i A');
	$countdown = $wm_date->format('c');
}else{
	$match_date = $data->match_date;
	$match_time = $data->match_time;
	$countdown = $wm_date->format('c');
}

?>



<div class="anwp-fl-game competition__match match-list__item match-list-item match-simple anwp-border-light p-0 position-relative game-status-<?php echo absint( $data->finished ); ?>"
	<?php if ( AnWP_Football_Leagues::string_to_bool( $data->datetime_tz ) ) : ?>
		data-fl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>"
	<?php endif; ?>
	data-anwp-match="<?php echo intval( $data->match_id ); ?>" data-fl-game-kickoff="<?php echo esc_attr( $data->kickoff_orig ); ?>">

	<?php if ( $data->show_match_datetime && '0000-00-00 00:00:00' !== $data->kickoff ) : ?>
		<div class="match-list-item__kickoff match-simple__kickoff px-1 anwp-text-xs <?php echo $data->show_club_name ? 'd-flex justify-content-between mb-1' : 'anwp-text-center'; ?>">
			<span class="match-simple__date match__date-formatted">
				<?php echo $match_date; ?>
			</span>
			<span class="competition_name"><?php echo get_the_title( $data->competition_id ) ?></span>
			<?php if ( 'TBD' !== $data->special_status ) : ?>
				<?php echo $data->show_club_name ? '' : '-'; ?>
				<span class="match-simple__time match__time-formatted">
					<?php echo $match_time; ?>
				</span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	

	<div class="anwp-row anwp-no-gutters p-1">
		<div class="anwp-col-6 d-flex align-items-center">

			<?php if ( $data->club_home_logo ) : ?>
				<?php if ( $data->show_club_name ) : ?>
					<img loading="lazy" width="30" height="30" class="anwp-object-contain match-simple__team-logo anwp-flex-none mx-1 my-2 my-sm-0 anwp-w-30 anwp-h-30"
					src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="<?php echo esc_attr( $data->club_home_title ); ?>">
				<?php else : ?>
					<img loading="lazy" width="40" height="40" class="anwp-object-contain match-simple__team-logo-big anwp-flex-none mx-1 my-2 my-sm-0 align-self-center flex-grow-1 anwp-w-40 anwp-h-40"
					src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="<?php echo esc_attr( $data->club_home_title ); ?>">
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $data->show_club_name ) : ?>
				<div class="match-simple__team flex-grow-1 anwp-text-center mx-2 anwp-text-xs">
					<?php echo $data->club_home_title; ?>
					<?php //echo esc_html( anwp_football_leagues()->customizer->get_value( 'match_list', 'match_simple_team_name' ) ? $data->club_home_title : $data->club_home_abbr ); ?>
				</div>
			<?php endif; ?>

			<div class="match-list-item__scores-home anwp-fl-game__scores-home match-list-item__scores match-simple__scores-number anwp-text-base mr-0 anwp-text-center ml-auto match-simple__scores-number-status-<?php echo (int) $data->finished; ?>">
				<?php echo (int) $data->finished ? (int) $data->home_goals : '-:'; ?>
			</div>
		</div>


		<div class="anwp-col-6 d-flex align-items-center">
			<div class="match-list-item__scores-away anwp-fl-game__scores-away match-list-item__scores match-simple__scores-number anwp-text-base ml-1 anwp-text-center match-simple__scores-number-status-<?php echo (int) $data->finished; ?>">
				<?php echo (int) $data->finished ? (int) $data->away_goals : ':-'; ?>
			</div>

			<?php if ( $data->show_club_name ) : ?>
				<div class="match-simple__team flex-grow-1 anwp-text-center mx-2 anwp-text-xs">
					<?php echo $data->club_away_title; ?>
					<?php //echo esc_html( anwp_football_leagues()->customizer->get_value( 'match_list', 'match_simple_team_name' ) ? $data->club_away_title : $data->club_away_abbr ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $data->club_away_logo ) : ?>
				<?php if ( $data->show_club_name ) : ?>
					<img loading="lazy" width="30" height="30" class="anwp-object-contain match-simple__team-logo anwp-flex-none mx-1 my-2 my-sm-0 anwp-w-30 anwp-h-30"
					src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="<?php echo esc_attr( $data->club_away_title ); ?>">
				<?php else : ?>
					<img loading="lazy" width="40" height="40" class="anwp-object-contain match-simple__team-logo-big anwp-flex-none mx-1 my-2 my-sm-0 align-self-center flex-grow-1 anwp-w-40 anwp-h-40"
					src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="<?php echo esc_attr( $data->club_away_title ); ?>">
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>

	<?php
	$time_result = '';

	switch ( intval( $data->extra ) ) {
		case 1:
		$time_result = esc_html( AnWPFL_Text::get_value( 'match__match__aet', _x( 'AET', 'Abbr: after extra time', 'anwp-football-leagues' ) ) );
		break;
		case 2:
		$time_result  = esc_html( AnWPFL_Text::get_value( 'match__match__penalties', _x( 'Penalties', 'on penalties', 'anwp-football-leagues' ) ) );
		$time_result .= ' ' . $data->home_goals_p . '-' . $data->away_goals_p;
		break;
	}
	?>
	<?php if ( $time_result || $data->aggtext || in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) : ?>
		<div class="match-simple__time-result-wrapper anwp-text-center anwp-text-xs anwp-opacity-80 anwp-leading-1-25 mb-1">
			<?php
			if ( in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) {
				echo '<span class="match-simple__time-result d-inline-block anwp-text-nowrap">' . esc_html( anwp_football_leagues()->data->get_value_by_key( $data->special_status, 'special_status' ) ) . '</span>';
			}

			if ( $time_result ) {
				echo '<span class="match-simple__separated match-simple__time-result d-inline-block anwp-text-nowrap">' . esc_html( $time_result ) . '</span>';
			}

			if ( $data->aggtext ) {
				echo '<span class="match-simple__separated match-simple__time-result d-inline-block">' . esc_html( $data->aggtext ) . '</span>';
			}
			?>
		</div>
	<?php endif; ?>

	<?php
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
			data-game-datetime="<?php echo esc_attr( $countdown ); ?>">
			<div class="d-flex justify-content-center anwp-fl-game-countdown__inner">
				<div class="anwp-fl-game-countdown__item anwp-fl-game-countdown__days">
					<div class="anwp-fl-game-countdown__label" style="<?php echo esc_html( $label_style ); ?>">
						<?php //echo esc_html( AnWPFL_Text::get_value( 'data__flip_countdown__days', esc_html_x( 'days', 'flip countdown', 'anwp-football-leagues' ) ) ); ?>
						<?php echo $translate->translate('days') ?>
					</div>
					<div class="anwp-fl-game-countdown__value anwp-fl-game-countdown__value-days" style="<?php echo esc_html( $value_style ); ?>"></div>
				</div>
				<div class="anwp-fl-game-countdown__separator"></div>
				<div class="anwp-fl-game-countdown__item anwp-fl-game-countdown__hours">
					<div class="anwp-fl-game-countdown__label" style="<?php echo esc_html( $label_style ); ?>">
						<?php //echo esc_html( AnWPFL_Text::get_value( 'data__flip_countdown__hours', esc_html_x( 'hours', 'flip countdown', 'anwp-football-leagues' ) ) ); ?>
						<?php echo $translate->translate('hours') ?>						
					</div>
					<div class="anwp-fl-game-countdown__value anwp-fl-game-countdown__value-hours" style="<?php echo esc_html( $value_style ); ?>"></div>
				</div>
				<div class="anwp-fl-game-countdown__separator"></div>
				<div class="anwp-fl-game-countdown__item anwp-fl-game-countdown__minutes">
					<div class="anwp-fl-game-countdown__label" style="<?php echo esc_html( $label_style ); ?>">
						<?php //echo esc_html( AnWPFL_Text::get_value( 'data__flip_countdown__minutes', esc_html_x( 'minutes', 'flip countdown', 'anwp-football-leagues' ) ) ); ?>
						<?php echo $translate->translate('minutes') ?>
					</div>
					<div class="anwp-fl-game-countdown__value anwp-fl-game-countdown__value-minutes" style="<?php echo esc_html( $value_style ); ?>"></div>
				</div>
				<div class="anwp-fl-game-countdown__separator"></div>
				<div class="anwp-fl-game-countdown__item anwp-fl-game-countdown__seconds">
					<div class="anwp-fl-game-countdown__label" style="<?php echo esc_html( $label_style ); ?>">
						<?php //echo esc_html( AnWPFL_Text::get_value( 'data__flip_countdown__seconds', esc_html_x( 'seconds', 'flip countdown', 'anwp-football-leagues' ) ) ); ?>
						<?php echo $translate->translate('seconds') ?>
					</div>
					<div class="anwp-fl-game-countdown__value anwp-fl-game-countdown__value-seconds" style="<?php echo esc_html( $value_style ); ?>"></div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<a class="anwp-link-cover anwp-link-without-effects" href="<?php echo esc_url( $data->permalink ); ?>"></a>
</div>
