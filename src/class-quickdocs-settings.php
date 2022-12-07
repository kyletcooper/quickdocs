<?php

/**
 * Contains the Quickdocs_Settings class.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

/**
 * Handles the QuickDocs settings page.
 *
 * @since 1.0.0
 */
class Quickdocs_Settings {

	/**
	 * Path to the template used to render the settings page.
	 *
	 * @var string TEMPLATE
	 */
	const TEMPLATE = Quickdocs_Plugin::PLUGIN_DIR . '/templates/settings.php';

	/**
	 * Adds all the hooks needed to added the settings page.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}
	}

	/**
	 * Displays the settings page.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function render(): void {
		include static::TEMPLATE;
	}

	/**
	 * Registers all QuickDocs settings.
	 *
	 * Runs on the 'admin_init' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function register_settings(): void {
		register_setting( 'quickdocs_design', 'qds_background_image' );
		register_setting( 'quickdocs_design', 'qds_accent_colour' );
		register_setting( 'quickdocs_design', 'qds_font_family' );

		register_setting( 'quickdocs_home', 'qds_home_title', array( 'default' => __( 'Documentation', 'quickdocs' ) ) );
		register_setting( 'quickdocs_home', 'qds_home_content' );
		register_setting( 'quickdocs_home', 'qds_home_show_topics', array( 'default' => true ) );
	}

	/**
	 * Adds the submenu settings page to the documentation article post type.
	 *
	 * Runs on the 'admin_menu' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function add_menu_page(): void {
		add_submenu_page(
			'edit.php?post_type=wrd_docs',
			__( 'QuickDocs Settings', 'quickdocs' ),
			__( 'Settings', 'quickdocs' ),
			'manage_options',
			'quickdocs-settings',
			array( $this, 'render' ),
			3
		);
	}

	/**
	 * Displays a select input for the settings page.
	 *
	 * @param string $name The name of the setting.
	 *
	 * @param array  $options The list of values the user can choose between. The key is used as the value of the option.
	 *
	 * @param string $default The default value of the option. Defaults to an empty string.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public static function select( string $name, array $options, string $default = '' ): void {
		$value = get_option( $name, $default );

		?>
		<select name="<?php echo esc_attr( $name ); ?>">
			<?php foreach ( $options as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php echo $value === $key ? 'selected' : null; ?>><?php echo esc_attr( $label ); ?></option>
			<?php endforeach; ?>
		</select>

		<?php
	}

	/**
	 * Displays an input for the settings page.
	 *
	 * @param string $name The name of the setting.
	 *
	 * @param string $default The default value of the option. Defaults to an empty string.
	 *
	 * @param string $type The type of the input. Defaults to 'text'.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public static function input( string $name, string $default = '', string $type = 'text' ): void {
		$value = get_option( $name, $default );

		$attrs = array(
			'name'  => $name,
			'type'  => $type,
			'value' => $value,
		);

		if ( 'checkbox' === $type || 'radio' === $type ) {
			if ( $value ) {
				$attrs['checked'] = '';
			}
			$attrs['value'] = '1';
		}

		echo '<input ';

		foreach ( $attrs as $attr => $value ) {
			echo esc_attr( $attr ) . '="' . esc_attr( $value ) . '" ';
		}

		echo '/>';
	}

	/**
	 * Displays an textarea for the settings page.
	 *
	 * @param string $name The name of the setting.
	 *
	 * @param string $default The default value of the option. Defaults to an empty string.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public static function textarea( string $name, string $default = '' ): void {
		$value = get_option( $name, $default );

		?>
		<textarea name="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $value ); ?></textarea>
		<?php
	}

	/**
	 * Displays an wysiwyg editor for the settings page.
	 *
	 * @param string $name The name of the setting.
	 *
	 * @param string $default The default value of the option. Defaults to an empty string.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public static function wysiwyg( string $name, string $default = '' ): void {
		$value = get_option( $name, $default );
		wp_editor( $value, $name );
	}
}
