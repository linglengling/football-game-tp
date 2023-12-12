<?php
/**
 * The Template for displaying Match.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.6.1
 *
 * @version       0.14.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'show_match_datetime' => true,
		'kickoff'             => '',
		'kickoff_c'           => '',
		'match_date'          => '',
		'match_time'          => '',
		'club_links'          => true,
		'home_club'           => '',
		'away_club'           => '',
		'club_home_title'     => '',
		'club_away_title'     => '',
		'club_home_link'      => '',
		'club_away_link'      => '',
		'club_home_logo'      => '',
		'club_away_logo'      => '',
		'match_id'            => '',
		'finished'            => '',
		'home_goals'          => '',
		'away_goals'          => '',
		'match_week'          => '',
		'stadium_id'          => '',
		'competition_id'      => '',
		'main_stage_id'       => '',
		'stage_title'         => '',
		'attendance'          => '',
		'aggtext'             => '',
		'home_goals_half'     => '',
		'away_goals_half'     => '',
		'home_goals_p'        => '',
		'away_goals_p'        => '',
		'home_goals_ft'       => '',
		'away_goals_ft'       => '',
		'referee_id'          => '',
		'special_status'      => '',
		'context'             => 'shortcode',
	]
);
?>

<div class="match-header match-status__<?php echo esc_attr( $data->finished ); ?> anwp-section anwp-bg-light p-2"
	data-fl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>">
	<div class="match-header__top px-3 pb-2 anwp-leading-1-5">
		<div class="anwp-text-center match-header__date">
			<?php
			if ( ( $data->show_match_datetime && '0000-00-00 00:00:00' !== $data->kickoff ) ) {
				$date_format = anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M Y';

				if ( 'TBD' === $data->special_status ) {
					echo '<span class="match__date-formatted">' . esc_html( date_i18n( $date_format, get_date_from_gmt( $data->kickoff, 'U' ) ) ) . '</span>';
				} else {
					$time_format = anwp_football_leagues()->get_option_value( 'custom_match_time_format' ) ?: get_option( 'time_format' );
					echo '<span class="match__date-formatted">' . esc_html( date_i18n( $date_format, get_date_from_gmt( $data->kickoff, 'U' ) ) ) . '</span><span class="mx-1">-</span>';
					echo '<span class="match__time-formatted">' . esc_html( date_i18n( $time_format, get_date_from_gmt( $data->kickoff, 'U' ) ) ) . '</span>';
				}
			}
			?>
		</div>
		<div class="anwp-text-center match-header__competition">
			<a class="match-header__competition-link anwp-link anwp-link-without-effects"
				href="<?php echo esc_url( get_permalink( (int) $data->main_stage_id ? : (int) $data->competition_id ) ); ?>">
				<?php echo esc_html( $data->stage_title ? ( $data->stage_title . ' - ' ) : '' ); ?>
				<?php echo esc_html( get_the_title( (int) $data->competition_id ) ); ?>
			</a>
			<?php echo esc_html( anwp_football_leagues()->competition->tmpl_get_matchweek_round_text( $data->match_week, $data->competition_id, ' | ' ) ); ?>
		</div>
		<div class="anwp-text-center match-header__period">
			<?php if ( '1' === $data->finished ) : ?>
				<?php
				$sup_texts = [];

				if ( apply_filters( 'anwpfl/match/show_half_time_score', true ) ) {
					$sup_texts[] = esc_html( AnWPFL_Text::get_value( 'match__match__half_time', __( 'Half Time', 'anwp-football-leagues' ) ) ) . ': ' . $data->home_goals_half . '-' . $data->away_goals_half;
				}

				// Full Time
				if ( apply_filters( 'anwpfl/match/show_full_time_score', true ) && ( '1' === $data->extra || '2' === $data->extra ) ) {
					$sup_texts[] = esc_html( AnWPFL_Text::get_value( 'match__match__full_time', __( 'Full Time', 'anwp-football-leagues' ) ) ) . ': ' . $data->home_goals_ft . '-' . $data->away_goals_ft;
				}

				// Aggregate Text
				if ( $data->aggtext ) {
					$sup_texts[] = $data->aggtext;
				}
				?>

				<?php foreach ( $sup_texts as $text ) : ?>
					<span class="match-header__period-item mx-3"><?php echo esc_html( $text ); ?></span>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="match-header__main d-sm-flex">
		<div class="match-header__team-wrapper match-header__team-home anwp-flex-1 d-flex flex-sm-column align-items-center position-relative mb-3 mb-sm-0">
			<?php if ( $data->club_home_logo ) : ?>
				<img loading="lazy" width="80" height="80" class="anwp-object-contain match-header__team-logo anwp-flex-none mb-0 mx-3 anwp-w-80 anwp-h-80"
					src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="<?php echo esc_attr( $data->club_home_title ); ?>">
			<?php endif; ?>

			<div class="match-header__team-title anwp-text-xl anwp-leading-1 anwp-font-semibold mx-3 anwp-text-sm-center my-3 anwp-break-word">
				<?php echo esc_html( $data->club_home_title ); ?>
			</div>

			<?php if ( '1' === $data->finished ) : ?>
				<div class="match-header__scores-wrapper anwp-font-semibold d-inline-block d-sm-none ml-auto px-3">
					<span class="match__scores-number"><?php echo (int) $data->home_goals; ?></span>
				</div>
			<?php endif; ?>
			<a class="anwp-link-cover anwp-link-without-effects anwp-cursor-pointer" href="<?php echo esc_url( $data->club_home_link ); ?>"></a>
		</div>

		<?php if ( '1' === $data->finished ) : ?>
			<div class="match-header__scores-wrapper d-sm-flex align-items-center mx-2 mx-sm-4 anwp-font-semibold d-none">
				<?php if ( 'shortcode' === $data->context ) : ?>
					<a href="<?php echo esc_url( get_permalink( (int) $data->match_id ) ); ?>" class="anwp-link-without-effects">
						<span class="match__scores-number d-inline-block"><?php echo (int) $data->home_goals; ?></span>
						<span class="match__scores-number-separator d-inline-block mx-1">:</span>
						<span class="match__scores-number d-inline-block"><?php echo (int) $data->away_goals; ?></span>
					</a>
				<?php else : ?>
					<span class="match__scores-number d-inline-block"><?php echo (int) $data->home_goals; ?></span>
					<span class="match__scores-number-separator d-inline-block mx-1">:</span>
					<span class="match__scores-number d-inline-block"><?php echo (int) $data->away_goals; ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="match-header__team-wrapper match-header__team-away anwp-flex-1 d-flex flex-sm-column align-items-center position-relative">
			<?php if ( $data->club_away_logo ) : ?>
				<img loading="lazy" width="80" height="80" class="anwp-object-contain match-header__team-logo anwp-flex-none mb-0 mx-3 anwp-w-80 anwp-h-80"
					src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="<?php echo esc_attr( $data->club_away_title ); ?>">
			<?php endif; ?>

			<div class="match-header__team-title anwp-text-xl anwp-leading-1 anwp-font-semibold mx-3 anwp-text-sm-center my-3 anwp-break-word">
				<?php echo esc_html( $data->club_away_title ); ?>
			</div>

			<?php if ( '1' === $data->finished ) : ?>
				<div class="match-header__scores-wrapper anwp-font-semibold d-inline-block d-sm-none ml-auto px-3">
					<span class="match__scores-number"><?php echo (int) $data->away_goals; ?></span>
				</div>
			<?php endif; ?>
			<a class="anwp-link-cover anwp-link-without-effects anwp-cursor-pointer" href="<?php echo esc_url( $data->club_away_link ); ?>"></a>
		</div>
	</div>

	<?php if ( '1' === $data->finished ) : ?>
		<div class="match-header__outcome anwp-text-center pb-2">
			<span class="match-header__outcome-text anwp-border-light">
				<?php
				if ( 'yes' === get_post_meta( $data->match_id, '_anwpfl_custom_outcome', true ) && ! empty( get_post_meta( $data->match_id, '_anwpfl_outcome_text', true ) ) ) {
					echo esc_html( get_post_meta( $data->match_id, '_anwpfl_outcome_text', true ) );
				} else {
					$time_result = esc_html( AnWPFL_Text::get_value( 'match__match__full_time', __( 'Full Time', 'anwp-football-leagues' ) ) );

					switch ( intval( $data->extra ) ) {
						case 1:
							$time_result = esc_html( AnWPFL_Text::get_value( 'match__match__aet', _x( 'AET', 'Abbr: after extra time', 'anwp-football-leagues' ) ) );
							break;
						case 2:
							$time_result  = esc_html( AnWPFL_Text::get_value( 'match__match__penalties', _x( 'Penalties', 'on penalties', 'anwp-football-leagues' ) ) );
							$time_result .= ' ' . $data->home_goals_p . '-' . $data->away_goals_p;
							break;
					}
					echo esc_html( $time_result );
				}
				?>
			</span>
		</div>
	<?php endif; ?>

	<?php if ( '0' === $data->finished && in_array( $data->special_status, [ 'PST', 'CANC' ], true ) ) : ?>
		<div class="match-header__special-status anwp-text-center anwp-text-uppercase pb-2">
			<span class="match-header__special-status-text px-1 py-0"><?php echo esc_html( anwp_football_leagues()->data->get_value_by_key( $data->special_status, 'special_status' ) ); ?></span>
		</div>
	<?php endif; ?>

	<?php
	if ( '0' === $data->finished ) :
		anwp_football_leagues()->load_partial( $data, 'match/match-countdown', 'modern' );

		if ( 'shortcode' === $data->context ) :
			?>
			<div class="anwp-text-center anwp-match-preview-link py-3">
				<a href="<?php echo esc_url( get_permalink( (int) $data->match_id ) ); ?>" class="anwp-link-without-effects">
					<span class="d-inline-block"><?php echo esc_html( AnWPFL_Text::get_value( 'match__match__match_preview', __( '- match preview -', 'anwp-football-leagues' ) ) ); ?></span>
				</a>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( $data->stadium_id || $data->referee_id ) : ?>
		<div class="match-header__bottom d-sm-flex mt-2 justify-content-center flex-wrap pt-2">
			<?php
			// Match stadium
			$stadium = intval( $data->stadium_id ) ? get_post( $data->stadium_id ) : null;

			if ( $stadium && 'publish' === $stadium->post_status ) :
				?>
				<div class="match__stadium mx-3 anwp-text-sm d-flex align-items-center justify-content-center">
					<svg class="anwp-icon anwp-icon--octi mr-2 anwp-w-20 anwp-fill-current">
						<use xlink:href="#icon-stadium"></use>
					</svg>
					<a class="match__stadium-title anwp-link anwp-link-without-effects" href="<?php echo esc_url( get_permalink( $stadium ) ); ?>">
						<?php echo esc_html( $stadium->post_title ); ?>
					</a>
				</div>
				<?php
				if ( (int) $data->attendance ) :
					echo ' (' . esc_html( number_format_i18n( (int) $data->attendance ) ) . ')';
				endif;
			endif;

			if ( (int) $data->referee_id ) :
				?>
				<div class="match__referee mx-3 anwp-text-sm d-flex align-items-center justify-content-center">
					<svg class="anwp-icon anwp-icon--octi mr-1 anwp-w-20 anwp-fill-current">
						<use xlink:href="#icon-whistle"></use>
					</svg>
					<?php echo esc_html( get_the_title( $data->referee_id ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php
	do_action( 'anwpfl/match/match-header-bottom', $data->match_id, $data );
	?>
</div>


