<?php
/**
 * The Template for displaying Cards of players or teams.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/shortcode-cards--mini.php.
 *
 * @var object $data - Object with widget data.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.7.3
 *
 * @version       0.14.11
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$default_photo = anwp_football_leagues()->helper->get_default_player_photo();

$data = (object) wp_parse_args(
	$data,
	[
		'competition_id' => '',
		'join_secondary' => 0,
		'season_id'      => '',
		'league_id'      => '',
		'club_id'        => '',
		'type'           => 'players',
		'limit'          => 0,
		'soft_limit'     => 'yes',
		'context'        => 'shortcode',
		'show_photo'     => 'yes',
		'points_r'       => '5',
		'points_yr'      => '2',
		'hide_zero'      => 0,
		'hide_points'    => 0,
		'sort_by_point'  => '',
	]
);

// Get list of items
$items = anwp_football_leagues()->player->tmpl_get_players_cards( $data );

if ( empty( $items ) ) {
	return;
}

// Limit number of players
if ( (int) $data->limit > 0 ) {
	$items = anwp_football_leagues()->player->tmpl_limit_players( $items, $data->limit, $data->soft_limit );
}

// Prepare players cache
if ( 'players' === $data->type ) {
	$ids = wp_list_pluck( $items, 'player_id' );
	anwp_football_leagues()->player->set_players_cache( $ids );
}

/**
 * Add option to hide points column.
 *
 * @since 0.9.0
 */
$hide_points = AnWP_Football_Leagues::string_to_bool( $data->hide_points );


$col_span = $hide_points ? 5 : 6;
?>
<div class="anwp-b-wrap cards-shortcode-mini context--<?php echo esc_attr( $data->context ); ?> anwp-grid-table anwp-grid-table--aligned anwp-grid-table--bordered anwp-text-xs anwp-border-light"
	style="--cards-shortcode-cols: <?php echo absint( $col_span ) - 2; ?>">

	<div class="anwp-grid-table__th anwp-border-light cards-shortcode-mini__rank justify-content-center anwp-bg-light anwp-text-xs">
		<?php echo esc_html( AnWPFL_Text::get_value( 'cards__shortcode__n', _x( '#', 'Rank', 'anwp-football-leagues' ) ) ); ?>
	</div>

	<div class="anwp-grid-table__th anwp-border-light cards-shortcode-mini__clubs-players anwp-bg-light anwp-text-xs">
		<?php echo esc_html( 'clubs' === $data->type ? AnWPFL_Text::get_value( 'cards__shortcode__clubs', __( 'Clubs', 'anwp-football-leagues' ) ) : AnWPFL_Text::get_value( 'cards__shortcode__player', __( 'Player', 'anwp-football-leagues' ) ) ); ?>
	</div>

	<div class="anwp-grid-table__th anwp-border-light cards-shortcode-mini__cards_y justify-content-center anwp-bg-light">
		<svg class="icon__card">
			<use xlink:href="#icon-card_y"></use>
		</svg>
	</div>

	<div class="anwp-grid-table__th anwp-border-light cards-shortcode-mini__card_yr justify-content-center anwp-bg-light">
		<svg class="icon__card">
			<use xlink:href="#icon-card_yr"></use>
		</svg>
	</div>

	<div class="anwp-grid-table__th anwp-border-light cards-shortcode-mini__card_r justify-content-center anwp-bg-light">
		<svg class="icon__card">
			<use xlink:href="#icon-card_r"></use>
		</svg>
	</div>

	<?php if ( ! $hide_points ) : ?>
		<div class="anwp-grid-table__th anwp-border-light cards-shortcode-mini__pts justify-content-center anwp-bg-light anwp-text-xs">
			<?php echo esc_html( AnWPFL_Text::get_value( 'cards__shortcode__pts', _x( 'Pts', 'points', 'anwp-football-leagues' ) ) ); ?>
		</div>
	<?php endif; ?>

	<?php foreach ( $items as $index => $p ) : ?>
		<div class="anwp-grid-table__td cards-shortcode-mini__rank justify-content-center">
			<?php echo intval( $index + 1 ); ?>
		</div>
		<div class="anwp-grid-table__td cards-shortcode-mini__clubs-players anwp-overflow-x-hidden">
			<?php
			if ( 'players' === $data->type ) :

				$player = anwp_football_leagues()->player->get_player( $p->player_id );
				$clubs  = explode( ',', $p->clubs );
				?>
				<div class="d-flex align-items-start position-relative">
					<?php if ( AnWP_Football_Leagues::string_to_bool( $data->show_photo ) ) : ?>
						<img loading="lazy" width="40" height="40" class="anwp-object-contain mr-2 anwp-w-40 anwp-h-40"
							src="<?php echo esc_url( $player->photo ?: $default_photo ); ?>" alt="<?php echo esc_attr( $player->name ); ?>">
					<?php endif; ?>
					<div class="d-flex flex-column">
						<div class="cards-shortcode-mini__player-name my-1"><?php echo esc_html( $player->name_short ); ?></div>

						<?php foreach ( $clubs as $ii => $club ) : ?>
							<div class="cards-shortcode-mini__player-club anwp-text-xs anwp-opacity-80 anwp-leading-1">
								<?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $club ) ); ?>
							</div>
						<?php endforeach; ?>
					</div>

					<a class="anwp-link-cover anwp-link-without-effects" title="<?php echo esc_attr( $player->name ); ?>" href="<?php echo esc_url( $player->link ); ?>"></a>
				</div>
			<?php elseif ( 'clubs' === $data->type ) : ?>
				<div class="d-flex align-items-center position-relative">

					<?php if ( AnWP_Football_Leagues::string_to_bool( $data->show_photo ) ) : ?>
						<img loading="lazy" width="25" height="25" class="anwp-object-contain mr-2 anwp-w-25 anwp-h-25"
							src="<?php echo esc_url( anwp_football_leagues()->club->get_club_logo_by_id( $p->club_id ) ); ?>"
							alt="<?php echo esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $p->club_id ) ); ?>">
					<?php endif; ?>
					<div class="cards-shortcode-mini__club-name">
						<?php echo esc_html( anwp_football_leagues()->club->get_club_title_by_id( $p->club_id ) ); ?>
					</div>

					<a class="anwp-link-cover anwp-link-without-effects" title="<?php echo esc_attr( anwp_football_leagues()->club->get_club_title_by_id( $p->club_id ) ); ?>" href="<?php echo esc_url( anwp_football_leagues()->club->get_club_link_by_id( $p->club_id ) ); ?>"></a>
				</div>
			<?php endif; ?>
		</div>

		<div class="anwp-grid-table__td cards-shortcode-mini__cards_y justify-content-center">
			<?php echo (int) $p->cards_y; ?>
		</div>

		<div class="anwp-grid-table__td cards-shortcode-mini__cards_yr justify-content-center">
			<?php echo (int) $p->cards_yr; ?>
		</div>

		<div class="anwp-grid-table__td cards-shortcode-mini__cards_r justify-content-center">
			<?php echo (int) $p->cards_r; ?>
		</div>

		<?php if ( ! $hide_points ) : ?>
			<div class="anwp-grid-table__td cards-shortcode-mini__countable justify-content-center">
				<?php echo (int) $p->countable; ?>
			</div>
		<?php endif; ?>

	<?php endforeach; ?>
</div>
