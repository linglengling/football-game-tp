<?php

if ( class_exists( 'WP_Customize_Control' ) ) {

	class AnWPFL_Simple_HTML_Custom_Control extends WP_Customize_Control {

		public $type = 'anwp_fl_simple_html';

		public function render_content() {
			?>
			<div class="anwp-fl-simple-html-custom-control">
				<?php if ( ! empty( $this->label ) ) : ?>
					<div class="anwp-fl-simple-html-control-title"><?php echo esc_html( $this->label ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $this->description ) ) : ?>
					<div class="anwp-fl-simple-html-control-description"><?php echo wp_kses_post( $this->description ); ?></div>
				<?php endif; ?>
			</div>
			<?php
		}
	}
}
