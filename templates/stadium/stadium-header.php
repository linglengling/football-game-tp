<?php
/**
 * The Template for displaying Stadium >> Header Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/stadium/stadium-header.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.14.0
 *
 * @version       0.14.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'stadium_id'    => '',
		'season_id'     => '',
		'show_selector' => true,
	]
);

if ( ! intval( $data->stadium_id ) ) {
	return;
}

$stadium = get_post( $data->stadium_id );
?>
<div class="stadium-header anwp-section">

	<?php if ( $stadium->_anwpfl_photo ) : ?>
		<div class="stadium-header__photo-wrapper anwp-text-center mb-3">
			<img loading="lazy" class="stadium-header__photo w-100" src="<?php echo esc_attr( $stadium->_anwpfl_photo ); ?>" alt="<?php echo get_post_meta( $stadium->_anwpfl_photo_id, '_wp_attachment_image_alt', true ) ?: 'stadium photo'; ?>">
		</div>
	<?php endif; ?>

	<div class="anwp-grid-table stadium-header__options anwp-text-base anwp-border-light anwp-bg-light p-3">

		<?php
		/**
		 * Hook: anwpfl/tmpl-stadium/fields_top
		 *
		 * @param WP_Post $stadium
		 *
		 * @since 0.7.5
		 *
		 */
		do_action( 'anwpfl/tmpl-stadium/fields_top', $stadium );
		?>

		<?php if ( $stadium->_anwpfl_city ) : ?>
			<div class="stadium-header__option__city stadium-header__option-title anwp-text-sm">
				<?php echo esc_html( AnWPFL_Text::get_value( 'stadium__content__city', __( 'City', 'anwp-football-leagues' ) ) ); ?>
			</div>
			<div class="stadium-header__option__city stadium-header__option-value">
				<?php echo esc_html( $stadium->_anwpfl_city ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $stadium->_anwpfl_clubs && is_array( $stadium->_anwpfl_clubs ) ) : ?>
			<div class="stadium-header__option__clubs stadium-header__option-title anwp-text-sm">
				<?php echo esc_html( AnWPFL_Text::get_value( 'stadium__content__clubs', __( 'Clubs', 'anwp-football-leagues' ) ) ); ?>
			</div>
			<div class="stadium-header__option__clubs stadium-header__option-value flex-wrap">
				<?php
				foreach ( $stadium->_anwpfl_clubs as $stadium_club ) :
					$alt_club_logo = anwp_football_leagues()->club->get_club_logo_by_id( $stadium_club );
					$alt_club_name = anwp_football_leagues()->club->get_club_title_by_id( $stadium_club );
					?>
					<div class="d-flex align-items-center mr-3">
						<?php if ( $alt_club_logo ) : ?>
							<img loading="lazy" width="30" height="30" class="mr-1 anwp-object-contain anwp-w-30 anwp-h-30" src="<?php echo esc_attr( $alt_club_logo ); ?>"
								alt="<?php echo esc_attr( $alt_club_name ); ?>">
						<?php endif; ?>
						<a href="<?php echo esc_url( anwp_football_leagues()->club->get_club_link_by_id( $stadium_club ) ); ?>" class="anwp-link-without-effects">
							<?php echo esc_html( $alt_club_name ); ?>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $stadium->_anwpfl_address ) : ?>
			<div class="stadium-header__option__address stadium-header__option-title anwp-text-sm">
				<?php echo esc_html( AnWPFL_Text::get_value( 'stadium__content__address', __( 'Address', 'anwp-football-leagues' ) ) ); ?>
			</div>
			<div class="stadium-header__option__address stadium-header__option-value">
				<?php echo esc_html( $stadium->_anwpfl_address ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $stadium->_anwpfl_capacity ) : ?>
			<div class="stadium-header__option__capacity stadium-header__option-title anwp-text-sm">
				<?php echo esc_html( AnWPFL_Text::get_value( 'stadium__content__capacity', __( 'Capacity', 'anwp-football-leagues' ) ) ); ?>
			</div>
			<div class="stadium-header__option__capacity stadium-header__option-value">
				<?php echo esc_html( $stadium->_anwpfl_capacity ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $stadium->_anwpfl_opened ) : ?>
			<div class="stadium-header__option__opened stadium-header__option-title anwp-text-sm">
				<?php echo esc_html( AnWPFL_Text::get_value( 'stadium__content__opened', __( 'Opened', 'anwp-football-leagues' ) ) ); ?>
			</div>
			<div class="stadium-header__option__opened stadium-header__option-value">
				<?php echo esc_html( $stadium->_anwpfl_opened ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $stadium->_anwpfl_surface ) : ?>
			<div class="stadium-header__option__surface stadium-header__option-title anwp-text-sm">
				<?php echo esc_html( AnWPFL_Text::get_value( 'stadium__content__surface', __( 'Surface', 'anwp-football-leagues' ) ) ); ?>
			</div>
			<div class="stadium-header__option__surface stadium-header__option-value">
				<?php echo esc_html( $stadium->_anwpfl_surface ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $stadium->_anwpfl_website ) : ?>
			<div class="stadium-header__option__website stadium-header__option-title anwp-text-sm">
				<?php echo esc_html( AnWPFL_Text::get_value( 'stadium__content__website', __( 'Website', 'anwp-football-leagues' ) ) ); ?>
			</div>
			<div class="stadium-header__option__website stadium-header__option-value anwp-break-words">
				<a target="_blank" rel="nofollow" href="<?php echo esc_attr( $stadium->_anwpfl_website ); ?>">
					<?php echo esc_html( str_replace( [ 'http://', 'https://' ], '', $stadium->_anwpfl_website ) ); ?>
				</a>
			</div>
		<?php endif; ?>

		<?php
		// Rendering custom fields
		for ( $ii = 1; $ii <= 3; $ii ++ ) :

			$custom_title = get_post_meta( $stadium->ID, '_anwpfl_custom_title_' . $ii, true );
			$custom_value = get_post_meta( $stadium->ID, '_anwpfl_custom_value_' . $ii, true );

			if ( $custom_title && $custom_value ) :
				?>
				<div class="stadium-header__option__<?php echo esc_attr( $custom_title ); ?> stadium-header__option-title anwp-text-sm">
					<?php echo esc_html( $custom_title ); ?>
				</div>
				<div class="stadium-header__option__<?php echo esc_attr( $custom_title ); ?> stadium-header__option-value">
					<?php echo do_shortcode( esc_html( $custom_value ) ); ?>
				</div>
				<?php
			endif;
		endfor;

		// Rendering dynamic custom fields - @since v0.10.17
		$custom_fields = get_post_meta( $stadium->ID, '_anwpfl_custom_fields', true );

		if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
			foreach ( $custom_fields as $field_title => $field_text ) {
				if ( empty( $field_text ) ) {
					continue;
				}
				?>
				<div class="stadium-header__option__<?php echo esc_attr( $field_title ); ?> stadium-header__option-title anwp-text-sm">
					<?php echo esc_html( $field_title ); ?>
				</div>
				<div class="stadium-header__option__<?php echo esc_attr( $field_title ); ?> stadium-header__option-value">
					<?php echo do_shortcode( esc_html( $field_text ) ); ?>
				</div>
				<?php
			}
		}

		/**
		 * Hook: anwpfl/tmpl-stadium/fields_bottom
		 *
		 * @param WP_Post $stadium
		 *
		 * @since 0.7.5
		 *
		 */
		do_action( 'anwpfl/tmpl-stadium/fields_bottom', $stadium );
		?>
	</div>

</div>
<?php
if ( $data->show_selector ) {
	anwp_football_leagues()->load_partial(
		[
			'selector_context' => 'stadium',
			'selector_id'      => $data->stadium_id,
			'season_id'        => $data->season_id,
		],
		'general/season-selector'
	);
}
