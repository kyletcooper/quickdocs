<?php

/**
 * Template for displaying the plugin settings page.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

$default_tab = null;
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab; // phpcs:ignore -- This is not form data.

?>

<div class="wrap">
	<h1><?php esc_html_e( 'QuickDocs Settings', 'quickdocs' ); ?></h1>

	<nav class="nav-tab-wrapper">
		<a href="?post_type=wrd_docs&page=quickdocs-settings" class="nav-tab 
		<?php
		if ( null === $tab ) :
			?>
			nav-tab-active<?php endif; ?>">Design</a>
		<a href="?post_type=wrd_docs&page=quickdocs-settings&tab=home" class="nav-tab 
		<?php
		if ( 'home' === $tab ) :
			?>
			nav-tab-active<?php endif; ?>">Home</a>
		<a href="?post_type=wrd_docs&page=quickdocs-settings&tab=import" class="nav-tab 
		<?php
		if ( 'import' === $tab ) :
			?>
			nav-tab-active<?php endif; ?>">Import</a>
	</nav>



	<div class="tab-content">
		<?php
		switch ( $tab ) :
			case 'import':
				$GLOBALS['quickdocs_importer']->dispatch();
				break;

			case 'home':
				include_once __DIR__ . '/partials/settings-home.php';
				break;

			default:
				include_once __DIR__ . '/partials/settings-design.php';
				break;
		endswitch;
		?>
	</div>
</div>
