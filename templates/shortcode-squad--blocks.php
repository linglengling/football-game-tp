<?php
/**
 * The Template for displaying Club Squad.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-squad--blocks.php.
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
		'season_dropdown' => 'hide',
		'header'          => true,
		'club_id'         => '',
		'season_id'       => '',
		'class'           => '',
	]
);

// Prepare squad
$squad         = anwp_football_leagues()->club->tmpl_prepare_club_squad( $data->club_id, $data->season_id, true );
$squad_display = anwp_football_leagues()->club->get_squad_display_options( $data->club_id, $data->season_id );

// Prepare staff
$staff = anwp_football_leagues()->club->tmpl_prepare_club_staff( $data->club_id, $data->season_id );

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
		<div class="anwp-grid-table squad-blocks">
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
				foreach ( $squad as $player_id => $player ) :
					if ( $player['position'] !== $loop_key && $squad_display->group ) {
						continue;
					}

					// Check player status. Do not show players "on trial" or "left"
					if ( in_array( $player['status'], [ 'left', 'on trial' ], true ) ) {
						continue;
					}
					?>
					<div class="squad-blocks__block position-relative d-flex flex-column anwp-border-light">
						<div class="squad-blocks__photo-wrapper anwp-text-center d-flex align-items-center anwp-bg-light p-4 anwp-border-light">
							<img loading="lazy" width="70" height="70" class="squad-blocks__photo anwp-object-contain anwp-w-70 anwp-h-70" src="<?php echo esc_url( $player['photo'] ?: $default_photo ); ?>" alt="<?php echo esc_attr( $player['name'] ); ?>">
							<div class="squad-blocks__player-number ml-auto anwp-text-4xl"><?php echo (int) $player['number'] ? : ''; ?></div>
						</div>
						<div class="d-flex flex-column position-relative px-4 mb-2">

							<?php if ( 'on loan' === $player['status'] ) : ?>
								<span class="squad-blocks__status-badge anwp-bg-info anwp-text-white anwp-leading-1 anwp-text-sm anwp-text-center position-absolute"><?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__on_loan', __( 'On Loan', 'anwp-football-leagues' ) ) ); ?></span>
							<?php endif; ?>

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

				if ( ! $squad_display->group ) :
					break;
				endif;

			endforeach;

			foreach ( $staff as $staff_id => $staff_member ) :

				if ( 'no' !== $staff_member['grouping'] ) {
					continue;
				}

				if ( $staff_member['job'] !== $staff_group_attached ) :
					?>
					<div class="squad-blocks__header anwp-text-uppercase anwp-text-lg anwp-bg-light p-1"><?php echo esc_html( $staff_member['job'] ); ?></div>
					<?php $staff_group_attached = $staff_member['job']; ?>
				<?php endif; ?>

				<div class="squad-blocks__block position-relative d-flex flex-column anwp-border-light">
					<div class="squad-blocks__photo-wrapper anwp-text-center d-flex align-items-center anwp-bg-light p-4 anwp-border-light">
						<img loading="lazy" width="70" height="70" class="squad-blocks__photo anwp-object-contain anwp-w-70 anwp-h-70" src="<?php echo esc_url( $staff_member['photo'] ?: $default_photo ); ?>" alt="<?php echo esc_attr( $staff_member['name'] ); ?>">
					</div>
					<div class="d-flex flex-column position-relative px-4 mb-2">

						<div class="squad-blocks__name mb-auto anwp-text-lg anwp-font-semibold"><?php echo esc_html( $staff_member['name'] ); ?></div>

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

	<div class="anwp-grid-table squad-blocks">
		<?php foreach ( $staff_group_items as $staff_group_item_id => $staff_group_item ) : ?>

			<?php if ( $staff_group_item['job'] !== $staff_job ) : ?>
				<div class="squad-blocks__header anwp-text-uppercase anwp-text-lg anwp-bg-light p-1"><?php echo esc_html( $staff_group_item['job'] ); ?></div>
				<?php $staff_job = $staff_group_item['job']; ?>
			<?php endif; ?>

			<div class="squad-blocks__block position-relative d-flex flex-column anwp-border-light">
				<div class="squad-blocks__photo-wrapper anwp-text-center d-flex align-items-center anwp-bg-light p-4 anwp-border-light">
					<img loading="lazy" width="70" height="70" class="squad-blocks__photo anwp-object-contain anwp-w-70 anwp-h-70" src="<?php echo esc_url( $staff_group_item['photo'] ?: $default_photo ); ?>" alt="<?php echo esc_attr( $staff_group_item['name'] ); ?>">
				</div>

				<div class="d-flex flex-column position-relative px-4 mb-2">

					<div class="squad-blocks__name mb-auto anwp-text-lg anwp-font-semibold"><?php echo esc_html( $staff_group_item['name'] ); ?></div>

					<?php if ( $staff_group_item['age'] ) : ?>
						<div class="squad-blocks__player-param d-flex anwp-border-light">
							<span class="squad-blocks__player-param-title anwp-text-sm anwp-opacity-70"><?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__age', __( 'Age', 'anwp-football-leagues' ) ) ); ?></span>
							<span class="squad-blocks__player-param-value ml-auto anwp-text-base"><?php echo esc_html( $staff_group_item['age'] ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $staff_group_item['nationality'] ) && is_array( $staff_group_item['nationality'] ) ) : ?>
						<div class="squad-blocks__player-param d-flex">
							<span class="squad-blocks__player-param-title anwp-text-sm anwp-opacity-70"><?php echo esc_html( AnWPFL_Text::get_value( 'squad__shortcode__nationality', __( 'Nationality', 'anwp-football-leagues' ) ) ); ?></span>
							<span class="squad-blocks__player-param-value ml-auto anwp-text-base">
								<?php
								foreach ( $staff_group_item ['nationality'] as $country_code ) :
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
				<a href="<?php echo esc_url( get_permalink( $staff_group_item_id ) ); ?>" class="anwp-link-cover"></a>
			</div>
		<?php endforeach; ?>
	</div>
<?php endforeach; ?>
</div>
