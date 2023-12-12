<?php
/**
 * The Template for displaying Club >> Header Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/club/club-header.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.8.4
 *
 * @version       0.15.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'logo_big'      => '',
		'club_id'       => '',
		'city'          => '',
		'nationality'   => '',
		'address'       => '',
		'website'       => '',
		'founded'       => '',
		'stadium'       => '',
		'club_kit'      => '',
		'twitter'       => '',
		'youtube'       => '',
		'facebook'      => '',
		'instagram'     => '',
		'vk'            => '',
		'tiktok'        => '',
		'linkedin'      => '',
		'season_id'     => '',
		'show_selector' => true,
	]
);

$club = get_post( $data->club_id );

/**
 * Hook: anwpfl/tmpl-club/before_header
 *
 * @since 0.8.4
 *
 * @param object $data
 */
do_action( 'anwpfl/tmpl-club/before_header', $data );
?>
<div class="club-header anwp-section d-sm-flex anwp-bg-light p-3">
	<?php if ( $data->logo_big ) : ?>
		<div class="club-header__logo-wrapper anwp-flex-sm-none anwp-text-center mb-3 mb-sm-0">
			<img loading="lazy" width="120" height="120" class="anwp-object-contain mr-sm-4 club-header__logo anwp-w-120 anwp-h-120" src="<?php echo esc_attr( $data->logo_big ); ?>" alt="<?php echo get_post_meta( $club->_anwpfl_logo_big_id, '_wp_attachment_image_alt', true ) ?: 'club logo'; ?>">
		</div>
	<?php endif; ?>

	<div class="anwp-flex-auto">
		<div class="anwp-grid-table club-header__options anwp-text-base anwp-border-light">

			<?php
			/**
			 * Hook: anwpfl/tmpl-club/fields_top
			 *
			 * @param WP_Post $club
			 * @param array   $data
			 *
			 * @since 0.7.5
			 *
			 */
			do_action( 'anwpfl/tmpl-club/fields_top', $club, $data );
			?>

			<?php if ( $data->city ) : ?>
				<div class="club-header__option-title anwp-text-sm"><?php echo esc_html( AnWPFL_Text::get_value( 'club__header__city', __( 'City', 'anwp-football-leagues' ) ) ); ?></div>
				<div class="club-header__option-value"><?php echo esc_html( $data->city ); ?></div>
			<?php endif; ?>

			<?php if ( $data->nationality ) : ?>
				<div class="club-header__option-title anwp-text-sm"><?php echo esc_html( AnWPFL_Text::get_value( 'club__header__country', __( 'Country', 'anwp-football-leagues' ) ) ); ?></div>
				<div class="club-header__option-value">
					<?php
					anwp_football_leagues()->load_partial(
						[
							'class'        => 'options__flag',
							'size'         => 32,
							'country_code' => $data->nationality,
						],
						'general/flag'
					);
					?>
				</div>
			<?php endif; ?>

			<?php if ( $data->address ) : ?>
				<div class="club-header__option-title anwp-text-sm"><?php echo esc_html( AnWPFL_Text::get_value( 'club__header__address', __( 'Address', 'anwp-football-leagues' ) ) ); ?></div>
				<div class="club-header__option-value"><?php echo esc_html( $data->address ); ?></div>
			<?php endif; ?>

			<?php if ( $data->website ) : ?>
				<div class="club-header__option-title anwp-text-sm"><?php echo esc_html( AnWPFL_Text::get_value( 'club__header__website', __( 'Website', 'anwp-football-leagues' ) ) ); ?></div>
				<div class="club-header__option-value anwp-break-words">
					<a target="_blank" rel="nofollow" href="<?php echo esc_attr( $data->website ); ?>">
						<?php echo esc_html( trim( str_replace( [ 'http://', 'https://' ], '', $data->website ), '/' ) ); ?>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( $data->founded ) : ?>
				<div class="club-header__option-title anwp-text-sm"><?php echo esc_html( AnWPFL_Text::get_value( 'club__header__founded', __( 'Founded', 'anwp-football-leagues' ) ) ); ?></div>
				<div class="club-header__option-value"><?php echo esc_html( $data->founded ); ?></div>
			<?php endif; ?>

			<?php if ( $data->stadium ) : ?>
				<div class="club-header__option-title anwp-text-sm"><?php echo esc_html( AnWPFL_Text::get_value( 'club__header__stadium', __( 'Stadium', 'anwp-football-leagues' ) ) ); ?></div>
				<div class="club-header__option-value">
					<a href="<?php echo esc_url( get_permalink( (int) $data->stadium ) ); ?>"><?php echo esc_html( get_the_title( (int) $data->stadium ) ); ?></a>
				</div>
			<?php endif; ?>

			<?php if ( $data->club_kit ) : ?>
				<div class="club-header__option-title anwp-text-sm align-items-start"><?php echo esc_html( AnWPFL_Text::get_value( 'club__header__club_kit', __( 'Club Kit', 'anwp-football-leagues' ) ) ); ?></div>
				<div class="club-header__option-value">
					<img loading="lazy" class="club__kit-photo" src="<?php echo esc_attr( $data->club_kit ); ?>" alt="club kit photo">
				</div>
			<?php endif; ?>

			<?php
			// Rendering custom fields
			for ( $ii = 1; $ii <= 3; $ii ++ ) :

				$custom_title = get_post_meta( $data->club_id, '_anwpfl_custom_title_' . $ii, true );
				$custom_value = get_post_meta( $data->club_id, '_anwpfl_custom_value_' . $ii, true );

				if ( $custom_title && $custom_value ) :
					?>
					<div class="club-header__option-title anwp-text-sm">
						<?php echo esc_html( $custom_title ); ?></div>
					<div class="club-header__option-value"><?php echo do_shortcode( esc_html( $custom_value ) ); ?></div>
					<?php
				endif;
			endfor;

			// Rendering dynamic custom fields - @since v0.10.17
			$custom_fields = get_post_meta( $data->club_id, '_anwpfl_custom_fields', true );

			if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
				foreach ( $custom_fields as $field_title => $field_text ) {
					if ( empty( $field_text ) ) {
						continue;
					}
					?>
					<div class="club-header__option-title anwp-text-sm"><?php echo esc_html( $field_title ); ?></div>
					<div class="club-header__option-value"><?php echo do_shortcode( $field_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php
				}
			}
			?>

			<?php if ( $data->twitter || $data->facebook || $data->youtube || $data->instagram || $data->vk || $data->linkedin || $data->tiktok ) : ?>
				<div class="club-header__option-title anwp-text-sm"><?php echo esc_html( AnWPFL_Text::get_value( 'club__header__social', __( 'Social', 'anwp-football-leagues' ) ) ); ?></div>
				<div class="club-header__option-value d-flex flex-wrap align-items-center py-2">
					<?php if ( $data->twitter ) : ?>
						<a href="<?php echo esc_url( $data->twitter ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-twitter"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->youtube ) : ?>
						<a href="<?php echo esc_url( $data->youtube ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-youtube"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->facebook ) : ?>
						<a href="<?php echo esc_url( $data->facebook ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-facebook"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->instagram ) : ?>
						<a href="<?php echo esc_url( $data->instagram ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-instagram"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->vk ) : ?>
						<a href="<?php echo esc_url( $data->vk ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-vk"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->tiktok ) : ?>
						<a href="<?php echo esc_url( $data->tiktok ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-tiktok"></use>
							</svg>
						</a>
					<?php endif; ?>
					<?php if ( $data->linkedin ) : ?>
						<a href="<?php echo esc_url( $data->linkedin ); ?>" class="anwp-link-without-effects mr-2 mb-0 anwp-leading-1 d-inline-block" target="_blank">
							<svg class="anwp-icon anwp-icon--s30">
								<use xlink:href="#icon-linkedin"></use>
							</svg>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php
			/**
			 * Hook: anwpfl/tmpl-club/fields_bottom
			 *
			 * @param WP_Post $club
			 * @param array   $data
			 *
			 * @since 0.7.5
			 *
			 */
			do_action( 'anwpfl/tmpl-club/fields_bottom', $club, $data );
			?>
		</div>
	</div>
</div>
<?php
if ( get_post_meta( $data->club_id, '_anwpfl_subteams', true ) ) {
	anwp_football_leagues()->load_partial(
		[
			'club_id' => $data->club_id,
		],
		'club/club-subteams'
	);
}

if ( $data->show_selector ) {
	anwp_football_leagues()->load_partial(
		[
			'selector_context' => 'club',
			'selector_id'      => $data->club_id,
			'season_id'        => $data->season_id,
		],
		'general/season-selector'
	);
}
