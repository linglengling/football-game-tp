<?php
/**
 * The Template for displaying Player >> Header Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/player/player-header.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.8.3
 *
 * @version       0.14.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'position_code' => '',
		'player_id'     => '',
		'club_id'       => '',
		'club_title'    => '',
		'club_link'     => '',
		'twitter'       => '',
		'youtube'       => '',
		'facebook'      => '',
		'instagram'     => '',
		'linkedin'      => '',
		'tiktok'        => '',
		'vk'            => '',
		'show_selector' => true,
	]
);

// Populate Player data
$player                   = (object) [];
$player->photo_id         = get_post_meta( $data->player_id, '_anwpfl_photo_id', true );
$player->weight           = get_post_meta( $data->player_id, '_anwpfl_weight', true );
$player->position         = anwp_football_leagues()->data->get_value_by_key( $data->position_code, 'position' );
$player->height           = get_post_meta( $data->player_id, '_anwpfl_height', true );
$player->place_of_birth   = get_post_meta( $data->player_id, '_anwpfl_place_of_birth', true );
$player->country_of_birth = get_post_meta( $data->player_id, '_anwpfl_country_of_birth', true );
$player->nationality      = maybe_unserialize( get_post_meta( $data->player_id, '_anwpfl_nationality', true ) );
$player->birth_date       = get_post_meta( $data->player_id, '_anwpfl_date_of_birth', true );
$player->death_date       = get_post_meta( $data->player_id, '_anwpfl_date_of_death', true );
$player->full_name        = get_post_meta( $data->player_id, '_anwpfl_full_name', true );
$player->club_id          = get_post_meta( $data->player_id, '_anwpfl_current_club', true );
$player->national_team    = get_post_meta( $data->player_id, '_anwpfl_national_team', true );

// Socials
$player->twitter   = get_post_meta( $data->player_id, '_anwpfl_twitter', true );
$player->youtube   = get_post_meta( $data->player_id, '_anwpfl_youtube', true );
$player->facebook  = get_post_meta( $data->player_id, '_anwpfl_facebook', true );
$player->instagram = get_post_meta( $data->player_id, '_anwpfl_instagram', true );
$player->vk        = get_post_meta( $data->player_id, '_anwpfl_vk', true );
$player->linkedin  = get_post_meta( $data->player_id, '_anwpfl_linkedin', true );
$player->tiktok    = get_post_meta( $data->player_id, '_anwpfl_tiktok', true );

// Check position translation
$translated_position = '';

switch ( $data->position_code ) {
	case 'g':
		$translated_position = anwp_football_leagues()->get_option_value( 'text_single_goalkeeper' );
		break;
	case 'd':
		$translated_position = anwp_football_leagues()->get_option_value( 'text_single_defender' );
		break;
	case 'm':
		$translated_position = anwp_football_leagues()->get_option_value( 'text_single_midfielder' );
		break;
	case 'f':
		$translated_position = anwp_football_leagues()->get_option_value( 'text_single_forward' );
		break;
}

if ( $translated_position ) {
	$player->position = $translated_position;
}

/**
 * Hook: anwpfl/tmpl-player/before_header
 *
 * @since 0.8.3
 *
 * @param object $player
 * @param object $data
 */
