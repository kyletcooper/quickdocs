<?php

/**
 * Partial for showing the Design tab in the plugin settings page.
 * 
 * @since 1.0.0
 * 
 * @package Quickdocs
 */

namespace quickdocs; ?>

<form method="post" action="options.php">
	<?php settings_fields('quickdocs_design'); ?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<?php esc_html_e('Background Graphic', 'quickdocs'); ?>
			</th>

			<td>
				<?php Quickdocs_Settings::select('qds_background_image', [
					'bg-wave' => __('Wave', 'quickdocs'),
					'bg-grid' => __('Grid', 'quickdocs'),
					'bg-dots' => __('Dots', 'quickdocs'),
					'bg-none' => __('None', 'quickdocs'),
				]) ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<?php esc_html_e('Colour Scheme', 'quickdocs'); ?>
			</th>

			<td>
				<?php Quickdocs_Settings::select('qds_accent_colour', [
					'accent-blue'   => __('Blue', 'quickdocs'),
					'accent-teal'   => __('Teal', 'quickdocs'),
					'accent-green'  => __('Green', 'quickdocs'),
					'accent-orange' => __('Orange', 'quickdocs'),
					'accent-red'    => __('Red', 'quickdocs'),
					'accent-purple' => __('Purple', 'quickdocs'),
					'accent-fuscia' => __('Fuscia', 'quickdocs'),
				]) ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<?php esc_html_e('Font Family', 'quickdocs'); ?>
			</th>

			<td>
				<?php Quickdocs_Settings::select('qds_font_family', [
					'font-arial'       => __('Arial', 'quickdocs'),
					'font-helvetica'   => __('Helvetica', 'quickdocs'),
					'font-lora'        => __('Lora', 'quickdocs'),
					'font-montserrat'  => __('Montserrat', 'quickdocs'),
					'font-nunito'      => __('Nunito', 'quickdocs'),
					'font-open-sans'   => __('Open Sans', 'quickdocs'),
					'font-poppins'     => __('Poppins', 'quickdocs'),
					'font-roboto'      => __('Roboto', 'quickdocs'),
					'font-roboto-slab' => __('Roboto Slab', 'quickdocs'),
					'font-verdana'     => __('Verdana', 'quickdocs'),
					'font-work-sans'   => __('Work Sans', 'quickdocs'),
				], 'font-poppins') ?>
			</td>
		</tr>

	</table>

	<?php submit_button(); ?>

	<p>
		<a href="<?php echo esc_url(admin_url('nav-menus.php?action=locations')) ?>">
			<?php esc_html_e('Edit sidebar menu', 'quickdocs'); ?>
		</a>
	</p>
</form>