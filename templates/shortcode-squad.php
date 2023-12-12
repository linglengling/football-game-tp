<?php
/**
 * The Template for displaying Club Squad.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-squad.php.
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

// Prepare squad
$squad         = anwp_football_leagues()->club->tmpl_prepare_club_squad( $data->club_id, $data->season_id, true );
$squad_display = anwp_football_leagues()->club->get_squad_display_options( $data->club_id, $data->season_id );

// Prepare staff
$staff = anwp_football_leagues()->club->tmpl_prepare_club_staff( $data->club_id, $data->season_id );

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
	?>

	<?php
	if ( empty( $squad ) ) :
		anwp_football_leagues()->load_partial(
			[
				'no_data_text' => AnWPFL_Text::get_value( 'squad__shortcode__no_players_in_the_squad', __( 'No players in the squad', 'anwp-football-leagues' ) ),
			],
			'general/no-data'
		);
	else :
		?>
		<div class="anwp-grid-table squad-rows anwp-text-center anwp-border-light">
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
				foreach ( $squad as $player_id => $player ) :
					if ( $player['position'] !== $position_slug && $squad_display->group ) {
						continue;
					}

					// Check player status. Do not show players "on trial" or "left"
					if ( in_array( $player['status'], [ 'left', 'on trial' ], true ) ) {
						continue;
					}
					?>

					<div class="squad-rows__number anwp-bg-secondary anwp-text-white px-2 anwp-text-3xl d-flex align-items-center justify-content-center">
						<?php echo (int) $player['number'] ? : ''; ?>
					</div>
					<div class="squad-rows__photo-wrapper px-2 position-relative">
						<img loading="lazy" width="60" height="60" class="squad-rows__photo anwp-object-contain m-2 anwp-w-60 anwp-h-60" src="<?php echo esc_url( $player['photo'] ?: $default_photo ); ?>" alt="<?php echo esc_attr( $player['name'] ); ?>">
						<?php if ( 'on loan' === $player['status'] ) : ?>
							<span class="anwp-bg-info anwp-text-white anwp-leading-1 anwp-text-xs text-uppercase anwp-text-center squad-rows__status-badge position-absolute"><?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__on_loan', __( 'On Loan', 'anwp-football-leagues' ) ) ); ?></span>
						<?php endif; ?>
					</div>
					<div class="squad-rows__name d-flex flex-column align-items-start justify-content-center anwp-text-base anwp-text-left anwp-font-semibold">
						<a href="<?php echo esc_url( get_permalink( $player_id ) ); ?>" class="anwp-link-without-effects">
							<?php
							$player_name_arr = explode( ' ', $player['name'], 2 );
							echo '<span class="squad-rows__name-1">' . esc_html( $player_name_arr[0] ) . '</span>';
							echo ! empty( $player_name_arr[1] ) ? ( '<span class="squad-rows__name-2">' . esc_html( $player_name_arr[1] ) . '</span>' ) : '';
							?>
						</a>

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
					<div class="squad-rows__age px-2 pt-2 anwp-text-xl anwp-grid-table__sm-none">
						<?php echo esc_html( $player['age'] ?: '-' ); ?>
					</div>
					<div class="squad-rows__nationality px-2 pt-1 d-flex flex-column anwp-grid-table__sm-none">
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

				if ( ! $squad_display->group ) :
					break;
				endif;
			endforeach;

			foreach ( $staff as $staff_id => $staff_member ) :

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

				<div class="squad-rows__number anwp-bg-secondary anwp-text-white px-2 anwp-text-3xl d-flex align-items-center justify-content-center"></div>
				<div class="squad-rows__photo-wrapper px-2 d-flex flex-row">
					<img loading="lazy" width="60" height="60" class="squad-rows__photo anwp-object-contain m-2 anwp-w-60 anwp-h-60" src="<?php echo esc_url( $staff_member['photo'] ?: $default_photo ); ?>" alt="<?php echo esc_attr( $staff_member['name'] ); ?>">
				</div>
				<div class="squad-rows__name d-flex align-items-center justify-content-start anwp-text-base anwp-text-left anwp-font-semibold">
					<a href="<?php echo esc_url( get_permalink( $staff_id ) ); ?>" class="anwp-link-without-effects">
						<?php
						$player_name_arr = explode( ' ', $staff_member['name'], 2 );
						echo '<span class="squad-rows__name-1">' . esc_html( $player_name_arr[0] ) . '</span>';
						echo ! empty( $player_name_arr[1] ) ? ( '<span class="squad-rows__name-2">' . esc_html( $player_name_arr[1] ) . '</span>' ) : '';
						?>
					</a>
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
				<div class="squad-rows__age px-2 pt-2 anwp-text-xl anwp-grid-table__sm-none">
					<?php echo esc_html( $staff_member['age'] ?: '-' ); ?>
				</div>
				<div class="squad-rows__nationality px-2 pt-1 d-flex flex-column anwp-grid-table__sm-none">
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
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php
	$staff_data = [];

	// Prepare staff data
	foreach ( $staff as $staff_id => $staff_member ) :
		if ( 'yes' !== $staff_member['grouping'] ) {
			continue;
		}

		$staff_data[ $staff_member['group'] ][ $staff_id ] = $staff_member;
	endforeach;

	foreach ( $staff_data as $staff_group => $staff_group_items ) :
		$staff_job = '';

		if ( $staff_group ) :

			anwp_football_leagues()->load_partial(
				[
					'text'  => $staff_group,
					'class' => empty( $squad ) ? '' : 'mt-5',
				],
				'general/header'
			);

		endif;
		?>

		<div class="anwp-grid-table squad-rows squad-rows__staff anwp-text-center anwp-border-light">
			<?php
			foreach ( $staff_group_items as $staff_group_item_id => $staff_group_item ) :
				/*
				|--------------------------------------------------------------------
				| Squad Header
				|--------------------------------------------------------------------
				*/
				if ( $staff_group_item['job'] !== $staff_job ) :
					?>
					<div class="squad-rows__header-title anwp-text-lg anwp-bg-light anwp-text-left px-3 py-1">
						<?php echo esc_html( $staff_group_item['job'] ); ?>
					</div>
					<div class="squad-rows__header-param anwp-text-sm anwp-bg-light py-1 d-flex align-items-center justify-content-center px-2 anwp-grid-table__sm-none">
						<?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__age', __( 'Age', 'anwp-football-leagues' ) ) ); ?>
					</div>
					<div class="squad-rows__header-param anwp-text-sm anwp-bg-light py-1 d-flex justify-content-center align-items-center px-2 anwp-grid-table__sm-none">
						<?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__nationality', __( 'Nationality', 'anwp-football-leagues' ) ) ); ?>
					</div>
					<?php $staff_job = $staff_group_item['job']; ?>
				<?php endif; ?>

				<div class="squad-rows__photo-wrapper px-2 position-relative">
					<img loading="lazy" width="60" height="60" class="squad-rows__photo anwp-object-contain m-2 anwp-w-60 anwp-h-60" src="<?php echo esc_url( $staff_group_item['photo'] ?: $default_photo ); ?>"  alt="<?php echo esc_attr( $staff_group_item['name'] ); ?>">
				</div>
				<div class="squad-rows__name d-flex align-items-center justify-content-start anwp-text-base anwp-text-left anwp-font-semibold">
					<a href="<?php echo esc_url( get_permalink( $staff_group_item_id ) ); ?>" class="anwp-link-without-effects">
						<?php
						$player_name_arr = explode( ' ', $staff_group_item['name'], 2 );
						echo '<span class="squad-rows__name-1">' . esc_html( $player_name_arr[0] ) . '</span>';
						echo ! empty( $player_name_arr[1] ) ? ( '<span class="squad-rows__name-2">' . esc_html( $player_name_arr[1] ) . '</span>' ) : '';
						?>
					</a>
					<div class="d-none anwp-grid-table__sm-flex align-items-center">
						<?php
						if ( ! empty( $staff_group_item['nationality'] ) && is_array( $staff_group_item['nationality'] ) ) :
							foreach ( $staff_group_item['nationality'] as $country_code ) :
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
						<div class="squad-rows__age-mobile anwp-text-lg"><?php echo esc_html( $staff_group_item['age'] ); ?></div>
					</div>
				</div>
				<div class="squad-rows__age px-2 pt-2 anwp-text-xl anwp-grid-table__sm-none">
					<?php echo esc_html( $staff_group_item['age'] ?: '-' ); ?>
				</div>
				<div class="squad-rows__nationality px-2 pt-1 d-flex flex-column anwp-grid-table__sm-none">
					<?php
					if ( ! empty( $staff_group_item['nationality'] ) && is_array( $staff_group_item['nationality'] ) ) :
						foreach ( $staff_group_item['nationality'] as $country_code ) :
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
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>
</div>
