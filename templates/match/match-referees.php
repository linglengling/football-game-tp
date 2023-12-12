<?php
/**
 * The Template for displaying Match >> Referees Section.
 *
 * This template can be overridden by copying it to yourtheme/anwp-football-leagues/match/match-referees.php.
 *
 * phpcs:disable WordPress.NamingConventions.ValidVariableName
 *
 * @var object $data - Object with args.
 *
 * @author        Andrei Strekozov <anwp.pro>
 * @package       AnWP-Football-Leagues/Templates
 * @since         0.7.3
 *
 * @version       0.14.15
 */

// phpcs:disable WordPress.NamingConventions.ValidVariableName

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = (object) wp_parse_args(
	$data,
	[
		'context'           => '',
		'match_id'          => '',
		'referee_id'        => '',
		'assistant_1'       => '',
		'assistant_2'       => '',
		'referee_fourth_id' => '',
		'header'            => true,
	]
);

if ( empty( $data->match_id ) ) {
	return '';
}

// Try to get data directly when used in shortcode
if ( 'shortcode' === $data->context ) {
	$data->referee_id        = get_post_meta( $data->match_id, '_anwpfl_referee', true );
	$data->assistant_1       = get_post_meta( $data->match_id, '_anwpfl_assistant_1', true );
	$data->assistant_2       = get_post_meta( $data->match_id, '_anwpfl_assistant_2', true );
	$data->referee_fourth_id = get_post_meta( $data->match_id, '_anwpfl_referee_fourth', true );
}

if ( empty( $data->referee_id ) && empty( $data->assistant_1 ) && empty( $data->assistant_2 ) && empty( $data->referee_fourth_id ) ) {
	return '';
}

$additional_referees = get_post_meta( $data->match_id, '_anwpfl_additional_referees', true );
$temp_referees       = get_post_meta( $data->match_id, '_anwpfl_temp_referees', true );

/**
 * Hook: anwpfl/tmpl-match/referees_before
 *
 * @param object $data Match data
 *
 * @since 0.7.5
 */
do_action( 'anwpfl/tmpl-match/referees_before', $data );

