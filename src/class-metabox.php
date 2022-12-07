<?php

/**
 * Contains the Metabox class.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

use WP_Post;

/**
 * Registers, displays and saves the value for a metabox attached to a post type.
 *
 * @since 1.0.0
 */
class Metabox {

	/**
	 * The slug of the metabox.
	 *
	 * @var string $id
	 */
	public string $id;

	/**
	 * The display title of the metabox.
	 *
	 * @var string $title
	 */
	public string $title;

	/**
	 * The file used to render the metabox.
	 *
	 * @var string $id
	 */
	public string $template;

	/**
	 * The post type to display this metabox on. Defaults to 'post'.
	 *
	 * @var string $post_type
	 */
	public string $post_type = 'post';

	/**
	 * The meta keys of the data stored by the metabox.
	 *
	 * @var array $meta_keys
	 */
	public array $meta_keys = array();

	/**
	 * Creates a new metabox and adds it's hooks.
	 *
	 * @param string $title The display name of the metabox.
	 *
	 * @param string $template The path of the template file used to display the metabox.
	 *
	 * @param string $post_type The post type to display this metabx on. Defaults to 'post'.
	 *
	 * @param array  $meta_keys Array of meta keys this metabox is responsible for saving the values of. Defaults to empty array.
	 *
	 * @since 1.0.0
	 */
	public function __construct( string $title, string $template, string $post_type = 'post', array $meta_keys = array() ) {
		$this->id        = sanitize_title( $title );
		$this->title     = $title;
		$this->template  = $template;
		$this->meta_keys = $meta_keys;
		$this->post_type = $post_type;

		add_action( 'init', array( $this, 'register_meta' ), 0 );
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_action( "save_post_$this->post_type", array( $this, 'save_metabox' ), 10, 2 );
	}

	/**
	 * Registers all of the meta keys.
	 *
	 * Runs on the 'init' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function register_meta(): void {
		foreach ( $this->meta_keys as $key ) {
			register_meta(
				'post',
				$key,
				array(
					'object_subtype' => $this->post_type,
					'single'         => true,
					'show_in_rest'   => true,
				)
			);
		}
	}

	/**
	 * Registers the meta box.
	 *
	 * Runs on the 'add_meta_boxes' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function register_metabox(): void {
		add_meta_box( $this->id, $this->title, array( $this, 'render_metabox_post' ), $this->post_type, 'side', 'high' );
	}

	/**
	 * Adds the nonce and includes the metabox's template file.
	 *
	 * @param WP_Post $post The post to render.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function render_metabox_post( WP_Post $post ): void {
		wp_nonce_field( "metabox_$this->id", "nonce_$this->id" );
		include $this->template;
	}

	/**
	 * Saves the results of the metabox when submitted.
	 *
	 * Runs on the 'save_post_$this->post_type' hook.
	 *
	 * @param int $post_id The post ID to save to.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function save_metabox( int $post_id ): void {
		if ( ! array_key_exists( "nonce_$this->id", $_REQUEST ) ) {
			return;
		}

		if (!wp_verify_nonce(wp_unslash($_REQUEST["nonce_$this->id"]), "metabox_$this->id")) { // phpcs:ignore -- Nonce does not need to be sanitized.
			return;
		}

		foreach ( $this->meta_keys as $key ) {
			if ( ! array_key_exists( $key, $_REQUEST ) ) {
				continue;
			}

			if ( ! current_user_can( 'edit_post_meta', $post_id, $key ) ) {
				continue;
			}

			$value = sanitize_meta( $key, wp_unslash( $_REQUEST[ $key ] ), 'post', $this->post_type );
			update_post_meta( $post_id, $key, $value );
		}
	}
}
