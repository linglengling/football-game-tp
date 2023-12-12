<?php
/**
 * The Template for displaying Competition Header Shortcode.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-competition_header.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.5.1
 * @since         0.7.4 Added link wrapper
 *
 * @version       0.15.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check for required data
if ( empty( $data->id ) ) {
	return;
}

$data = (object) wp_parse_args(
	$data,
	[
		'title_as_link'   => 0,
		'title'           => '',
		'season_selector' => 0,
		'transparent_bg'  => 0,
		'title_field'     => '', // league name (league - default) or competition title (competition)
	]
);

$season_selector = apply_filters( 'anwpfl/competition/show_season_selector', AnWP_Football_Leagues::string_to_bool( $data->season_selector ) );
$season_options  = [];

$competition_obj = anwp_football_leagues()->competition->get_competition( $data->id );
$terms           = anwp_football_leagues()->competition->tmpl_get_competition_terms( $data->id );
$logo            = get_post_meta( $data->id, '_anwpfl_logo_big', true ) ?: $competition_obj->logo;

// Prepare season data
if ( ! empty( $terms['season_title'] ) && is_array( $terms['season_title'] ) ) {
	if ( count( $terms['season_title'] ) > 1 ) {
		$seasons = [];

		foreach ( $terms['season_title'] as $season_title ) {
			$seasons = wp_parse_id_list( array_merge( $seasons, explode( '-', $season_title ) ) );
		}

		sort( $seasons, SORT_NUMERIC );

		if ( count( $seasons ) > 1 ) {
			$terms['season_title'] = $seasons[0] . '-' . $seasons[ count( $seasons ) - 1 ];
		} elseif ( count( $seasons ) > 0 ) {
			$terms['season_title'] = $seasons[0];
		} else {
			$terms['season_title'] = '';
		}
	} else {
		$terms['season_title'] = $terms['season_title'][0];
	}
} else {
	$terms['season_title'] = '';
}

/*
|--------------------------------------------------------------------
| Season Selector
|--------------------------------------------------------------------
*/
if ( $season_selector && absint( $competition_obj->league_id ) && absint( $competition_obj->season_ids ) && 1 === count( wp_parse_id_list( $competition_obj->season_ids ) ) ) {

	$season_all = anwp_football_leagues()->season->get_seasons_options();

	foreach ( anwp_football_leagues()->competition->get_competitions() as $competition_item ) {
		if ( absint( $competition_obj->league_id ) === absint( $competition_item->league_id ) && 'secondary' !== $competition_item->multistage && isset( $season_all[ $competition_item->season_ids ] ) ) {
			$season_options[] = [
				'id'        => $competition_item->id,
				'season'    => $season_all[ $competition_item->season_ids ],
				'permalink' => get_permalink( $competition_item->id ),
			];
		}
	}

	if ( ! empty( $season_options ) ) {
		$season_options = wp_list_sort( $season_options, 'season', 'DESC' );
	}
}

$transparent_bg = AnWP_Football_Leagues::string_to_bool( $data->transparent_bg );

/*
|--------------------------------------------------------------------------
| Prepare link
|--------------------------------------------------------------------------
*/
$link_post_id = 0;

if ( AnWP_Football_Leagues::string_to_bool( $data->title_as_link ) ) {
	$link_post_id = anwp_football_leagues()->competition->get_main_competition_id( $data->id );
}

$competition_title = empty( $data->title ) ? ( 'competition' === $data->title_field ? $competition_obj->title : $terms['league_title'] ) : $data->title;
?>
<div class="anwp-b-wrap competition-header p-3 position-relative anwp-section d-sm-flex align-items-center <?php echo $transparent_bg ? '' : 'anwp-bg-light'; ?>">

	<?php if ( $logo ) : ?>
		<div class="competition-header__logo-wrapper anwp-flex-sm-none anwp-text-center mb-3 mb-sm-0">
			<img loading="lazy" width="100" height="100" class="anwp-object-contain mr-sm-3 competition-header__logo anwp-w-100 anwp-h-100"
				src="<?php echo esc_attr( $logo ); ?>"
				alt="<?php echo esc_html( $competition_title ); ?>">
		</div>
	<?php endif; ?>

	<div class="competition-header__title-wrapper anwp-text-center anwp-text-sm-left">
		<h2 class="competition-header__title mb-2 mt-2 anwp-text-3xl"><?php echo esc_html( $competition_title ); ?></h2>
		<div class="competition-header__sub-title anwp-text-sm anwp-opacity-80">
			<?php if ( ! empty( $season_options ) && count( $season_options ) > 1 ) : ?>
				<select class="anwp-fl-season-dropdown anwp-text-sm">
					<?php foreach ( $season_options as $season_item ) : ?>
						<option <?php selected( $season_item['id'], $competition_obj->id ); ?>
							data-href="<?php echo esc_url( $season_item['permalink'] ); ?>"
							value="<?php echo esc_attr( $season_item['id'] ); ?>"><?php echo esc_attr( $season_item['season'] ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php else : ?>
				<?php echo esc_html( $terms['season_title'] ); ?>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( $link_post_id ) : ?>
		<a href="<?php echo esc_url( get_permalink( $link_post_id ) ); ?>" class="anwp-link-cover anwp-link-without-effects"></a>
	<?php endif; ?>
</div>