do_action( 'anwpfl/tmpl-player/before_header', $player, $data );
?>
<div class="player-header anwp-section d-sm-flex anwp-bg-light p-3">

	<?php
	if ( $player->photo_id ) :
		$caption = wp_get_attachment_caption( $player->photo_id );

		$render_main_photo_caption = 'hide' !== anwp_football_leagues()->customizer->get_value( 'player', 'player_render_main_photo_caption' );

		/**
		 * Rendering player main photo caption.
		 *
		 * @param string $render_main_photo_caption
		 * @param int    $data- >player_id
		 *
		 * @since 0.7.5
		 *
		 */
		$render_main_photo_caption = apply_filters( 'anwpfl/tmpl-player/render_main_photo_caption', $render_main_photo_caption, $data->player_id );
		?>
		<div class="player-header__photo-wrapper anwp-flex-sm-none anwp-text-center mr-sm-4 mb-3 mb-sm-0">
			<?php echo wp_get_attachment_image( $player->photo_id, 'medium', false, [ 'class' => 'anwp-object-contain anwp-w-120 anwp-h-120 player-header__photo' ] ); ?>
			<?php if ( $render_main_photo_caption && $caption ) : ?>
				<div class="mt-1 player-header__photo-caption anwp-text-sm anwp-opacity-80"><?php echo esc_html( $caption ); ?></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="anwp-flex-auto player-header__inner">
		<div class="anwp-grid-table player-header__options anwp-text-base anwp-border-light">

			<?php if ( $player->full_name ) : ?>
				<div class="player-header__option__full_name player-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__full_name', __( 'Full Name', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="player-header__option__full_name player-header__option-value">
					<?php echo esc_html( $player->full_name ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $player->position ) : ?>
				<div class="player-header__option__position player-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__position', __( 'Position', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="player-header__option__position player-header__option-value">
					<?php echo esc_html( $player->position ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $player->national_team && anwp_football_leagues()->club->get_club_title_by_id( $player->national_team ) && anwp_football_leagues()->club->is_national_team( $player->national_team ) ) : ?>
				<?php $club_logo = anwp_football_leagues()->club->get_club_logo_by_id( $player->national_team ); ?>
				<div class="player-header__option__national_team player-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__national_team', __( 'National Team', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="player-header__option__national_team player-header__option-value">
					<div class="d-flex align-items-center">
						<?php if ( $club_logo ) : ?>
							<img loading="lazy" width="30" height="30" class="mr-2 anwp-object-contain anwp-w-30 anwp-h-30" src="<?php echo esc_attr( $club_logo ); ?>" alt="club logo">
						<?php endif; ?>
						<a class="anwp-leading-1-25" href="<?php echo esc_url( anwp_football_leagues()->club->get_club_link_by_id( $player->national_team ) ); ?>"><?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $player->national_team ) ); ?></a>
					</div>
				</div>
			<?php endif; ?>

			<?php
			if ( $player->club_id && anwp_football_leagues()->club->get_club_title_by_id( $player->club_id ) ) :
				$club_logo = anwp_football_leagues()->club->get_club_logo_by_id( $player->club_id );
				?>
				<div class="player-header__option__club_id player-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__current_club', __( 'Current Club', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="player-header__option__club_id player-header__option-value">
					<div class="d-flex align-items-center">
						<?php if ( $club_logo ) : ?>
							<img loading="lazy" width="30" height="30" class="mr-2 anwp-object-contain anwp-w-30 anwp-h-30" src="<?php echo esc_attr( $club_logo ); ?>" alt="club logo">
						<?php endif; ?>
						<a class="anwp-leading-1-25" href="<?php echo esc_url( anwp_football_leagues()->club->get_club_link_by_id( $player->club_id ) ); ?>"><?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $player->club_id ) ); ?></a>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $player->nationality && is_array( $player->nationality ) ) : ?>
				<div class="player-header__option__nationality player-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__nationality', __( 'Nationality', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="player-header__option__nationality player-header__option-value">
					<?php
					foreach ( $player->nationality as $country_code ) :
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
				</div>
			<?php endif; ?>

			<?php if ( $player->place_of_birth ) : ?>
				<div class="player-header__option__place_of_birth player-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__place_of_birth', __( 'Place Of Birth', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="player-header__option__place_of_birth player-header__option-value">
					<div class="d-flex align-items-center">
						<?php
						echo esc_html( $player->place_of_birth );
						if ( $player->country_of_birth ) :
							anwp_football_leagues()->load_partial(
								[
									'class'        => 'options__flag ml-2',
									'size'         => 32,
									'country_code' => $player->country_of_birth,
								],
								'general/flag'
							);
						endif;
						?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $player->birth_date ) : ?>
				<div class="player-header__option__birth_date player-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__date_of_birth', __( 'Date Of Birth', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="player-header__option__birth_date player-header__option-value">
					<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $player->birth_date ) ) ); ?>
				</div>
				<?php
				if ( ! $player->death_date ) :
					$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $player->birth_date );
					$interval       = $birth_date_obj ? $birth_date_obj->diff( new DateTime() )->y : '-';
					?>
					<div class="player-header__option__age player-header__option-title anwp-text-sm">
						<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__age', __( 'Age', 'anwp-football-leagues' ) ) ); ?>
					</div>
					<div class="player-header__option__age player-header__option-value">
						<?php echo esc_html( $interval ); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php
			if ( $player->death_date ) :
				$death_age = '';

				if ( $player->birth_date ) {
					$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $player->birth_date );
					$death_age      = $birth_date_obj ? $birth_date_obj->diff( DateTime::createFromFormat( 'Y-m-d', $player->death_date ) )->y : '-';
				}

				?>
				<div class="player-header__option__death_date player-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__date_of_death', __( 'Date Of Death', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="player-header__option__death_date player-header__option-value">
					<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $player->death_date ) ) ); ?>
					<?php echo intval( $death_age ) ? esc_html( ' (' . intval( $death_age ) . ')' ) : ''; ?>
				</div>
			<?php endif; ?>

			<?php if ( $player->weight ) : ?>
				<div class="player-header__option__weight player-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__weight_kg', __( 'Weight (kg)', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="player-header__option__weight player-header__option-value">
					<?php echo esc_html( $player->weight ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $player->height ) : ?>
				<div class="player-header__option__height player-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__height_cm', __( 'Height (cm)', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="player-header__option__height player-header__option-value">
					<?php echo esc_html( $player->height ); ?>
				</div>
			<?php endif; ?>

			<?php
			// Rendering custom fields
			for ( $ii = 1; $ii <= 3; $ii ++ ) :

				$custom_title = get_post_meta( $data->player_id, '_anwpfl_custom_title_' . $ii, true );
				$custom_value = get_post_meta( $data->player_id, '_anwpfl_custom_value_' . $ii, true );

				if ( $custom_title && $custom_value ) :
					?>
					<div class="player-header__option__<?php echo esc_attr( $custom_title ); ?> player-header__option-title anwp-text-sm">
						<?php echo esc_html( $custom_title ); ?>
					</div>
					<div class="player-header__option__<?php echo esc_attr( $custom_title ); ?> player-header__option-value">
						<?php echo do_shortcode( esc_html( $custom_value ) ); ?>
					</div>
					<?php
				endif;
			endfor;

			// Rendering dynamic custom fields - @since v0.10.17
			$custom_fields = get_post_meta( $data->player_id, '_anwpfl_custom_fields', true );

			if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
				foreach ( $custom_fields as $field_title => $field_text ) {
					if ( empty( $field_text ) ) {
						continue;
					}
					?>
					<div class="player-header__option__<?php echo esc_attr( $field_title ); ?> player-header__option-title anwp-text-sm">
						<?php echo esc_html( $field_title ); ?>
					</div>
					<div class="player-header__option__<?php echo esc_attr( $field_title ); ?> player-header__option-value">
						<?php echo do_shortcode( esc_html( $field_text ) ); ?>
					</div>
					<?php
				}
			}
			?>

			<?php if ( $player->twitter || $player->facebook || $player->youtube || $player->instagram || $player->vk || $player->linkedin || $player->tiktok ) : ?>
				<div class="player-header__option__social player-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'club__header__social', __( 'Social', 'anwp-football-leagues' ) ) ); ?>
				</div>

				<div class="player-header__option-value d-flex flex-wrap align-items-center py-2">
					<?php if ( $player->twitter ) : ?>
						<a href="<?php echo esc_url( $player->twitter ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-twitter"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->youtube ) : ?>
						<a href="<?php echo esc_url( $player->youtube ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-youtube"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->facebook ) : ?>
						<a href="<?php echo esc_url( $player->facebook ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-facebook"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->instagram ) : ?>
						<a href="<?php echo esc_url( $player->instagram ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-instagram"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->linkedin ) : ?>
						<a href="<?php echo esc_url( $player->linkedin ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-linkedin"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->tiktok ) : ?>
						<a href="<?php echo esc_url( $player->tiktok ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-tiktok"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $player->vk ) : ?>
						<a href="<?php echo esc_url( $player->vk ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-vk"></use>
							</svg>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
if ( $data->show_selector ) {
	anwp_football_leagues()->load_partial(
		[
			'selector_context' => 'player',
			'selector_id'      => $data->player_id,
			'season_id'        => $data->current_season_id,
		],
		'general/season-selector'
	);
}
