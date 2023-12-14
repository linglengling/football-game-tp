<?php
/**
 * The Template for displaying Matches.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-matches.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.4.3
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// $wpdb->prefix = 'wp_2_';
$wpdb->prefix = get_option('wm_prefix');
$wpdb->set_prefix($wpdb->prefix);

$args = (object) wp_parse_args(
	$data,
	[
		'competition_id'        => '',
		'show_secondary'        => 0,
		'season_id'             => '',
		'league_id'             => '',
		'group_id'              => '',
		'type'                  => '',
		'limit'                 => 0,
		'date_from'             => '',
		'date_to'               => '',
		'stadium_id'            => '',
		'filter_by'             => '',
		'filter_values'         => '',
		'filter_by_clubs'       => '',
		'filter_by_matchweeks'  => '',
		'days_offset'           => '',
		'days_offset_to'        => '',
		'sort_by_date'          => '',
		'sort_by_matchweek'     => '',
		'club_links'            => true,
		'priority'              => '',
		'class'                 => '',
		'group_by'              => '',
		'group_by_header_style' => '',
		'show_club_logos'       => 1,
		'show_match_datetime'   => true,
		'competition_logo'      => '1',
		'outcome_id'            => '',
		'exclude_ids'           => '',
		'include_ids'           => '',
		'no_data_text'          => '',
		'home_club'             => '',
		'away_club'             => '',
		'layout'                => 'slim',
		'header_class'          => '',
		'load_more_per_load'    => 20,
		'show_load_more'        => false,
	]
);

/*
|--------------------------------------------------------------------
| Prepare Load More
|--------------------------------------------------------------------
*/
$show_load_more = AnWP_Football_Leagues::string_to_bool( $args->show_load_more );

if ( $show_load_more && ( ! absint( $args->limit ) || $args->include_ids ) ) {
	$show_load_more = false;
}

if ( $show_load_more ) {
	$args->limit ++;
}

// Get competition matches
$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $args );

// Post getting grid posts
if ( $show_load_more ) {
	$args->limit --;
	$show_load_more = count( $matches ) > $args->limit;

	if ( $show_load_more ) {
		array_pop( $matches );
	}
}

// Update "load more"
$data->show_load_more = $show_load_more;

if ( empty( $matches ) ) {
	if ( trim( $args->no_data_text ) ) {
		anwp_football_leagues()->load_partial(
			[
				'no_data_text' => $args->no_data_text,
			],
			'general/no-data'
		);
	}

	return;
}

$slider = 1;

if($slider !== 1) :
	?>
	<div class="anwp-b-wrap match-list__outer-wrapper">
		<div class="match-list match-list--shortcode <?php echo esc_attr( $args->class ); ?>">
			<?php
			$group_current = '';

			foreach ( $matches as $ii => $list_match ) :

				if ( '' !== $args->group_by ) {

					$group_text = '';

				// Check current group by value
					if ( 'stage' === $args->group_by && $group_current !== $list_match->competition_id ) {
						$group_text    = get_post_meta( $list_match->competition_id, '_anwpfl_stage_title', true );
						$group_current = $list_match->competition_id;
					} elseif ( 'competition' === $args->group_by && $group_current !== $list_match->competition_id ) {
						$group_text    = anwp_football_leagues()->competition->get_competition_title( $list_match->competition_id );
						$group_current = $list_match->competition_id;
					} elseif ( 'matchweek' === $args->group_by && $group_current !== $list_match->match_week && '0' !== $list_match->match_week ) {
						$group_text    = anwp_football_leagues()->competition->tmpl_get_matchweek_round_text( $list_match->match_week, $list_match->competition_id );
						$group_current = $list_match->match_week;
					} elseif ( 'day' === $args->group_by ) {
						$day_to_compare = date( 'Y-m-d', strtotime( $list_match->kickoff ) );

						if ( $day_to_compare !== $group_current ) {
							$group_text    = '0000-00-00 00:00:00' === $list_match->kickoff ? '&nbsp;' : date_i18n( anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M Y', strtotime( $list_match->kickoff ) );
							$group_current = $day_to_compare;
						}
					} elseif ( 'month' === $args->group_by ) {
						$month_to_compare = date( 'Y-m', strtotime( $list_match->kickoff ) );

						if ( $month_to_compare !== $group_current ) {
							$group_text    = '0000-00-00 00:00:00' === $list_match->kickoff ? '&nbsp;' : date_i18n( 'M Y', strtotime( $list_match->kickoff ) );
							$group_current = $month_to_compare;
						}
					}

					if ( $group_text ) {
						if ( 'secondary' === $args->group_by_header_style ) {
							anwp_football_leagues()->load_partial(
								[
									'text'  => esc_html( $group_text ),
									'class' => $ii ? ' mt-4 mb-1' : 'mb-1',
								],
								'general/subheader'
							);
						} else {
							anwp_football_leagues()->load_partial(
								[
									'text'  => esc_html( $group_text ),
									'class' => $ii ? ' mt-4' : '',
								],
								'general/header'
							);
						}
					}
				}

			// Get match data to render
				$game_data = anwp_football_leagues()->match->prepare_match_data_to_render( $list_match, $args );

				$game_data['competition_logo'] = $args->competition_logo;
				$game_data['outcome_id']       = $args->outcome_id;

				anwp_football_leagues()->load_partial( $game_data, 'match/match', $args->layout ?: 'slim' );

			endforeach;
			?>
		</div>

		<?php if ( $show_load_more ) : ?>
			<div class="anwp-b-wrap anwp-fl-btn-wrapper d-flex justify-content-center mt-3">
				<div class="anwp-fl-btn anwp-cursor-pointer anwp-fl-btn__load-more d-flex align-items-center"
				data-fl-loaded-qty="<?php echo absint( count( $matches ) ); ?>"
				data-fl-group="<?php echo esc_attr( $group_current ); ?>"
				data-fl-games-per-load="<?php echo absint( $args->load_more_per_load ); ?>"
				data-fl-load-more="<?php echo esc_attr( anwp_football_leagues()->match->get_serialized_load_more_data( $args ) ); ?>"
				>
				<?php echo esc_html( AnWPFL_Text::get_value( 'general__load_more', __( 'Load More', 'anwp-football-leagues' ) ) ); ?>
				<img class="ml-2 my-n2 anwp-fl-spinner" src="<?php echo esc_url( admin_url( '/images/spinner.gif' ) ); ?>" alt="spinner">
			</div>
		</div>
	<?php endif; ?>
</div>

<?php else : ?>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" referrerpolicy="no-referrer" />

	<div class="anwp-b-wrap match-list__outer-wrapper flexslider">
		<div class="wm-match-list match-list--slider owl-carousel">
			<?php
			$group_current = '';

			foreach ( $matches as $ii => $list_match ) :
				
				$game_data = anwp_football_leagues()->match->prepare_match_data_to_render( $list_match, $args );

				$game_data['competition_logo'] = $args->competition_logo;
				$game_data['outcome_id']       = $args->outcome_id;

				anwp_football_leagues()->load_partial( $game_data, 'match/match', 'slider' );

			endforeach;
			?>
		</div>		
	</div>

<?php endif; ?>