$referee_keys = [
	'referee_id'        => [
		'slug'      => 'referee',
		'l10n_key'  => 'match__referees__referee',
		'l10n_text' => __( 'Referee', 'anwp-football-leagues' ),
	],
	'assistant_1'       => [
		'slug'      => 'assistant_1',
		'l10n_key'  => 'match__referees__assistant',
		'l10n_text' => __( 'Assistant Referee', 'anwp-football-leagues' ) . ' 1',
	],
	'assistant_2'       => [
		'slug'      => 'assistant_2',
		'l10n_key'  => 'match__referees__assistant',
		'l10n_text' => __( 'Assistant Referee', 'anwp-football-leagues' ) . ' 2',
	],
	'referee_fourth_id' => [
		'slug'      => 'referee_fourth',
		'l10n_key'  => 'match__referees__fourth_official',
		'l10n_text' => __( 'Fourth official', 'anwp-football-leagues' ),
	],
];
?>
<div class="match-referees anwp-section">

	<?php
	/*
	|--------------------------------------------------------------------
	| Block Header
	|--------------------------------------------------------------------
	*/
	if ( AnWP_Football_Leagues::string_to_bool( $data->header ) ) {
		anwp_football_leagues()->load_partial(
			[
				'text' => AnWPFL_Text::get_value( 'match__referees__referees', __( 'Referees', 'anwp-football-leagues' ) ),
			],
			'general/header'
		);
	}
	?>

	<div class="match__referee-outer py-2 d-flex flex-wrap anwp-fl-border-bottom anwp-border-light anwp-text-base">
		<?php foreach ( [ 'referee_id', 'assistant_1', 'assistant_2', 'referee_fourth_id' ] as $referee_slug ) : ?>
			<?php if ( ! empty( $data->{$referee_slug} ) ) : ?>
				<div class="match__referee-wrapper d-flex align-items-center mr-4">
					<span class="match__referee-job anwp-text-sm anwp-opacity-80 mr-2"><?php echo esc_html( AnWPFL_Text::get_value( $referee_keys[ $referee_slug ]['l10n_key'], $referee_keys[ $referee_slug ]['l10n_text'] ) ); ?>:</span>

					<?php
					$referee_data = 'temp' === $data->{$referee_slug} ? (object) wp_parse_args(
						$temp_referees[ $referee_keys[ $referee_slug ]['slug'] ],
						[
							'country' => '',
							'name'    => '',
						]
					) : anwp_football_leagues()->referee->get_referee( $data->{$referee_slug} );

					if ( ! empty( $referee_data->country ) ) :
						anwp_football_leagues()->load_partial(
							[
								'class'        => 'options__flag mr-1 d-flex align-items-center',
								'size'         => 16,
								'country_code' => $referee_data->country,
							],
							'general/flag'
						);
					endif;

					if ( ! empty( $referee_data->country_2 ) ) :
						anwp_football_leagues()->load_partial(
							[
								'class'        => 'options__flag mr-1 d-flex align-items-center',
								'size'         => 16,
								'country_code' => $referee_data->country_2,
							],
							'general/flag'
						);
					endif;

					if ( 'temp' === $data->{$referee_slug} ) :
						echo '<span class="match__referee-name">' . esc_html( $referee_data->name ) . '</span>';
					elseif ( anwp_football_leagues()->referee->get_referee( $data->{$referee_slug} ) ) :
						?>
						<a class="match__referee-name anwp-link-without-effects" href="<?php echo esc_url( $referee_data->link ); ?>">
							<?php echo esc_html( $referee_data->name ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<?php if ( ! empty( $additional_referees ) ) : ?>
		<div class="py-2 d-flex flex-wrap anwp-fl-border-bottom anwp-border-light anwp-text-base">
			<?php foreach ( $additional_referees as $additional_referee ) : ?>
				<?php if ( ! empty( $additional_referee['_anwpfl_referee'] ) && ( absint( $additional_referee['_anwpfl_referee'] ) || 'temp__' === mb_substr( $additional_referee['_anwpfl_referee'], 0, 6 ) ) ) : ?>
					<div class="match__referee-wrapper d-flex align-items-center mr-4">
						<?php if ( ! empty( $additional_referee['role'] ) ) : ?>
							<span class="match__referee-job anwp-text-sm anwp-opacity-80 mr-2"><?php echo esc_html( $additional_referee['role'] ); ?>:</span>
						<?php endif; ?>

						<?php
						$referee_data = 'temp__' === mb_substr( $additional_referee['_anwpfl_referee'], 0, 6 ) ? (object) wp_parse_args(
							$temp_referees['additional_referees'][ mb_substr( $additional_referee['_anwpfl_referee'], 6 ) ],
							[
								'country' => '',
								'name'    => '',
							]
						) : anwp_football_leagues()->referee->get_referee( $additional_referee['_anwpfl_referee'] );

						if ( ! empty( $referee_data->country ) ) :
							anwp_football_leagues()->load_partial(
								[
									'class'        => 'options__flag mr-1 d-flex align-items-center',
									'size'         => 16,
									'country_code' => $referee_data->country,
								],
								'general/flag'
							);
						endif;

						if ( ! empty( $referee_data->country_2 ) ) :
							anwp_football_leagues()->load_partial(
								[
									'class'        => 'options__flag mr-1 d-flex align-items-center',
									'size'         => 16,
									'country_code' => $referee_data->country_2,
								],
								'general/flag'
							);
						endif;

						if ( 'temp__' === mb_substr( $additional_referee['_anwpfl_referee'], 0, 6 ) ) :
							echo '<span class="match__referee-name">' . esc_html( $referee_data->name ) . '</span>';
						elseif ( anwp_football_leagues()->referee->get_referee( $additional_referee['_anwpfl_referee'] ) ) :
							?>
							<a class="match__referee-name anwp-link-without-effects" href="<?php echo esc_url( $referee_data->link ); ?>">
								<?php echo esc_html( $referee_data->name ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
