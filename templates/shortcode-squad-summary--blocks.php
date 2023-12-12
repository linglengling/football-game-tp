<?php
/**
 * The Template for displaying Club Squad (Subteams - Summary).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-squad-summary--blocks.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.5.0
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check required params
if ( empty( $data->club_id ) || empty( $data->season_id ) ) {
	return;
}

// Prevent errors with new params
$data = (object) wp_parse_args(
	$data,
	[
		'header'    => true,
		'club_id'   => '',
		'season_id' => '',
		'class'     => '',
	]
);

try {
	$squad_display = anwp_football_leagues()->club->get_squad_display_options( $data->club_id, $data->season_id );
	$subteam_list  = get_post_meta( $data->club_id, '_anwpfl_subteam_list', true );

	if ( ! empty( $subteam_list ) && is_array( $subteam_list ) ) {
		foreach ( $subteam_list as $subteam_item ) {
			$squad_data[] = [
				'team_id' => $subteam_item['subteam'],
				'title'   => $subteam_item['title'],
				'squad'   => anwp_football_leagues()->club->tmpl_prepare_club_squad( $subteam_item['subteam'], $data->season_id, true ),
				'staff'   => anwp_football_leagues()->club->tmpl_prepare_club_staff( $subteam_item['subteam'], $data->season_id ),
			];
		}
	}
} catch ( Exception $e ) {
	return;
}

// Initialize staff groups
$staff_group_attached = '';

$default_photo = anwp_football_leagues()->helper->get_default_player_photo();

// Prepare positions
$positions      = anwp_football_leagues()->data->get_positions_plural();
$positions_l10n = [
	'g' => anwp_football_leagues()->get_option_value( 'text_multiple_goalkeeper' ) ?: $positions['g'],
	'd' => anwp_football_leagues()->get_option_value( 'text_multiple_defender' ) ?: $positions['d'],
	'm' => anwp_football_leagues()->get_option_value( 'text_multiple_midfielder' ) ?: $positions['m'],
	'f' => anwp_football_leagues()->get_option_value( 'text_multiple_forward' ) ?: $positions['f'],
];
?>
<div class="anwp-b-wrap squad squad--shortcode <?php echo esc_attr( $data->class ); ?>">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'squad__shortcode__squad', __( 'Squad', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}

	if ( empty( $squad_data ) ) :
		anwp_football_leagues()->load_partial(
			[
				'no_data_text' => AnWPFL_Text::get_value( 'squad__shortcode__no_players_in_the_squad', __( 'No players in the squad', 'anwp-football-leagues' ) ),
			],
			'general/no-data'
		);
	else :
		$root_team_title = get_post_meta( $data->club_id, '_anwpfl_root_team_title', true );
		?>
		<div class="club-subteams pb-3 pt-1 d-flex flex-wrap">
			<div class="m-1 club-subteams__item club-subteams__squad anwp-fl-btn d-flex align-items-center position-relative py-0 club-subteams__item--active anwp-cursor-default"
				data-filter-value="">
				<?php echo esc_html( $root_team_title ); ?>
			</div>

			<?php foreach ( $subteam_list as $subteam_item ) : ?>
				<div class="m-1 club-subteams__item club-subteams__squad anwp-fl-btn d-flex align-items-center position-relative py-0" data-filter-value="<?php echo esc_attr( $subteam_item['subteam'] ); ?>">
					<?php echo esc_html( $subteam_item['title'] ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="anwp-grid-table squad-wrapper squad-blocks">
			<?php
			foreach ( $positions_l10n as $loop_key => $loop_title ) :

				/*
				|--------------------------------------------------------------------
				| Squad Header
				|--------------------------------------------------------------------
				*/
				if ( $squad_display->group ) :
					?>
					<div class="squad-blocks__header anwp-text-uppercase anwp-text-lg anwp-bg-light p-1"><?php echo esc_html( $loop_title ); ?></div>
					<?php
				endif;

				/*
				|--------------------------------------------------------------------
				| Squad Data
				|--------------------------------------------------------------------
				*/
				foreach ( $squad_data as $team_squad_data ) :
					foreach ( $team_squad_data['squad'] as $player_id => $player ) :
						if ( $player['position'] !== $loop_key && $squad_display->group ) {
							continue;
						}

						// Check player status. Do not show players "on trial" or "left"
						if ( in_array( $player['status'], [ 'left', 'on trial' ], true ) ) {
							continue;
						}
						?>
						<div class="squad-blocks__block position-relative d-flex flex-column anwp-border-light" data-filter="<?php echo esc_html( $team_squad_data['team_id'] ); ?>">
							<div class="squad-blocks__photo-wrapper anwp-text-center d-flex align-items-center anwp-bg-light p-4 anwp-border-light">
								<img loading="lazy" width="70" height="70" class="squad-blocks__photo anwp-object-contain anwp-w-70 anwp-h-70" src="<?php echo esc_url( $player['photo'] ?: $default_photo ); ?>" alt="<?php echo esc_attr( $player['name'] ); ?>">
								<div class="squad-blocks__player-number ml-auto anwp-text-4xl"><?php echo (int) $player['number'] ?: ''; ?></div>
							</div>
							<div class="d-flex flex-column position-relative px-4 mb-2">

								<div class="anwp-bg-gray px-2 py-0 mt-1 mb-1 mb-sm-0 mr-4 anwp-text-sm anwp-leading-1-25 mx-auto mt-n2">
									<?php echo esc_html( $team_squad_data['title'] ); ?>
								</div>

								<div class="squad-blocks__name mb-auto anwp-text-lg anwp-font-semibold"><?php echo esc_html( $player['name'] ); ?></div>

								<?php if ( $player['position'] ) : ?>
									<div class="squad-blocks__player-param d-flex anwp-border-light">
										<span class="squad-blocks__player-param-title anwp-text-sm anwp-opacity-70"><?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__position', __( 'Position', 'anwp-football-leagues' ) ) ); ?></span>
										<span class="squad-blocks__player-param-value ml-auto anwp-text-base"><?php echo esc_html( anwp_football_leagues()->data->get_value_by_key( $player['position'], 'position' ) ); ?></span>
									</div>
								<?php endif; ?>

								<?php if ( $player['age'] ) : ?>
									<div class="squad-blocks__player-param d-flex anwp-border-light">
										<span class="squad-blocks__player-param-title anwp-text-sm anwp-opacity-70"><?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__age', __( 'Age', 'anwp-football-leagues' ) ) ); ?></span>
										<span class="squad-blocks__player-param-value ml-auto anwp-text-base"><?php echo esc_html( $player['age'] ); ?></span>
									</div>
								<?php endif; ?>

								<?php if ( ! empty( $player['nationality'] ) && is_array( $player['nationality'] ) ) : ?>
									<div class="squad-blocks__player-param d-flex">
										<span class="squad-blocks__player-param-title anwp-text-sm anwp-opacity-70"><?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__nationality', __( 'Nationality', 'anwp-football-leagues' ) ) ); ?></span>
										<span class="squad-blocks__player-param-value ml-auto anwp-text-base">
										<?php
										foreach ( $player ['nationality'] as $country_code ) :
											anwp_football_leagues()->load_partial(
												[
													'class'        => 'options__flag',
													'size'         => 32,
													'country_code' => $country_code,
												],
												'general/flag'
											);
										endforeach;
										?>
									</span>
									</div>
								<?php endif; ?>
							</div>
							<a href="<?php echo esc_url( get_permalink( $player_id ) ); ?>" class="anwp-link-cover"></a>
						</div>
						<?php
					endforeach;
				endforeach;

				if ( ! $squad_display->group ) :
					break;
				endif;
			endforeach;

			foreach ( $squad_data as $team_squad_data ) :
				foreach ( $team_squad_data['staff'] as $staff_id => $staff_member ) :

					if ( 'no' !== $staff_member['grouping'] ) {
						continue;
					}

					if ( $staff_member['job'] !== $staff_group_attached ) :
						?>
						<div class="squad-blocks__header anwp-text-uppercase anwp-text-lg anwp-bg-light p-1"><?php echo esc_html( $staff_member['job'] ); ?></div>
						<?php $staff_group_attached = $staff_member['job']; ?>
					<?php endif; ?>

					<div class="squad-blocks__block position-relative d-flex flex-column anwp-border-light" data-filter="<?php echo esc_html( $team_squad_data['team_id'] ); ?>">
						<div class="squad-blocks__photo-wrapper anwp-text-center d-flex align-items-center anwp-bg-light p-4 anwp-border-light">
							<img loading="lazy" width="70" height="70" class="squad-blocks__photo anwp-object-contain anwp-w-70 anwp-h-70" src="<?php echo esc_url( $staff_member['photo'] ?: $default_photo ); ?>" alt="<?php echo esc_attr( $staff_member['name'] ); ?>">
						</div>
						<div class="d-flex flex-column position-relative px-4 mb-2">

							<div class="squad-blocks__name mb-auto anwp-text-lg anwp-font-semibold"><?php echo esc_html( $staff_member['name'] ); ?></div>

							<div class="anwp-bg-gray px-2 py-0 mt-1 mb-1 mb-sm-0 mr-4 anwp-text-sm anwp-leading-1-25 mx-auto mt-n2">
								<?php echo esc_html( $team_squad_data['title'] ); ?>
							</div>

							<?php if ( $staff_member['age'] ) : ?>
								<div class="squad-blocks__player-param d-flex anwp-border-light">
									<span class="squad-blocks__player-param-title anwp-text-sm anwp-opacity-70"><?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__age', __( 'Age', 'anwp-football-leagues' ) ) ); ?></span>
									<span class="squad-blocks__player-param-value ml-auto anwp-text-base"><?php echo esc_html( $staff_member['age'] ); ?></span>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $staff_member['nationality'] ) && is_array( $staff_member['nationality'] ) ) : ?>
								<div class="squad-blocks__player-param d-flex">
									<span class="squad-blocks__player-param-title anwp-text-sm anwp-opacity-70"><?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__nationality', __( 'Nationality', 'anwp-football-leagues' ) ) ); ?></span>
									<span class="squad-blocks__player-param-value ml-auto anwp-text-base">
									<?php
									foreach ( $staff_member ['nationality'] as $country_code ) :
										anwp_football_leagues()->load_partial(
											[
												'class'        => 'options__flag',
												'size'         => 32,
												'country_code' => $country_code,
											],
											'general/flag'
										);
									endforeach;
									?>
									</span>
								</div>
							<?php endif; ?>
						</div>
						<a href="<?php echo esc_url( get_permalink( $staff_id ) ); ?>" class="anwp-link-cover"></a>
					</div>
					<?php
				endforeach;
			endforeach;
			?>
		</div>
	<?php endif; ?>
</div>
