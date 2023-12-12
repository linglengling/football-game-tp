<?php
/**
 * The Template for displaying Club >> Title
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/club/club-title.php.
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

// Parse template data
$data = (object) wp_parse_args(
	$data,
	[
		'club_id'    => '',
		'extra_html' => '',
		'class'      => '',
		'is_home'    => true,
		'show_link'  => true,
	]
);

if ( ! absint( $data->club_id ) ) {
	return;
}

$is_home   = AnWP_Football_Leagues::string_to_bool( $data->is_home );
$show_link = AnWP_Football_Leagues::string_to_bool( $data->show_link );
$club_obj  = anwp_football_leagues()->club->get_club( $data->club_id );

if ( empty( $club_obj->logo_big ) ) {
	if ( empty( $club_obj ) ) {
		$club_obj = (object) [
			'title' => '',
			'link'  => '',
		];
	}

	$club_obj->logo_big = anwp_football_leagues()->helper->get_default_club_logo();
}
?>
<div class="club-title p-2 d-flex align-items-center anwp-bg-light position-relative <?php echo esc_attr( $is_home ? '' : 'flex-row-reverse' ); ?> <?php echo esc_attr( $data->class ); ?>">
	<img loading="lazy" width="35" height="35" class="anwp-object-contain club-title__logo anwp-flex-none mb-0 anwp-w-35 anwp-h-35"
		src="<?php echo esc_url( $club_obj->logo_big ); ?>" alt="<?php echo esc_attr( $club_obj->title ); ?>">
	<div class="club-title__name anwp-text-xl <?php echo esc_attr( $is_home ? 'ml-3 mr-auto' : 'mr-3 ml-auto' ); ?>">
		<?php echo esc_html( $club_obj->title ); ?>
	</div>
	<?php echo $data->extra_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<?php if ( $show_link ) : ?>
		<a class="anwp-link-cover anwp-link-without-effects anwp-cursor-pointer" href="<?php echo esc_url( $club_obj->link ); ?>"></a>
	<?php endif; ?>
</div>
