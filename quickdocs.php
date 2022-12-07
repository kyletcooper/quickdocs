<?php

/**
 * Contains the Quickdocs_Plugin class.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

/**
 * Plugin Name:       QuickDocs
 * Description:       Quickly create modern, function documentation without making a new site.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4.0
 * Author:            Web Results Direct
 * Author URI:        https://wrd.studio
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       quickdocs
 * Domain Path:       /languages
 */

namespace quickdocs;

/**
 * Manages setting up the various areas of the plugin.
 *
 * @since 1.0.0
 */
class Quickdocs_Plugin
{


	const PLUGIN_FILE = __FILE__;
	const VERSION     = '1.0.0';
	const PLUGIN_DIR  = WP_PLUGIN_DIR . '/quickdocs';
	const PLUGIN_URL  = WP_PLUGIN_URL . '/quickdocs';

	/**
	 * Includes plugin files that are used in every area of WordPress (public, admin, ajax, rest).
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		require_once static::PLUGIN_DIR . '/src/class-metabox.php';
		require_once static::PLUGIN_DIR . '/src/class-quickdocs-posttype.php';
		require_once static::PLUGIN_DIR . '/src/class-quickdocs-menus.php';
		require_once static::PLUGIN_DIR . '/src/class-quickdocs-settings.php';
		require_once static::PLUGIN_DIR . '/src/class-quickdocs-importer.php';
		require_once static::PLUGIN_DIR . '/src/class-table-of-contents.php';
		require_once static::PLUGIN_DIR . '/src/template-tags.php';

		new Quickdocs_Posttype();
		new Quickdocs_Menus();
		new Quickdocs_Settings();
		new Quickdocs_Importer();

		register_activation_hook(__FILE__, array($this, 'on_activation'));
		register_deactivation_hook(__FILE__, array($this, 'on_deactivation'));

		add_action('rest_api_init', array($this, 'register_endpoints'));
	}

	/**
	 * Adds routes to the Rest API.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function register_endpoints(): void
	{
		require_once static::PLUGIN_DIR . '/src/routes/class-custom-route-quickdocs-helpful.php';
		require_once static::PLUGIN_DIR . '/src/routes/class-custom-rest-field-quickdocs-search.php';

		$route_helpful = new Custom_Route_Quickdocs_Helpful();
		$route_helpful->register_routes();

		$field_search = new Custom_Rest_Field_Quickdocs_Search();
		$field_search->register_fields();
	}

	/**
	 * Runs when the plugin is activated.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function on_activation(): void
	{
		if (!get_option('quickdocs_flush_rewrite_rules')) {
			add_option('quickdocs_flush_rewrite_rules', true);
		}
	}

	/**
	 * Runs when the plugin is deactivated.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function on_deactivation(): void
	{
		flush_rewrite_rules();
	}
}

new Quickdocs_Plugin();
