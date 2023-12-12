<?php
/**
 * The Template for displaying Match >> Gallery Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-gallery.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.10.21
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
		'match_id' => '',
		'header'   => true,
	]
);

if ( empty( $data->match_id ) ) {
	return;
}

// Get Gallery Data
$gallery = get_post_meta( $data->match_id, '_anwpfl_gallery', true );

if ( empty( $gallery ) || ! is_array( $gallery ) ) {
	return;
}

$gallery_alts  = anwp_football_leagues()->data->get_image_alt( array_keys( $gallery ) );
$gallery_notes = get_post_meta( $data->match_id, '_anwpfl_gallery_notes', true );

// Load Gallery scripts
wp_enqueue_script( 'anwp-fl-justified-gallery' );
wp_enqueue_script( 'anwp-fl-justified-gallery-modal' );
?>
<div class="match-gallery anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__gallery__gallery', __( 'Gallery', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<div class="anwp-fl-justified-gallery anwpfl-not-ready-0 match-gallery__images">
		<?php foreach ( $gallery as $image_id => $image ) : ?>
			<a class="anwp-fl-justified-gallery-item" href="<?php echo esc_attr( $image ); ?>"><img width="200" height="200" loading="lazy" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( isset( $gallery_alts[ $image_id ] ) ? $gallery_alts[ $image_id ] : '' ); ?>"></a>
		<?php endforeach; ?>
	</div>

	<?php if ( $gallery_notes ) : ?>
		<p class="match-gallery__notes mt-2 anwp-text-sm"><?php echo wp_kses_post( $gallery_notes ); ?></p>
	<?php endif; ?>
</div>
