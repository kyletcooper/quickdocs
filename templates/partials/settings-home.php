<?php

/**
 * Partial for showing the Home tab in the plugin settings page.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

?>

<form method="post" action="options.php">
	<?php settings_fields( 'quickdocs_home' ); ?>
	<table class="form-table">

		<tr valign="top">
			<th scope="row">
				<?php esc_html_e( 'Title', 'quickdocs' ); ?>
			</th>

			<td>
				<?php Quickdocs_Settings::input( 'qds_home_title' ); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<?php esc_html_e( 'Introduction', 'quickdocs' ); ?>
			</th>

			<td>
				<?php Quickdocs_Settings::wysiwyg( 'qds_home_content' ); ?>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<?php esc_html_e( 'Show Topics', 'quickdocs' ); ?>
			</th>

			<td>
				<?php Quickdocs_Settings::input( 'qds_home_show_topics', true, 'checkbox' ); ?>
			</td>
		</tr>

	</table>

	<?php submit_button(); ?>
</form>
