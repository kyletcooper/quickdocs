<?php

/**
 * Contains the Quickdocs_Menus class.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

use WP_Post;

/**
 * Handles methods related to the custom documentation sidebar.
 *
 * Adds metaboxes to the documentation articles post type & topics term to choose a menu. Also adds a new menu location.
 *
 * @since 1.0.0
 */
class Quickdocs_Menus {


	/**
	 * The meta key to store the menu ID under.
	 *
	 * @var string MENU_KEY
	 */
	const MENU_KEY = 'quickdocs_nav_menu';

	/**
	 * The meta key to store the parent page of a menu.
	 *
	 * @var string MENU_PARENT_KEY
	 */
	const MENU_PARENT_KEY = 'quickdocs_menu_parent_page';

	/**
	 * Adds all the hooks.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		 add_action( 'init', array( $this, 'register_meta' ), 0 );
		add_action( 'after_setup_theme', array( $this, 'register_nav_menu' ), 0 );
		add_action( 'nav_menu_item_title', array( $this, 'add_nav_menu_item_icons' ), 0, 3 );

		add_filter( 'wp_get_nav_menu_items', array( $this, 'store_menu_id' ), 10, 2 );
		add_action( 'admin_footer', array( $this, 'render_metabox_menu' ) );
		add_action( Quickdocs_Posttype::TOPICS_TAXONOMY . '_add_form_fields', array( $this, 'render_metabox_tax_new' ) );
		add_action( Quickdocs_Posttype::TOPICS_TAXONOMY . '_edit_form_fields', array( $this, 'render_metabox_tax_edit' ) );
		add_action(
			'add_meta_boxes',
			function () {
				add_meta_box( 'quickdocs', __( 'Sidebar Menu', 'quickdocs' ), array( $this, 'render_metabox_post' ), Quickdocs_Posttype::POST_TYPE, 'side', 'high' );
			}
		);

		add_action( 'wp_update_nav_menu', array( $this, 'save_metabox_menu' ), 10, 2 );
		add_action( 'edit_' . Quickdocs_Posttype::TOPICS_TAXONOMY, array( $this, 'save_metabox_term' ) );
		add_action( 'create_' . Quickdocs_Posttype::TOPICS_TAXONOMY, array( $this, 'save_metabox_term' ) );
		add_action( 'save_post', array( $this, 'save_metabox_post' ), 10, 2 );
	}

	/**
	 * Registers the meta keys.
	 *
	 * On the 'init' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function register_meta(): void {
		register_meta(
			'post',
			static::MENU_KEY,
			array(
				'object_subtype' => Quickdocs_Posttype::POST_TYPE,
				'single'         => true,
				'show_in_rest'   => true,
			)
		);

		register_meta(
			'term',
			static::MENU_KEY,
			array(
				'object_subtype' => Quickdocs_Posttype::TOPICS_TAXONOMY,
				'single'         => true,
				'show_in_rest'   => true,
			)
		);
	}

	/**
	 * Registers the menu location for the global default sidebar menu.
	 *
	 * On the 'after_setup_theme' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function register_nav_menu(): void {
		register_nav_menu( static::MENU_KEY, __( 'Default Documentation Sidebar', 'quickdocs' ) );
	}

	/**
	 * Adds the article state icons to title of menu items.
	 *
	 * On the 'nav_menu_item_title' hook. Requires the 'quickdocs_show_icons' property of $args to be true.
	 *
	 * @param string   $title The menu item's title.
	 *
	 * @param WP_Post  $menu_item The current menu item object.
	 *
	 * @param stdClass $args An object of wp_nav_menu() arguments.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function add_nav_menu_item_icons( string $title, WP_Post $menu_item, $args ): string {
		if ( ! is_post_documentation( $menu_item->object_id ) ) {
			return $title;
		}

		if ( ! $args || ! property_exists( $args, 'quickdocs_show_icons' ) || false === $args->quickdocs_show_icons ) {
			return $title;
		}

		$states = get_article_states( $menu_item->object_id );

		foreach ( $states as $state ) {
			$title .= get_article_state_icon( $state );
		}

		return $title;
	}

	/**
	 * Displays the a dropdown of all menus.
	 *
	 * @param string $selected What to have selected.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function render_metabox_dropdown( string $selected = null ): void {
		wp_dropdown_categories(
			array(
				'name'             => static::MENU_KEY,
				'taxonomy'         => 'nav_menu',
				'hide_if_empty'    => false,
				'hide_empty'       => false,
				'selected'         => $selected,
				'show_option_none' => __( 'Default (inherit)', 'quickdocs' ),
			)
		);
	}

	/**
	 * Displays the metabox for a post.
	 *
	 * @param WP_Post $post The post to render for.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function render_metabox_post( WP_Post $post ): void {
		wp_nonce_field( 'quickdocs_metabox', 'quickdocs_metabox_nonce' );
		$selected = get_post_meta( $post->ID, static::MENU_KEY, true );

		$this->render_metabox_dropdown( $selected );
	}

	/**
	 * Displays the metabox for a new term.
	 *
	 * Runs on the 'topic_new_form_fields' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function render_metabox_tax_new(): void {
		wp_nonce_field( 'quickdocs_metabox', 'quickdocs_metabox_nonce' );
		?>

		<div class="form-field term-meta-text-wrap">
			<label for="term-meta-text"><?php esc_html_e( 'Documentation Sidebar', 'text_domain' ); ?></label>
			<?php $this->render_metabox_dropdown(); ?>
		</div>
		<?php
	}

	/**
	 * Displays the metabox for an existing term.
	 *
	 * Runs on the 'topic_edit_form_fields' hook.
	 *
	 * @param \WP_Term $term The term being edited.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function render_metabox_tax_edit( \WP_Term $term ): void {
		wp_nonce_field( 'quickdocs_metabox', 'quickdocs_metabox_nonce' );
		$selected = get_term_meta( $term->term_id, static::MENU_KEY, true );
		?>

		<tr class="form-field term-meta-text-wrap">
			<th scope="row">
				<label for="term-meta-text"><?php esc_html_e( 'Documentation Sidebar', 'quickdocs' ); ?></label>
			</th>
			<td>
				<?php $this->render_metabox_dropdown( $selected ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Stores the menu ID so we can use it later in the hook cycle.
	 *
	 * @param array  $items An array of menu item post objects.
	 *
	 * @param object $menu The menu object.
	 *
	 * @param $menu
	 *
	 * Runs on the 'wp_get_nav_menu_items' hook.
	 */
	public function store_menu_id( $items, $menu ) {
		$this->menu = $menu->term_id;
		return $items;
	}

