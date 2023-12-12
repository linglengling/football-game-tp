<?php
/**
 * The Template for displaying Referee.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-referee.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.12.4
 *
 * @version       0.14.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$data = (object) wp_parse_args(
	$data,
	[
		'referee_id'        => '',
		'options_text'      => '',
		'context'           => 'shortcode',
		'profile_link'      => 'yes',
		'profile_link_text' => 'profile',
		'show_job'          => 0,
	]
);

if ( empty( $data->referee_id ) ) {
	return;
}

// Check player exists
$referee = get_post( $data->referee_id );

if ( empty( $referee->post_type ) || 'anwp_referee' !== $referee->post_type ) {
	return;
}

// Nationality
$nationality = maybe_unserialize( get_post_meta( $referee->ID, '_anwpfl_nationality', true ) );

$photo_id  = get_post_meta( $referee->ID, '_anwpfl_photo_id', true );
$job_title = AnWP_Football_Leagues::string_to_bool( $data->show_job ) ? get_post_meta( $referee->ID, '_anwpfl_job_title', true ) : '';
?>
<div class="anwp-b-wrap">
	<div class="player-block anwp-fl-border anwp-border-light context--<?php echo esc_attr( $data->context ); ?>">
		<div class="d-flex align-items-center p-2 anwp-bg-light player-block__header">
			<?php if ( $photo_id ) : ?>
				<?php echo wp_get_attachment_image( $photo_id, 'medium', false, [ 'class' => 'anwp-w-80 anwp-h-80 anwp-object-contain m-0' ] ); ?>
			<?php endif; ?>
			<div class="flex-grow-1">
				<div class="player-block__name px-3 anwp-text-base anwp-leading-1-25 anwp-font-medium pl-3"><?php echo esc_html( $referee->post_title ); ?></div>

				<?php if ( ! empty( $nationality ) || anwp_football_leagues()->player->get_translated_position( $referee->ID ) ) : ?>
					<div class="player-block__extra d-flex align-items-center mt-2 pl-3 anwp-text-sm">
						<?php
						if ( ! empty( $nationality ) && is_array( $nationality ) ) :
							foreach ( $nationality as $country_code ) :
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
						<span><?php echo esc_html( $job_title ); ?></span>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="player-block__options">

			<?php if ( trim( $data->options_text ) ) : ?>

				<?php
				$referee_options = explode( '|', $data->options_text );

				foreach ( $referee_options as $referee_option ) :

					if ( ! trim( $referee_option ) ) {
						continue;
					}

					if ( false === mb_strpos( $referee_option, ':' ) ) {
						continue;
					}

					list( $label, $value ) = explode( ':', $referee_option );
					?>
					<div class="player-block__option d-flex align-items-center anwp-fl-border-top anwp-border-light py-2">
						<div class="player-block__option-label flex-grow-1 anwp-text-sm"><?php echo esc_html( trim( $label ) ); ?></div>
						<div class="player-block__option-value border-left anwp-text-sm"><?php echo esc_html( trim( $value ) ); ?></div>
					</div>
				<?php endforeach; ?>

			<?php endif; ?>
		</div>

		<?php if ( AnWP_Football_Leagues::string_to_bool( $data->profile_link ) ) : ?>
			<div class="player-block__profile-link anwp-fl-border-top anwp-border-light p-2">
				<div class="position-relative anwp-fl-btn-outline anwp-text-sm w-100 player-block__profile-link-btn">
					<?php echo esc_html( $data->profile_link_text ); ?>
					<a href="<?php echo esc_url( get_permalink( $referee ) ); ?>" class="anwp-link-cover anwp-link-without-effects"></a>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
