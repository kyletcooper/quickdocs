<?php

/**
 * Template to render the Article State metabox in the block editor.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

$state = get_post_meta( get_the_ID(), 'quickdocs_state', true );
if ( ! is_array( $state ) ) {
	$state = array();
}

?>

<input type="hidden" name="quickdocs_state[]" value="normal" checked>

<p>
	<label>
		<input type="checkbox" name="quickdocs_state[]" value="archived" 
		<?php
		if ( in_array( 'archived', $state, true ) ) {
			echo 'checked';}
		?>
		>
		<?php esc_html_e( 'Archived', 'quickdocs' ); ?>
	</label>
</p>
<p>
	<label>
		<input type="checkbox" name="quickdocs_state[]" value="incomplete" 
		<?php
		if ( in_array( 'incomplete', $state, true ) ) {
			echo 'checked';}
		?>
		>
		<?php esc_html_e( 'Incomplete', 'quickdocs' ); ?>
	</label>

</p>
<p>
	<label>
		<input type="checkbox" name="quickdocs_state[]" value="deprecated" 
		<?php
		if ( in_array( 'deprecated', $state, true ) ) {
			echo 'checked';}
		?>
		>
		<?php esc_html_e( 'Deprecated', 'quickdocs' ); ?>
	</label>

</p>
<p>
	<label>
		<input type="checkbox" name="quickdocs_state[]" value="experimental" 
		<?php
		if ( in_array( 'experimental', $state, true ) ) {
			echo 'checked';}
		?>
		>
		<?php esc_html_e( 'Experimental', 'quickdocs' ); ?>
	</label>
</p>
<p>
	<label>
		<input type="checkbox" name="quickdocs_state[]" value="premium" 
		<?php
		if ( in_array( 'premium', $state, true ) ) {
			echo 'checked';}
		?>
		>
		<?php esc_html_e( 'Premium', 'quickdocs' ); ?>
	</label>
</p>
