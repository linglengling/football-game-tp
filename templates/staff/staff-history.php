<?php
/**
 * The Template for displaying Staff >> History Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/staff/staff-history.php.
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.10.0
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
		'staff_id' => '',
		'header'   => true,
	]
);

if ( empty( $data->staff_id ) ) {
	return;
}

// History
$history = get_post_meta( $data->staff_id, '_anwpfl_staff_history_metabox_group', true );

if ( empty( $history ) || ! is_array( $history ) ) {
	return;
}
?>
<div class="staff-history anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'staff__content__career', __( 'Career', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}
	?>
	<div class="staff-history__wrapper anwp-grid-table anwp-grid-table--aligned anwp-grid-table--bordered anwp-text-base anwp-border-light anwp-overflow-x-auto">
		<div class="anwp-grid-table__th staff-history__club">
			<?php echo esc_html( AnWPFL_Text::get_value( 'staff__content__club', __( 'Club', 'anwp-football-leagues' ) ) ); ?>
		</div>
		<div class="anwp-grid-table__th staff-history__job">
			<?php echo esc_html( AnWPFL_Text::get_value( 'staff__content__job_title', __( 'Job Title', 'anwp-football-leagues' ) ) ); ?>
		</div>
		<div class="anwp-grid-table__th staff-history__from">
			<?php echo esc_html( AnWPFL_Text::get_value( 'staff__content__from', __( 'From', 'anwp-football-leagues' ) ) ); ?>
		</div>
		<div class="anwp-grid-table__th staff-history__to">
			<?php echo esc_html( AnWPFL_Text::get_value( 'staff__content__to', __( 'To', 'anwp-football-leagues' ) ) ); ?>
		</div>
		<?php
		foreach ( $history as $item ) :
			$item = wp_parse_args(
				$item,
				[
					'club' => '',
					'job'  => '',
					'from' => '',
					'to'   => '',
				]
			);
			?>
			<div class="anwp-grid-table__td staff-history__club d-flex flex-nowrap align-items-center">
				<?php if ( $item['club'] ) : ?>
					<?php
					$club_logo  = anwp_football_leagues()->club->get_club_logo_by_id( $item['club'] );
					$club_title = anwp_football_leagues()->club->get_club_title_by_id( $item['club'] );
					?>
					<?php if ( $club_logo ) : ?>
						<img loading="lazy" width="30" height="30" class="mr-2 anwp-object-contain anwp-w-30 anwp-h-30" src="<?php echo esc_url( $club_logo ); ?>" alt="<?php echo esc_html( $club_title ); ?>">
					<?php endif; ?>
					<div><?php echo esc_html( $club_title ); ?></div>
				<?php endif; ?>
			</div>
			<div class="anwp-grid-table__td staff-history__job anwp-bg-light">
				<?php echo esc_html( $item['job'] ); ?>
			</div>
			<div class="anwp-grid-table__td staff-history__from anwp-text-sm">
				<?php echo $item['from'] ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item['from'] ) ) ) : ''; ?>
			</div>
			<div class="anwp-grid-table__td staff-history__to anwp-text-sm">
				<?php echo $item['to'] ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item['to'] ) ) ) : ''; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
