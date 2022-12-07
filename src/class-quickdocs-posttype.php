<?php

/**
 * Contains the Quickdocs_Posttype class.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

/**
 * Handles everything to do with the documentation article posttype.
 *
 * Registers the post type, redirects templates, enqueues assets, changes the archive title/description and creates metaboxes.
 */
class Quickdocs_Posttype {


	/**
	 * The registered name of the topics taxonomy.
	 *
	 * @var string TOPICS_TAXONOMY
	 *
	 * @since 1.0.0
	 */
	const TOPICS_TAXONOMY = 'topic';

	/**
	 * The registered name of the documentation article post type.
	 *
	 * @var string POST_TYPE
	 *
	 * @since 1.0.0
	 */
	const POST_TYPE = 'wrd_docs';

	/**
	 * The meta key used to store how many users found an article helpful.
	 *
	 * @var string HELPFUL_META_KEY
	 *
	 * @since 1.0.0
	 */
	const HELPFUL_META_KEY = 'found_helpful';

	/**
	 * The meta key used to store how many users found an article unhelpful.
	 *
	 * @var string UNHELPFUL_META_KEY
	 *
	 * @since 1.0.0
	 */
	const UNHELPFUL_META_KEY = 'found_unhelpful';

	/**
	 * Adds all the hooks required to create the post type.
	 */
	public function __construct() {
		 add_action( 'init', array( $this, 'register_post_type' ), 0 );
		add_action( 'init', array( $this, 'register_taxonomy' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 1000 );

		add_action( 'template_include', array( $this, 'template_include' ) );

		add_action( 'admin_notices', array( $this, 'helpfulness_notice' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'helpfulness_notice_gutenberg' ) );

		add_filter( 'excerpt_length', array( $this, 'excerpt_length' ) );
		add_filter( 'excerpt_more', array( $this, 'excerpt_more' ) );
		add_filter( 'get_the_archive_title', array( $this, 'get_the_archive_title' ), 10, 2 );
		add_filter( 'get_the_archive_description', array( $this, 'get_the_archive_description' ) );

		$this->register_metaboxes();
	}

	/**
	 * Creates the post type.
	 *
	 * Runs on the 'init' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function register_post_type(): void {
		$args = array(
			'label'               => __( 'Documentation', 'quickdocs' ),
			'labels'              => array(
				'name'               => __( 'Documentation', 'quickdocs' ),
				'singular_name'      => __( 'Documentation', 'quickdocs' ),
				'add_new'            => __( 'Add Article', 'textdomain' ),
				'add_new_item'       => __( 'Add New Article', 'textdomain' ),
				'new_item'           => __( 'New Article', 'textdomain' ),
				'edit_item'          => __( 'Edit Article', 'textdomain' ),
				'view_item'          => __( 'View Article', 'textdomain' ),
				'all_items'          => __( 'All Articles', 'textdomain' ),
				'search_items'       => __( 'Search Articles', 'textdomain' ),
				'not_found'          => __( 'No Articles found.', 'textdomain' ),
				'not_found_in_trash' => __( 'No Articles found in Trash.', 'textdomain' ),
			),
			'supports'            => array( 'title', 'editor', 'revisions', 'excerpt' ),
			'taxonomies'          => array( 'topic' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDI0IDI0IiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgd2lkdGg9IjI0cHgiIGZpbGw9IiMwMDAwMDAiPgo8cGF0aCBkPSJNOSw0djEuMzhjLTAuODMtMC4zMy0xLjcyLTAuNS0yLjYxLTAuNWMtMS43OSwwLTMuNTgsMC42OC00Ljk1LDIuMDVsMy4zMywzLjMzaDEuMTF2MS4xMWMwLjg2LDAuODYsMS45OCwxLjMxLDMuMTEsMS4zNiBWMTVINnYzYzAsMS4xLDAuOSwyLDIsMmgxMGMxLjY2LDAsMy0xLjM0LDMtM1Y0SDl6IE03Ljg5LDEwLjQxVjguMjZINS42MUw0LjU3LDcuMjJDNS4xNCw3LDUuNzYsNi44OCw2LjM5LDYuODggYzEuMzQsMCwyLjU5LDAuNTIsMy41NCwxLjQ2bDEuNDEsMS40MWwtMC4yLDAuMmMtMC41MSwwLjUxLTEuMTksMC44LTEuOTIsMC44QzguNzUsMTAuNzUsOC4yOSwxMC42Myw3Ljg5LDEwLjQxeiBNMTksMTcgYzAsMC41NS0wLjQ1LDEtMSwxcy0xLTAuNDUtMS0xdi0yaC02di0yLjU5YzAuNTctMC4yMywxLjEtMC41NywxLjU2LTEuMDNsMC4yLTAuMkwxNS41OSwxNEgxN3YtMS40MWwtNi01Ljk3VjZoOFYxN3oiLz4KPC9zdmc+',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
			'show_in_rest'        => true,
			'rewrite'             => array(
				'slug'       => 'docs',
				'with_front' => false,
				'pages'      => true,
				'feeds'      => true,
			),
		);
		register_post_type( static::POST_TYPE, $args );

		if ( get_option( 'quickdocs_flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
			delete_option( 'quickdocs_flush_rewrite_rules', false );
		}
	}

	/**
	 * Creates the taxonomy.
	 *
	 * Runs on the 'init' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function register_taxonomy() {
		register_taxonomy(
			static::TOPICS_TAXONOMY,
			array( static::POST_TYPE ),
			array(
				'labels'       => array(
					'name'              => __( 'Topics', 'quickdocs' ),
					'singular_name'     => __( 'Topic', 'quickdocs' ),
					'search_items'      => __( 'Search Topics', 'quickdocs' ),
					'all_items'         => __( 'All Topics', 'quickdocs' ),
					'parent_item'       => __( 'Parent Topic', 'quickdocs' ),
					'parent_item_colon' => __( 'Parent Topic:', 'quickdocs' ),
					'edit_item'         => __( 'Edit Topic', 'quickdocs' ),
					'update_item'       => __( 'Update Topic', 'quickdocs' ),
					'add_new_item'      => __( 'Add New Topic', 'quickdocs' ),
					'new_item_name'     => __( 'New Topic Name', 'quickdocs' ),
					'menu_name'         => __( 'Topic', 'quickdocs' ),
				),
				'hierarchical' => true,
				'public'       => true,
				'show_in_rest' => true,
				'rewrite'      => array(
					'slug'         => 'topics',
					'with_front'   => false,
					'hierarchical' => true,
				),
			)
		);
	}

	/**
	 * Redirects templates for documentation articles & their archives.
	 *
	 * Runs on the 'template_include' hook.
	 *
	 * @param string $template Current template path.
	 *
	 * @return string The path of the template.
	 *
	 * @since 1.0.0
	 */
	public function template_include( string $template ): string {
		if ( is_singular_documentation() ) {
			return Quickdocs_Plugin::PLUGIN_DIR . '/templates/single.php';
		} elseif ( is_post_type_archive( self::POST_TYPE ) ) {
			return Quickdocs_Plugin::PLUGIN_DIR . '/templates/archive.php';
		} elseif ( is_tax( self::TOPICS_TAXONOMY ) ) {
			return Quickdocs_Plugin::PLUGIN_DIR . '/templates/archive.php';
		}

		return $template;
	}

	/**
	 * Shortens the length of excerpts for the documentation article post type.
	 *
	 * Runs on the 'excerpt_length' hook.
	 *
	 * @param int $length Current length of exceprt to use.
	 *
	 * @return int The new length of the excerpt.
	 *
	 * @since 1.0.0
	 */
	public function excerpt_length( int $length ): int {
		if ( get_post_type() === static::POST_TYPE ) {
			return 20;
		}

		return $length;
	}

	/**
	 * Changes the read more teaser for the documentation article post type.
	 *
	 * Runs on the 'excerpt_more' hook.
	 *
	 * @param string $more Current teaser of exceprt to use.
	 *
	 * @return int The new teaser of the excerpt.
	 *
	 * @since 1.0.0
	 */
	public function excerpt_more( string $more ): string {
		if ( get_post_type() === static::POST_TYPE ) {
			return '...';
		}

		return $more;
	}

	/**
	 * Changes the archive title for the documentation article post type.
	 *
	 * Runs on the 'get_the_archive_title' hook.
	 *
	 * @param string $title Archive title to be displayed.
	 *
	 * @param string $original_title Archive title without the prefix.
	 *
	 * @return string The new archive title.
	 *
	 * @since 1.0.0
	 */
	public function get_the_archive_title( string $title, string $original_title ): string {
		if ( is_post_type_archive( self::POST_TYPE ) ) {
			return wp_kses( get_option( 'qds_home_title' ), 'post' );
		} elseif ( is_tax( self::TOPICS_TAXONOMY ) ) {
			return $original_title;
		}

		return $title;
	}

	/**
	 * Changes the archive desc for the documentation article post type.
	 *
	 * Runs on the 'get_the_archive_description' hook.
	 *
	 * @param string $desc Archive desccription to be displayed.
	 *
	 * @return string The new archive desccription.
	 *
	 * @since 1.0.0
	 */
	public function get_the_archive_description( string $desc ) {
		if ( is_post_type_archive( self::POST_TYPE ) ) {
			return wpautop( wp_kses( get_option( 'qds_home_content' ), 'post' ) );
		}

		return $desc;
	}

	/**
	 * Gets the Google Fonts URL of the font family chosen in the settings.
	 *
	 * @return string The CSS url of the font family.
	 *
	 * @since 1.0.0
	 */
	private function get_font_url(): string {
		$urls = array(
			'font-arial'       => '',
			'font-helvetica'   => '',
			'font-verdana'     => '',
			'font-poppins'     => 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap',
			'font-roboto'      => 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap',
			'font-open-sans'   => 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500&display=swap',
			'font-montserrat'  => 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&display=swap',
			'font-nunito'      => 'https://fonts.googleapis.com/css2?family=Nunito:wght@400;500&display=swap',
			'font-work-sans'   => 'https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500&display=swap',
			'font-roboto-slab' => 'https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500&display=swap',
			'font-lora'        => 'https://fonts.googleapis.com/css2?family=Lora:wght@400;500&display=swap',
		);

		$key = get_option( 'qds_font_family', 'font-poppins' );

		if ( ! array_key_exists( $key, $urls ) ) {
			$key = 'font-poppins';
		}

		return $urls[ $key ];
	}

	/**
	 * Enqueues all scripts and styles needed for the documentation article templates.
	 *
	 * Runs on the 'wp_enqueue_scripts' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function enqueue(): void {
		if ( get_post_type() !== self::POST_TYPE ) {
			return;
		}

		$ver = Quickdocs_Plugin::VERSION;

		wp_enqueue_style( 'quickdocs-font', $this->get_font_url(), array(), $ver );
		wp_enqueue_style( 'quickdocs-styles', Quickdocs_Plugin::PLUGIN_URL . '/assets/styles/styles.css', array(), $ver );
		wp_enqueue_style( 'quickdocs-highlight', Quickdocs_Plugin::PLUGIN_URL . '/assets/styles/highlight.css', array(), $ver );

		wp_enqueue_script( 'quickdocs-highlight', Quickdocs_Plugin::PLUGIN_URL . '/assets/scripts/highlight.min.js', array(), $ver, false );
		wp_enqueue_script( 'quickdocs-scrollspy', Quickdocs_Plugin::PLUGIN_URL . '/assets/scripts/scrollspy.js', array(), $ver, true );
		wp_enqueue_script( 'quickdocs-helpful', Quickdocs_Plugin::PLUGIN_URL . '/assets/scripts/helpful.js', array(), $ver, true );
		wp_enqueue_script( 'quickdocs-sidebar', Quickdocs_Plugin::PLUGIN_URL . '/assets/scripts/sidebar.js', array(), $ver, true );

		wp_localize_script(
			'quickdocs-helpful',
			'quickdocs',
			array(
				'rest_url'   => esc_url_raw( rest_url() ),
				'rest_nonce' => wp_create_nonce( 'wp_rest' ),
				'post_id'    => get_the_ID(),
			)
		);
	}

	/**
	 * Adds all the metaboxes for the documentation articles post type.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function register_metaboxes() {
		 new Metabox( __( 'Article State', 'quickdocs' ), Quickdocs_Plugin::PLUGIN_DIR . '/metaboxes/article-state.php', static::POST_TYPE, array( 'quickdocs_state' ) );
	}

	/**
	 * Adds a notice to the legacy editor with information on how many users marked a page as helpful/unhelpful.
	 *
	 * Runs on the 'admin_notices' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function helpfulness_notice() {
		if ( ! is_post_documentation() || is_archive() ) {
			return;
		}

		// translators: Both placeholders are numbers. First of how many people found the article helpful, second unhelpful.
		$msg = __( '%1$d people found this article helpful. %2$d people found it unhelpful.', 'quickdocs' );
		$msg = sprintf( $msg, get_helpful_count(), get_unhelpful_count() );
		?>
		<div class="notice notice-info">
			<p><?php echo esc_html( $msg ); ?></p>
		</div>
		<?php
	}

	/**
	 * Adds a message to the block editor with information on how many users marked a page as helpful/unhelpful.
	 *
	 * Runs on the 'enqueue_block_editor_assets' hook.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function helpfulness_notice_gutenberg() {
		if ( ! is_post_documentation() ) {
			return;
		}

		$ver = Quickdocs_Plugin::VERSION;
		wp_enqueue_script( 'quickdocs-admin-notice', Quickdocs_Plugin::PLUGIN_URL . '/assets/scripts/block-editor-admin-notice.js', array(), $ver, true );

		// translators: Both placeholders are numbers. First of how many people found the article helpful, second unhelpful.
		$msg = __( '%1$d people found this article helpful. %2$d people found it unhelpful.', 'quickdocs' );
		$msg = sprintf( $msg, get_helpful_count(), get_unhelpful_count() );

		wp_localize_script(
			'quickdocs-admin-notice',
			'quickdocs',
			array(
				'admin_notice' => sprintf( $msg, get_helpful_count(), get_unhelpful_count() ),
			)
		);
	}
}
