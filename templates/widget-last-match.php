<?php
/**
 * The Template for displaying Last Match.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/widget-last-match.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.10.13
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check for required data
if ( empty( $data->club_id ) && empty( $data->competition_id ) ) {
	return;
}

// Prevent errors with new params
$args = (object) wp_parse_args(
	$data,
	[
		'club_id'         => '',
		'competition_id'  => '',
		'season_id'       => '',
		'match_link_text' => '',
		'show_club_name'  => 1,
		'exclude_ids'     => '',
		'include_ids'     => '',
		'max_size'        => '',
		'offset'          => '',
		'transparent_bg'  => '',
	]
);

// Get competition matches
$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended(
	[
		'competition_id'  => $args->competition_id,
		'season_id'       => $args->season_id,
		'show_secondary'  => 1,
		'type'            => 'result',
		'filter_by_clubs' => $args->club_id,
		'limit'           => 1,
		'sort_by_date'    => 'desc',
		'exclude_ids'     => $args->exclude_ids,
		'offset'          => $args->offset,
		'include_ids'     => $args->include_ids,
	]
);

if ( empty( $matches ) || empty( $matches[0]->match_id ) ) {
	return;
}

$data      = (object) anwp_football_leagues()->match->prepare_match_data_to_render( $matches[0], [], 'widget', 'full' );
$show_name = AnWP_Football_Leagues::string_to_bool( $args->show_club_name );

/*
|--------------------------------------------------------------------
| Extra & Penalty
|--------------------------------------------------------------------
*/
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

$transparent_bg = AnWP_Football_Leagues::string_to_bool( $args->transparent_bg );

// Max Size
$image_size_style = '';

if ( absint( $args->max_size ) ) {
	$image_size_style = 'width: ' . absint( $args->max_size ) . 'px; height: ' . absint( $args->max_size ) . 'px;';
}
?>
<div class="anwp-b-wrap match-widget py-3 <?php echo $transparent_bg ? '' : 'anwp-bg-light'; ?>">

	<?php if ( $data->stadium_id ) : ?>
		<div class="match-widget__stadium anwp-text-center anwp-opacity-80 anwp-text-xs d-flex flex-wrap align-items-center justify-content-center mb-2">
			<svg class="anwp-icon anwp-icon--octi mr-1 anwp-icon--s12">
				<use xlink:href="#icon-location"></use>
			</svg>
			<?php
			// Stadium name
			echo esc_html( get_the_title( $data->stadium_id ) );

			// Stadium city
			$stadium_city = get_post_meta( $data->stadium_id, '_anwpfl_city', true );
			echo $stadium_city ? esc_html( ', ' . $stadium_city ) : '';
			?>
		</div>
	<?php endif; ?>

	<div class="match-widget__competition anwp-text-center anwp-text-sm">
		<?php echo esc_html( $data->stage_title ? ( $data->stage_title . ' - ' ) : '' ); ?>
		<?php echo esc_html( get_post( (int) $data->competition_id )->post_title ); ?>
	</div>

	<div class="match-widget__kickoff anwp-text-center anwp-text-sm" data-fl-game-datetime="<?php echo esc_attr( $data->kickoff_c ); ?>" data-fl-date-format="v3">
		<?php
		if ( $data->kickoff && '0000-00-00 00:00:00' !== $data->kickoff ) {

			$date_format = anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M';
			$time_format = anwp_football_leagues()->get_option_value( 'custom_match_time_format' ) ?: get_option( 'time_format' );

			echo '<span class="match__date-formatted">' . esc_html( date_i18n( $date_format, get_date_from_gmt( $data->kickoff, 'U' ) ) ) . '</span><span class="mx-1"></span>';
			echo '<span class="match__time-formatted">' . esc_html( date_i18n( $time_format, get_date_from_gmt( $data->kickoff, 'U' ) ) ) . '</span>';
		}
		?>
	</div>

	<div class="match-widget__clubs d-flex my-3">
		<div class="anwp-flex-1 d-flex flex-column align-items-center anwp-text-center anwp-min-width-0 px-1">
			<?php if ( $show_name ) : ?>
				<img loading="lazy"
						class="match-widget__club-logo anwp-object-contain my-2 <?php echo $image_size_style ? '' : 'anwp-w-50 anwp-h-50'; ?>"
						style="<?php echo esc_html( $image_size_style ); ?>"
						src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="<?php echo esc_attr( $data->club_home_title ); ?>">
				<div class="match-widget__club-title anwp-text-sm">
					<?php echo esc_html( $data->club_home_title ); ?>
				</div>
			<?php else : ?>
				<img loading="lazy" class="match-widget__club-logo anwp-object-contain my-2 <?php echo $image_size_style ? '' : 'anwp-w-50 anwp-h-50'; ?>"
						style="<?php echo esc_html( $image_size_style ); ?>"
						src="<?php echo esc_url( $data->club_home_logo ); ?>" alt="<?php echo esc_attr( $data->club_home_title ); ?>"
						data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $data->club_home_title ); ?>">
			<?php endif; ?>
		</div>
		<div class="anwp-flex-none align-self-center match-widget__scores d-flex anwp-text-base">
			<span class="match-widget__scores-number match-widget__scores--home mr-1 match-widget__scores-number-status-<?php echo (int) $data->finished; ?>"><?php echo (int) $data->home_goals; ?></span>
			<span class="match-widget__scores-number match-widget__scores--away match-widget__scores-number-status-<?php echo (int) $data->finished; ?>"><?php echo (int) $data->away_goals; ?></span>
		</div>
		<div class="anwp-flex-1 d-flex flex-column align-items-center anwp-text-center anwp-min-width-0 px-1">
			<?php if ( $show_name ) : ?>
				<img loading="lazy" class="match-widget__club-logo anwp-object-contain my-2 <?php echo $image_size_style ? '' : 'anwp-w-50 anwp-h-50'; ?>"
						style="<?php echo esc_html( $image_size_style ); ?>"
						src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="<?php echo esc_attr( $data->club_away_title ); ?>">
				<div class="match-widget__club-title anwp-text-sm">
					<?php echo esc_html( $data->club_away_title ); ?>
				</div>
			<?php else : ?>
				<img loading="lazy" class="match-widget__club-logo anwp-object-contain my-2 <?php echo $image_size_style ? '' : 'anwp-w-50 anwp-h-50'; ?>"
						style="<?php echo esc_html( $image_size_style ); ?>"
						src="<?php echo esc_url( $data->club_away_logo ); ?>" alt="<?php echo esc_attr( $data->club_away_title ); ?>"
						data-toggle="anwp-tooltip" data-tippy-content="<?php echo esc_attr( $data->club_away_title ); ?>">
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $time_result ) : ?>
		<div class="match-widget__extra anwp-text-center mt-1 anwp-text-sm anwp-leading-1-25">
			<?php echo esc_html( $time_result ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $args->match_link_text ) : ?>
		<div class="anwp-text-center anwp-match-preview-link mt-1 anwp-text-sm">
			<a href="<?php echo esc_url( get_permalink( (int) $data->match_id ) ); ?>" class="anwp-link-without-effects match-widget__link-preview">
				<?php echo esc_html( $args->match_link_text ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
