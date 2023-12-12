<?php
/**
 * The Template for displaying Widget Matches.
 * Layout: Modern
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/widget-matches--modern.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.4.4
 *
 * @version       0.14.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Prevent errors with new params
$data = (object) wp_parse_args(
	$data,
	[
		'show_club_logos'       => 1,
		'show_match_datetime'   => true,
		'club_links'            => true,
		'group_by_header_style' => '',
		'link_text'             => '',
		'link_target'           => '',
	]
);

// Get competition matches
$matches = anwp_football_leagues()->competition->tmpl_get_competition_matches_extended( $data );

?>
	<div class="anwp-b-wrap match-list match-list--widget layout--<?php echo esc_attr( $data->layout ); ?>">
		<?php
		$group_current = '';

		foreach ( $matches as $m_index => $match ) :

			if ( '' !== $data->group_by ) {

				$group_text = '';

				// Check current group by value
				if ( 'stage' === $data->group_by && $group_current !== $match->competition_id ) {
					$group_text    = get_post_meta( $match->competition_id, '_anwpfl_stage_title', true );
					$group_current = $match->competition_id;
				} elseif ( 'matchweek' === $data->group_by && $group_current !== $match->match_week && '0' !== $match->match_week ) {
					$group_text    = anwp_football_leagues()->competition->tmpl_get_matchweek_round_text( $match->match_week, $match->competition_id );
					$group_current = $match->match_week;
				} elseif ( 'day' === $data->group_by ) {
					$day_to_compare = date( 'Y-m-d', strtotime( $match->kickoff ) );

					if ( $day_to_compare !== $group_current ) {
						$group_text    = date_i18n( anwp_football_leagues()->get_option_value( 'custom_match_date_format' ) ?: 'j M Y', strtotime( $match->kickoff ) );
						$group_current = $day_to_compare;
					}
				} elseif ( 'month' === $data->group_by ) {
					$month_to_compare = date( 'Y-m', strtotime( $match->kickoff ) );

					if ( $month_to_compare !== $group_current ) {
						$group_text    = date_i18n( 'M Y', strtotime( $match->kickoff ) );
						$group_current = $month_to_compare;
					}
				}

				if ( $group_text ) {
					if ( 'secondary' === $data->group_by_header_style ) {
						anwp_football_leagues()->load_partial(
							[
								'text'  => esc_html( $group_text ),
								'class' => $m_index ? ' mt-4 mb-1' : 'mb-1',
							],
							'general/subheader'
						);
					} else {
						anwp_football_leagues()->load_partial(
							[
								'text'  => esc_html( $group_text ),
								'class' => $m_index ? ' mt-4' : '',
							],
							'general/header'
						);
					}
				}
			}

			$tmpl_data = array_merge( (array) $data, anwp_football_leagues()->match->prepare_match_data_to_render( $match, $data ) );
			anwp_football_leagues()->load_partial( $tmpl_data, 'match/match', 'modern' );
			?>
		<?php endforeach; ?>
	</div>
<?php if ( ! empty( $data->link_text ) && ! empty( $data->link_target ) ) : ?>
	<div class="position-relative anwp-fl-btn-outline anwp-text-sm w-100 match-list__link-btn mt-2">
		<?php echo esc_html( $data->link_text ); ?>
		<a href="<?php echo esc_url( get_permalink( (int) $data->link_target ) ); ?>" class="anwp-link-cover anwp-link-without-effects"></a>
	</div>
	<?php
endif;