	/**
	 * Displays the metabox on the menu editor so we can select the menu's parent page.
	 *
	 * Runs on the 'admin_footer' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function render_metabox_menu(): void {
		$screen = get_current_screen();

		if ($screen->base !== 'nav-menus' || array_key_exists('action', $_GET)) { // phpcs:ignore -- This is not form data, this checks we're on the correct admin page.
			return;
		}

		$term_id = $this->menu;
		?>
		<div id="quickdocs-menu-meta" style="display: none; margin-top: 10px;">
			<?php wp_nonce_field( 'quickdocs_metabox', 'quickdocs_metabox_nonce' ); ?>
			<fieldset class="menu-settings-group menu-parent-page">
				<legend class="menu-settings-group-name howto"><?php esc_html_e( 'Parent page', 'quickdocs' ); ?></legend>
				<div class="menu-settings-input select-input">
					<?php

					dropdown_documentation(
						array(
							'selected'          => get_term_meta( $term_id, static::MENU_PARENT_KEY, true ),
							'dropdown_name'     => static::MENU_PARENT_KEY,
							'show_option_none'  => __( 'No parent', 'quickdocs' ),
							'option_none_value' => -1,
						)
					);

					?>
				</div>
			</fieldset>
		</div>
		<script>
			(function($) {
				$('#quickdocs-menu-meta').appendTo("#post-body-content").css("display", "block");
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Wrapper for save_metabox with the 'term' object type.
	 *
	 * Runs on the 'wp_update_nav_menu' hook.
	 *
	 * @param int $menu_id The ID of the menu to update.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @see save_metabox()
	 */
	public function save_metabox_menu( $menu_id ): void {
		$this->save_metabox( $menu_id, 'term' );
	}

	/**
	 * Wrapper for save_metabox with the 'post' object type.
	 *
	 * Runs on the 'save_post' hook.
	 *
	 * @param int $post_id The ID of the post to update.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @see save_metabox()
	 */
	public function save_metabox_post( $post_id ): void {
		$this->save_metabox( $post_id, 'post' );
	}

	/**
	 * Wrapper for save_metabox with the 'term' object type.
	 *
	 * Runs on the 'edit_topic' & 'create_topic' hooks.
	 *
	 * @param int $term_id The ID of the term to update.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @see save_metabox()
	 */
	public function save_metabox_term( $term_id ): void {
		$this->save_metabox( $term_id, 'term' );
	}

	/**
	 * Saves the meta values sent for the meta keys this class handles.
	 *
	 * @param int    $object_id The ID of the object to save the metadata to.
	 *
	 * @param string $object_type The type of object the ID refers to. E.g. post, term etc.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function save_metabox( $object_id, $object_type ): void {
		$meta_keys = array( static::MENU_KEY, static::MENU_PARENT_KEY );

		foreach ( $meta_keys as $meta_key ) {
			if ( ! array_key_exists( $meta_key, $_REQUEST ) ) {
				continue;
			}

			if ( 'post' === $object_type ) {
				if ( ! current_user_can( 'edit_post_meta', $object_id, $meta_key ) ) {
					continue;
				}
			} else {
				if ( ! current_user_can( 'manage_categories' ) ) {
					continue;
				}
			}

			if (!isset($_REQUEST['quickdocs_metabox_nonce'])) { // phpcs:ignore -- Nonce does not need sanitization.
				continue;
			}

			$nonce = wp_unslash($_REQUEST['quickdocs_metabox_nonce']); // phpcs:ignore -- Nonce does not need sanitization.

			if ( ! wp_verify_nonce( $nonce, 'quickdocs_metabox' ) ) {
				continue;
			}

			$value = sanitize_meta( $meta_key, wp_unslash( $_REQUEST[ $meta_key ] ), $object_type );
			update_metadata( $object_type, $object_id, $meta_key, $value );
		}
	}
}
