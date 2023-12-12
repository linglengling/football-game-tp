<?php
/**
 * The Template for displaying Club Squad (Subteams - Summary).
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-squad-summary.php.
 *
 * @var object $data - Object with shortcode data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.5.0
 *
 * @version       0.14.14
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
		'class'     => 'mt-4',
		'season_id' => '',
		'club_id'   => '',
		'header'    => true,
	]
);

try {
	$squad_data    = [];
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

// Default photo
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
		<div class="anwp-grid-table squad-rows squad-wrapper anwp-text-center anwp-border-light">
			<?php
			foreach ( $positions_l10n as $position_slug => $position_title ) :

				/*
				|--------------------------------------------------------------------
				| Squad Header
				|--------------------------------------------------------------------
				*/
				if ( $squad_display->group ) :
					?>
					<div class="squad-rows__header-title anwp-text-lg anwp-bg-light anwp-text-left px-3 py-1">
						<?php echo esc_html( $position_title ); ?>
					</div>
					<div class="squad-rows__header-param anwp-text-sm anwp-bg-light py-1 d-flex align-items-center justify-content-center px-2 anwp-grid-table__sm-none">
						<?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__age', __( 'Age', 'anwp-football-leagues' ) ) ); ?>
					</div>
					<div class="squad-rows__header-param anwp-text-sm anwp-bg-light py-1 d-flex justify-content-center align-items-center px-2 anwp-grid-table__sm-none">
						<?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__nationality', __( 'Nationality', 'anwp-football-leagues' ) ) ); ?>
					</div>
					<?php
				endif;

				/*
				|--------------------------------------------------------------------
				| Squad Data
				|--------------------------------------------------------------------
				*/
				foreach ( $squad_data as $team_squad_data ) :
					foreach ( $team_squad_data['squad'] as $player_id => $player ) :
						if ( $player['position'] !== $position_slug && $squad_display->group ) {
							continue;
						}

						// Check player status. Do not show players "on trial" or "left"
						if ( in_array( $player['status'], [ 'left', 'on trial' ], true ) ) {
							continue;
						}
						?>

						<div class="squad-rows__number anwp-bg-secondary anwp-text-white px-2 anwp-text-3xl d-flex align-items-center justify-content-center"
							data-filter="<?php echo esc_html( $team_squad_data['team_id'] ); ?>">
							<?php echo (int) $player['number'] ?: ''; ?>
						</div>
						<div class="squad-rows__photo-wrapper px-2 position-relative" data-filter="<?php echo esc_html( $team_squad_data['team_id'] ); ?>">
							<img loading="lazy" width="60" height="60" class="squad-rows__photo anwp-object-contain m-2 anwp-w-60 anwp-h-60" src="<?php echo esc_url( $player['photo'] ?: $default_photo ); ?>" alt="<?php echo esc_attr( $player['name'] ); ?>">
						</div>
						<div class="squad-rows__name d-flex flex-column align-items-start justify-content-center anwp-text-base anwp-text-left anwp-font-semibold"
							data-filter="<?php echo esc_html( $team_squad_data['team_id'] ); ?>">
							<a href="<?php echo esc_url( get_permalink( $player_id ) ); ?>" class="anwp-link-without-effects">
								<?php
								$player_name_arr = explode( ' ', $player['name'], 2 );
								echo '<span class="squad-rows__name-1">' . esc_html( $player_name_arr[0] ) . '</span>';
								echo ! empty( $player_name_arr[1] ) ? ( '<span class="squad-rows__name-2">' . esc_html( $player_name_arr[1] ) . '</span>' ) : '';
								?>
							</a>

							<div class="anwp-bg-gray px-2 py-0 mt-1 mb-1 mb-sm-0 mr-4 anwp-text-xs anwp-leading-1-25">
								<?php echo esc_html( $team_squad_data['title'] ); ?>
							</div>

							<?php if ( ! $squad_display->group && $player['position'] ) : ?>
								<div class="anwp-grid-table__sm-none anwp-opacity-70 anwp-text-sm">
									<?php echo esc_html( anwp_football_leagues()->player->get_translated_position( $player_id, $player['position'] ) ); ?>
								</div>
							<?php endif; ?>

							<div class="d-none anwp-grid-table__sm-flex align-items-center">
								<?php
								if ( ! empty( $player['nationality'] ) && is_array( $player['nationality'] ) ) :
									foreach ( $player['nationality'] as $country_code ) :
										anwp_football_leagues()->load_partial(
											[
												'class'        => 'options__flag mr-3',
												'size'         => 32,
												'country_code' => $country_code,
											],
											'general/flag'
										);
									endforeach;
								endif;
								?>
								<?php if ( ! $squad_display->group && $player['position'] ) : ?>
									<div class="anwp-opacity-70">
										<?php echo esc_html( anwp_football_leagues()->player->get_translated_position( $player_id, $player['position'] ) ); ?>
									</div>
									<div class="anwp-text-xs anwp-opacity-30 mx-2">|</div>
								<?php endif; ?>

								<div class="squad-rows__age-title-mobile mr-1 anwp-opacity-70">
									<?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__age', __( 'Age', 'anwp-football-leagues' ) ) ); ?>:
								</div>
								<div class="squad-rows__age-mobile anwp-text-lg"><?php echo esc_html( $player['age'] ?: '-' ); ?></div>
							</div>
						</div>
						<div class="squad-rows__age px-2 pt-2 anwp-text-xl anwp-grid-table__sm-none" data-filter="<?php echo esc_html( $team_squad_data['team_id'] ); ?>">
							<?php echo esc_html( $player['age'] ?: '-' ); ?>
						</div>
						<div class="squad-rows__nationality px-2 pt-1 d-flex flex-column anwp-grid-table__sm-none"
							data-filter="<?php echo esc_html( $team_squad_data['team_id'] ); ?>">
							<?php
							if ( ! empty( $player['nationality'] ) && is_array( $player['nationality'] ) ) :
								foreach ( $player['nationality'] as $country_code ) :
									anwp_football_leagues()->load_partial(
										[
											'class'        => 'options__flag',
											'size'         => 32,
											'country_code' => $country_code,
										],
										'general/flag'
									);
								endforeach;
							endif;
							?>
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
						/*
						|--------------------------------------------------------------------
						| Squad Header
						|--------------------------------------------------------------------
						*/
						?>
						<div class="squad-rows__header-title anwp-text-lg anwp-bg-light anwp-text-left px-3 py-1">
							<?php echo esc_html( $staff_member['job'] ); ?>
						</div>
						<div class="squad-rows__header-param anwp-text-sm anwp-bg-light py-1 d-flex align-items-center justify-content-center px-2 anwp-grid-table__sm-none">
							<?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__age', __( 'Age', 'anwp-football-leagues' ) ) ); ?>
						</div>
						<div class="squad-rows__header-param anwp-text-sm anwp-bg-light py-1 d-flex justify-content-center align-items-center px-2 anwp-grid-table__sm-none">
							<?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__nationality', __( 'Nationality', 'anwp-football-leagues' ) ) ); ?>
						</div>
						<?php $staff_group_attached = $staff_member['job']; ?>
					<?php endif; ?>

					<div class="squad-rows__number anwp-bg-secondary anwp-text-white px-2 anwp-text-3xl d-flex align-items-center justify-content-center" data-filter="<?php echo esc_html( $team_squad_data['team_id'] ); ?>"></div>
					<div class="squad-rows__photo-wrapper px-2 d-flex flex-row" data-filter="<?php echo esc_html( $team_squad_data['team_id'] ); ?>">
						<img loading="lazy" width="60" height="60" class="squad-rows__photo anwp-object-contain m-2 anwp-w-60 anwp-h-60" src="<?php echo esc_url( $staff_member['photo'] ?: $default_photo ); ?>" alt="<?php echo esc_attr( $staff_member['name'] ); ?>">
					</div>
					<div class="squad-rows__name d-flex flex-column align-items-start justify-content-center anwp-text-base anwp-text-left anwp-font-semibold" data-filter="<?php echo esc_html( $team_squad_data['team_id'] ); ?>">
						<a href="<?php echo esc_url( get_permalink( $staff_id ) ); ?>" class="anwp-link-without-effects">
							<?php
							$player_name_arr = explode( ' ', $staff_member['name'], 2 );
							echo '<span class="squad-rows__name-1">' . esc_html( $player_name_arr[0] ) . '</span>';
							echo ! empty( $player_name_arr[1] ) ? ( '<span class="squad-rows__name-2">' . esc_html( $player_name_arr[1] ) . '</span>' ) : '';
							?>
						</a>

						<div class="anwp-bg-gray px-2 py-0 mt-1 mb-1 mb-sm-0 mr-4 anwp-text-xs anwp-leading-1-25">
							<?php echo esc_html( $team_squad_data['title'] ); ?>
						</div>

						<div class="d-none anwp-grid-table__sm-flex align-items-center">
							<?php
							if ( ! empty( $staff_member['nationality'] ) && is_array( $staff_member['nationality'] ) ) :
								foreach ( $staff_member['nationality'] as $country_code ) :
									anwp_football_leagues()->load_partial(
										[
											'class'        => 'options__flag mr-3',
											'size'         => 32,
											'country_code' => $country_code,
										],
										'general/flag'
									);
								endforeach;
							endif;
							?>

							<div class="squad-rows__age-title-mobile mr-1 anwp-opacity-70">
								<?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__age', __( 'Age', 'anwp-football-leagues' ) ) ); ?>:
							</div>
							<div class="squad-rows__age-mobile anwp-text-lg"><?php echo esc_html( $staff_member['age'] ?: '-' ); ?></div>
						</div>
					</div>
					<div class="squad-rows__age px-2 pt-2 anwp-text-xl anwp-grid-table__sm-none" data-filter="<?php echo esc_html( $team_squad_data['team_id'] ); ?>">
						<?php echo esc_html( $staff_member['age'] ?: '-' ); ?>
					</div>
					<div class="squad-rows__nationality px-2 pt-1 d-flex flex-column anwp-grid-table__sm-none" data-filter="<?php echo esc_html( $team_squad_data['team_id'] ); ?>">
						<?php
						if ( ! empty( $staff_member['nationality'] ) && is_array( $staff_member['nationality'] ) ) :
							foreach ( $staff_member['nationality'] as $country_code ) :
								anwp_football_leagues()->load_partial(
									[
										'class'        => 'options__flag',
										'size'         => 32,
										'country_code' => $country_code,
									],
									'general/flag'
								);
							endforeach;
						endif;
						?>
					</div>
					<?php
				endforeach;
			endforeach;
			?>
		</div>
	<?php endif; ?>
</div>
