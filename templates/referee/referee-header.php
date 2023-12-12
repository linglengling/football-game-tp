<?php
/**
 * The Template for displaying Referee >> Header Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/referee/referee-header.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.11.14
 *
 * @version       0.14.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'staff_id'      => '',
		'season_id'     => '',
		'show_selector' => true,
	]
);

$staff_id = $data->staff_id;

if ( ! intval( $staff_id ) ) {
	return;
}

// Prepare data
$photo_id       = get_post_meta( $staff_id, '_anwpfl_photo_id', true );
$place_of_birth = get_post_meta( $staff_id, '_anwpfl_place_of_birth', true );
$job_title      = get_post_meta( $staff_id, '_anwpfl_job_title', true );
$death_date     = get_post_meta( $staff_id, '_anwpfl_date_of_death', true );
$full_name      = get_post_meta( $staff_id, '_anwpfl_full_name', true );

// Nationality
$nationality = maybe_unserialize( get_post_meta( $staff_id, '_anwpfl_nationality', true ) );

// Birth Date
$birth_date = get_post_meta( $staff_id, '_anwpfl_date_of_birth', true );
?>
<div class="referee-header anwp-section d-sm-flex anwp-bg-light p-3">

	<?php
	if ( $photo_id ) :
		$caption = wp_get_attachment_caption( $photo_id );

		$render_main_photo_caption = 'hide' !== anwp_football_leagues()->customizer->get_value( 'player', 'player_render_main_photo_caption' );

		/**
		 * Rendering player main photo caption.
		 *
		 * @since 0.7.5
		 *
		 * @param string $render_main_photo_caption
		 * @param int    $staff_id
		 */
		$render_main_photo_caption = apply_filters( 'anwpfl/tmpl-player/render_main_photo_caption', $render_main_photo_caption, $staff_id );
		?>
		<div class="referee-header__photo-wrapper anwp-flex-sm-none anwp-text-center mr-sm-4 mb-3 mb-sm-0">
			<?php echo wp_get_attachment_image( $photo_id, 'medium', false, [ 'class' => 'anwp-object-contain anwp-w-120 anwp-h-120 player-header__photo' ] ); ?>
			<?php if ( $render_main_photo_caption && $caption ) : ?>
				<div class="mt-1 referee-header__photo-caption anwp-text-sm anwp-opacity-80"><?php echo esc_html( $caption ); ?></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="anwp-flex-auto referee-header__inner">
		<div class="anwp-grid-table referee-header__options anwp-text-base anwp-border-light">

			<?php if ( $full_name ) : ?>
				<div class="referee-header__option__full_name referee-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__full_name', __( 'Full Name', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="referee-header__option__full_name referee-header__option-value">
					<?php echo esc_html( $full_name ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $job_title ) : ?>
				<div class="referee-header__option__job_title referee-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'referee__content__job_title', __( 'Job Title', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="referee-header__option__job_title referee-header__option-value">
					<?php echo esc_html( $job_title ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $nationality && is_array( $nationality ) ) : ?>
				<div class="referee-header__option__nationality referee-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'referee__content__nationality', __( 'Nationality', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="referee-header__option__nationality referee-header__option-value">
					<?php
					foreach ( $nationality as $country_code ) :
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

			<?php if ( $place_of_birth ) : ?>
				<div class="referee-header__option__place_of_birth referee-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'referee__content__place_of_birth', __( 'Place Of Birth', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="referee-header__option__place_of_birth referee-header__option-value">
					<?php echo esc_html( $place_of_birth ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $birth_date ) : ?>
				<div class="referee-header__option__birth_date referee-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'referee__content__date_of_birth', __( 'Date Of Birth', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="referee-header__option__birth_date referee-header__option-value">
					<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $birth_date ) ) ); ?>
				</div>

				<?php
				if ( ! $death_date ) :
					$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $birth_date );
					$interval       = $birth_date_obj ? $birth_date_obj->diff( new DateTime() )->y : '-';
					?>
					<div class="referee-header__option__age referee-header__option-title anwp-text-sm">
						<?php echo esc_html( AnWPFL_Text::get_value( 'referee__content__age', __( 'Age', 'anwp-football-leagues' ) ) ); ?>
					</div>
					<div class="referee-header__option__age referee-header__option-value">
						<?php echo esc_html( $interval ); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php
			if ( $death_date ) :
				$death_age = '';

				if ( $birth_date ) {
					$birth_date_obj = DateTime::createFromFormat( 'Y-m-d', $birth_date );
					$death_age      = $birth_date_obj ? $birth_date_obj->diff( DateTime::createFromFormat( 'Y-m-d', $death_date ) )->y : '-';
				}

				?>
				<div class="referee-header__option__death_date referee-header__option-title anwp-text-sm">
					<?php echo esc_html( AnWPFL_Text::get_value( 'player__header__date_of_death', __( 'Date Of Death', 'anwp-football-leagues' ) ) ); ?>
				</div>
				<div class="referee-header__option__death_date referee-header__option-value">
					<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $death_date ) ) ); ?>
					<?php echo intval( $death_age ) ? esc_html( ' (' . intval( $death_age ) . ')' ) : ''; ?>
				</div>
			<?php endif; ?>

			<?php
			for ( $ii = 1; $ii <= 3; $ii ++ ) :

				$custom_title = get_post_meta( $staff_id, '_anwpfl_custom_title_' . $ii, true );
				$custom_value = get_post_meta( $staff_id, '_anwpfl_custom_value_' . $ii, true );

				if ( $custom_title && $custom_value ) :
					?>
					<div class="referee-header__option__<?php echo esc_attr( $custom_title ); ?> referee-header__option-title anwp-text-sm">
						<?php echo esc_html( $custom_title ); ?>
					</div>
					<div class="referee-header__option__<?php echo esc_attr( $custom_title ); ?> referee-header__option-value">
						<?php echo do_shortcode( esc_html( $custom_value ) ); ?>
					</div>
					<?php
				endif;
			endfor;

			// Rendering dynamic custom fields - @since v0.10.17
			$custom_fields = get_post_meta( $staff_id, '_anwpfl_custom_fields', true );

			if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
				foreach ( $custom_fields as $field_title => $field_text ) {
					if ( empty( $field_text ) ) {
						continue;
					}
					?>
					<div class="referee-header__option__<?php echo esc_attr( $field_title ); ?> referee-header__option-title anwp-text-sm">
						<?php echo esc_html( $field_title ); ?>
					</div>
					<div class="referee-header__option__<?php echo esc_attr( $field_title ); ?> referee-header__option-value">
						<?php echo do_shortcode( esc_html( $field_text ) ); ?>
					</div>
					<?php
				}
			}
			?>
		</div>
	</div>
</div>
<?php
if ( $data->show_selector ) {
	anwp_football_leagues()->load_partial(
		[
			'selector_context' => 'referee',
			'selector_id'      => $staff_id,
			'season_id'        => $data->season_id,
		],
		'general/season-selector'
	);
}
