<?php
/**
 * The Template for displaying Match >> Video Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-video.php.
 *
 * @var object $data - Object with args.
 *
 * @author           Andrei Strekozov <anwp.pro>
 * @package          AnWP-Football-Leagues/Templates
 * @since            0.10.23
 * @version          0.11.13
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'video_source'    => '',
		'video_media_url' => '',
		'video_id'        => '',
		'match_id'        => '',
		'header'          => true,
	]
);

if ( ! in_array( $data->video_source, [ 'site', 'youtube', 'vimeo' ], true ) ) {
	return;
}

/**
 * Hook: anwpfl/tmpl-match/video_before
 *
 * @param object $data Match data
 *
 * @since 0.7.5
 */
do_action( 'anwpfl/tmpl-match/video_before', $data );

$video_info        = get_post_meta( $data->match_id, '_anwpfl_video_info', true );
$additional_videos = get_post_meta( $data->match_id, '_anwpfl_additional_videos', true );
$jpg_yt_image      = 'jpg' === get_post_meta( $data->match_id, '_anwpfl_yt_image_format', true );

wp_enqueue_script( 'plyr' );
wp_enqueue_style( 'plyr' );
?>
<div class="match-video anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__video__match_video', __( 'Match Video', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<div class="match__video mt-2">

		<?php if ( 'youtube' === AnWPFL_Options::get_value( 'preferred_video_player' ) || ( 'mixed' === AnWPFL_Options::get_value( 'preferred_video_player' ) && 'youtube' === $data->video_source && $data->video_id ) ) : ?>
			<div class="anwp-fl-v-video__item position-relative anwp-cursor-pointer"
				data-url="<?php echo esc_attr( anwp_football_leagues()->helper->get_youtube_id( $data->video_id ) ); ?>">
				<?php if ( $jpg_yt_image ) : ?>
					<img class="w-100" src="https://i.ytimg.com/vi/<?php echo esc_attr( anwp_football_leagues()->helper->get_youtube_id( $data->video_id ) ); ?>/maxresdefault.jpg" alt="youtube video" />
				<?php else : ?>
					<img class="w-100" src="https://i.ytimg.com/vi_webp/<?php echo esc_attr( anwp_football_leagues()->helper->get_youtube_id( $data->video_id ) ); ?>/maxresdefault.webp" alt="youtube video" />
				<?php endif; ?>
				<div class="anwp-fl-v-playbtn"><span class="anwp-fl-v-playbtn__visually-hidden">Play</span></div>
			</div>
		<?php else : ?>
			<?php if ( 'site' === $data->video_source && $data->video_media_url ) : ?>
				<video class="anwp-video-player" playsinline controls>
					<source src="<?php echo esc_url( $data->video_media_url ); ?>" type="video/mp4">
				</video>
			<?php elseif ( 'youtube' === $data->video_source && $data->video_id ) : ?>
				<div class="anwp-video-player" data-plyr-provider="youtube" data-plyr-embed-id="<?php echo esc_attr( $data->video_id ); ?>"></div>
			<?php elseif ( 'vimeo' === $data->video_source && $data->video_id ) : ?>
				<div class="anwp-video-player" data-plyr-provider="vimeo" data-plyr-embed-id="<?php echo esc_attr( $data->video_id ); ?>"></div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $video_info ) : ?>
			<div class="match__video-info mt-2 anwp-text-sm"><?php echo esc_html( $video_info ); ?></div>
		<?php endif; ?>

		<?php
		/*
		|--------------------------------------------------------------------
		| Additional Videos
		|--------------------------------------------------------------------
		*/
		if ( ! empty( $additional_videos ) && is_array( $additional_videos ) ) :
			?>
			<div class="anwp-video-grid mt-2 d-flex flex-wrap anwp-no-gutters mx-n1">
				<?php
				foreach ( $additional_videos as $additional_video ) :

					if ( empty( $additional_video['video_source'] ) ) {
						continue;
					}

					$video_info = isset( $additional_video['video_info'] ) ? $additional_video['video_info'] : '';
					?>
					<div class="anwp-video-grid__item mt-3 anwp-col-sm-6 anwp-col-xl-4">
						<div class="anwp-video-grid__item-inner mx-1 mt-1">
							<?php
							if ( 'youtube' === AnWPFL_Options::get_value( 'preferred_video_player' ) || ( 'mixed' === AnWPFL_Options::get_value( 'preferred_video_player' ) && 'youtube' === $data->video_source && $data->video_id ) ) :
								$video_id = anwp_football_leagues()->helper->get_youtube_id( $additional_video['video_id'] );
								?>
								<div class="anwp-fl-v-video__item position-relative anwp-cursor-pointer"
									data-url="<?php echo esc_attr( $video_id ); ?>">
									<img class="w-100" src="https://i.ytimg.com/vi_webp/<?php echo esc_attr( $video_id ); ?>/mqdefault.webp" alt="youtube video" />
									<div class="anwp-fl-v-playbtn"><span class="anwp-fl-v-playbtn__visually-hidden">Play</span></div>
								</div>
							<?php else : ?>

								<?php if ( 'site' === $additional_video['video_source'] && $additional_video['video_media_url'] ) : ?>
									<video class="anwp-video-player" playsinline controls>
										<source src="<?php echo esc_url( $additional_video['video_media_url'] ); ?>" type="video/mp4">
									</video>
								<?php elseif ( 'youtube' === $additional_video['video_source'] && $additional_video['video_id'] ) : ?>
									<div class="anwp-video-player" data-plyr-provider="youtube" data-plyr-embed-id="<?php echo esc_attr( $additional_video['video_id'] ); ?>"></div>
								<?php elseif ( 'vimeo' === $additional_video['video_source'] && $additional_video['video_id'] ) : ?>
									<div class="anwp-video-player" data-plyr-provider="vimeo" data-plyr-embed-id="<?php echo esc_attr( $additional_video['video_id'] ); ?>"></div>
								<?php endif; ?>
								<?php if ( $video_info ) : ?>
									<div class="anwp-video-grid__item-info mt-1"><?php echo esc_html( $video_info ); ?></div>
								<?php endif; ?>

							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
