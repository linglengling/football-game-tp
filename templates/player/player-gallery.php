<?php
/**
 * The Template for displaying Player >> Gallery Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/player/player-gallery.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.10.9
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
		'player_id' => '',
		'header'    => true,
	]
);

$player = get_post( $data->player_id );

// Get Gallery Data
$gallery = $player->_anwpfl_gallery;

if ( empty( $gallery ) || ! is_array( $gallery ) ) {
	return;
}

$gallery_alts = anwp_football_leagues()->data->get_image_alt( array_keys( $gallery ) );

/**
 * Hook: anwpfl/tmpl-player/before_gallery
 *
 * @since 0.10.9
 *
 * @param WP_Post $player
 */
do_action( 'anwpfl/tmpl-player/before_gallery', $player );

// Load Gallery scripts
wp_enqueue_script( 'anwp-fl-justified-gallery' );
wp_enqueue_script( 'anwp-fl-justified-gallery-modal' );
?>
<div class="player-gallery anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'player__gallery__gallery', __( 'Gallery', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<div class="player-gallery__gallery anwp-fl-justified-gallery anwpfl-not-ready-0">
		<?php foreach ( $gallery as $image_id => $image ) : ?>
			<a class="anwp-fl-justified-gallery-item" href="<?php echo esc_attr( $image ); ?>"><img width="200" height="200" loading="lazy" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( isset( $gallery_alts[ $image_id ] ) ? $gallery_alts[ $image_id ] : '' ); ?>"></a>
		<?php endforeach; ?>
	</div>

	<?php if ( $player->_anwpfl_gallery_notes ) : ?>
		<p class="mt-2 anwp-text-sm player-gallery__notes"><?php echo wp_kses_post( $player->_anwpfl_gallery_notes ); ?></p>
	<?php endif; ?>
</div>
