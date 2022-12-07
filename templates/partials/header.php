<?php

/**
 * Template partial to open a QuickDocs page.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

?>

<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<meta name="theme-color" content="#fff" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#16161c" media="(prefers-color-scheme: dark)">

	<?php wp_head(); ?>
</head>

<body id="quickdocs" <?php body_class( array( 'quickdocs', get_option( 'qds_accent_colour', 'accent-blue' ), get_option( 'qds_font_family', 'font-poppins' ), get_option( 'qds_background_image', 'bg-wave' ) ) ); ?>>
	<script>
		// Get color scheme is saved, otherwise auto detect it
		let colorScheme = localStorage.getItem('quickdocs-colorscheme');
		if (!colorScheme) {
			const darkMode = window.matchMedia("(prefers-color-scheme:dark)").matches;
			colorScheme = darkMode ? 'dark' : 'light';
		}
		document.body.classList.add('scheme-' + colorScheme);

		if (document.body.clientWidth < 1000) {
			// Hide menu by default on mobile
			document.body.classList.add('menu-hidden');
		} else if (localStorage.getItem('quickdocs-menu-hidden') === 'true') {
			// See if user has hidden the sidebar already. Local storage always returns a string
			document.body.classList.add('menu-hidden');
		}

		<?php if ( ! get_sidebar_menu() ) : ?>
			// Hide sidebar if it doesn't exist.
			document.body.classList.add('menu-hidden');
		<?php endif; ?>

		// Run syntax highlighting
		hljs.highlightAll();
	</script>

	<?php wp_body_open(); ?>

	<div class="actionbuttons">
		<button data-colorscheme class="iconbtn colorscheme-toggle" aria-label="<?php esc_attr_e( 'Toggle colour scheme', 'quickdocs' ); ?>">
			<svg class="colorscheme-toggle-dark" xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor">
				<rect fill="none" height="24" width="24" />
				<path d="M11.1 12.08c-2.33-4.51-.5-8.48.53-10.07C6.27 2.2 1.98 6.59 1.98 12c0 .14.02.28.02.42.62-.27 1.29-.42 2-.42 1.66 0 3.18.83 4.1 2.15 1.67.48 2.9 2.02 2.9 3.85 0 1.52-.87 2.83-2.12 3.51.98.32 2.03.5 3.11.5 3.5 0 6.58-1.8 8.37-4.52-2.36.23-6.98-.97-9.26-5.41z" />
				<path d="M7 16h-.18C6.4 14.84 5.3 14 4 14c-1.66 0-3 1.34-3 3s1.34 3 3 3h3c1.1 0 2-.9 2-2s-.9-2-2-2z" />
			</svg>

			<svg class="colorscheme-toggle-light" xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="currentColor">
				<rect fill="none" height="24" width="24" />
				<path d="M12,7c-2.76,0-5,2.24-5,5s2.24,5,5,5s5-2.24,5-5S14.76,7,12,7L12,7z M2,13l2,0c0.55,0,1-0.45,1-1s-0.45-1-1-1l-2,0 c-0.55,0-1,0.45-1,1S1.45,13,2,13z M20,13l2,0c0.55,0,1-0.45,1-1s-0.45-1-1-1l-2,0c-0.55,0-1,0.45-1,1S19.45,13,20,13z M11,2v2 c0,0.55,0.45,1,1,1s1-0.45,1-1V2c0-0.55-0.45-1-1-1S11,1.45,11,2z M11,20v2c0,0.55,0.45,1,1,1s1-0.45,1-1v-2c0-0.55-0.45-1-1-1 C11.45,19,11,19.45,11,20z M5.99,4.58c-0.39-0.39-1.03-0.39-1.41,0c-0.39,0.39-0.39,1.03,0,1.41l1.06,1.06 c0.39,0.39,1.03,0.39,1.41,0s0.39-1.03,0-1.41L5.99,4.58z M18.36,16.95c-0.39-0.39-1.03-0.39-1.41,0c-0.39,0.39-0.39,1.03,0,1.41 l1.06,1.06c0.39,0.39,1.03,0.39,1.41,0c0.39-0.39,0.39-1.03,0-1.41L18.36,16.95z M19.42,5.99c0.39-0.39,0.39-1.03,0-1.41 c-0.39-0.39-1.03-0.39-1.41,0l-1.06,1.06c-0.39,0.39-0.39,1.03,0,1.41s1.03,0.39,1.41,0L19.42,5.99z M7.05,18.36 c0.39-0.39,0.39-1.03,0-1.41c-0.39-0.39-1.03-0.39-1.41,0l-1.06,1.06c-0.39,0.39-0.39,1.03,0,1.41s1.03,0.39,1.41,0L7.05,18.36z" />
			</svg>
		</button>

		<?php if ( get_sidebar_menu() ) : ?>
			<button data-menu class="iconbtn menu-toggle" aria-label="<?php esc_attr_e( 'Open sidebar', 'quickdocs' ); ?>">
				<svg id="keyboard_double_arrow_left_black_24dp" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
					<rect id="Rectangle_908" data-name="Rectangle 908" width="24" height="24" fill="none" />
					<path id="Path_633" data-name="Path 633" d="M17.59,18,19,16.59,14.42,12,19,7.41,17.59,6l-6,6Z" fill="currentColor" />
					<path id="Path_634" data-name="Path 634" d="M11,18l1.41-1.41L7.83,12l4.58-4.59L11,6,5,12Z" fill="currentColor" />
				</svg>
			</button>
		<?php endif; ?>
	</div>

	<dialog class="search" data-search-modal>
		<form class="search-form" role="search">
			<label class="search-icon-wrapper" for="search-input" aria-hidden="true">
				<svg class="search-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000">
					<path d="M0 0h24v24H0z" fill="none" />
					<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
				</svg>
			</label>

			<input id="search-input" class="search-input" type="text" name="s" data-search aria-label="<?php esc_attr_e( 'Search docs', 'quickdocs' ); ?>" role="searchbox" placeholder="<?php esc_attr_e( 'Search docs...', 'quickdocs' ); ?>" autocomplete="off">

			<kbd class="search-esc">ESC</kbd>
		</form>

		<ul class="search-results" data-search-results></ul>
	</dialog>

	<nav class="sidebar" data-sidebar>
		<button class="search-btn" data-search-open>
			<svg class="search-btn-icon" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000">
				<path d="M0 0h24v24H0z" fill="none" />
				<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
			</svg>

			<?php esc_html_e( 'Search...', 'quickdocs' ); ?>

			<kbd class="search-shortcut">CTRL + K</kbd>
		</button>

		<?php
		$parent = get_sidebar_menu_parent();
		if ( $parent ) :
			?>
			<a href="<?php the_permalink( $parent ); ?>" class="nav-parent">
				<svg xmlns="http://www.w3.org/2000/svg" height="48" width="48" viewBox="0 0 48 48">
					<path d="M28.05 36 16 23.95 28.05 11.9l2.15 2.15-9.9 9.9 9.9 9.9Z" />
				</svg>
				<?php echo esc_html( get_the_title( $parent ) ); ?>
			</a>
		<?php endif; ?>

		<?php

		if ( get_sidebar_menu() ) :
			wp_nav_menu(
				array(
					'menu'                 => get_sidebar_menu(),
					'menu_id'              => 'nav',
					'quickdocs_show_icons' => true,
				)
			);
		endif;

		?>
	</nav>

	<article class="page">
