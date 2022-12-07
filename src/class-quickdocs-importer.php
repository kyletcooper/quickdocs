<?php

/**
 * Contains the Quickdocs_Importer class.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

use DOMDocument;
use DOMElement;

/**
 * Custom importer for documentation articles. Supports .html, .md and .txt files.
 *
 * @since 1.0.0
 */
class Quickdocs_Importer {




	/**
	 * Creates the Quickdocs_Importer
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		 $GLOBALS['quickdocs_importer'] = $this;
		add_action( 'admin_init', array( $this, 'register' ) );
	}

	/**
	 * Registers the import so it can be used in the Tools section of the admin area.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function register(): void {
		register_importer( 'quickdocs', __( 'Documentation Importer', 'quickdocs' ), __( 'Import markdown, HTML and text files as new documentation articles.', 'quickdocs' ), array( $this, 'dispatch' ) );
	}

	/**
	 * Determines the step the user is on in the import process and runs the relevent action.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function dispatch() {
		$this->header();

		$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];

		switch ( $step ) {
			case 0:
				$this->greet();
				break;
			case 1:
				if ( ! check_admin_referer( 'import-upload' ) ) {
					$this->footer();
					return;
				}
				if ( ! current_user_can( 'import' ) ) {
					$this->footer();
					return;
				}

				$this->handle_upload();

				break;
		}

		$this->footer();
	}

	/**
	 * Displays the opening of the importer.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function header(): void {
		?>
		<div class="wrap">
			<h2>Documentation Importer</h2>
		<?php
	}

	/**
	 * Displays the opening of the importer.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function footer(): void {
		?>
		</div>
		<?php
	}

	/**
	 * Displays the import files uploader form.
	 *
	 * This is based on to use wp_import_upload_form core function with the following changes:
	 *      - File input: adds multiple & accept and changes name to imports[]
	 *      - Submit button: makes text plural and changes language domain
	 *
	 * @param string $action The name of the action.
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_import_upload_form/
	 *
	 * @since 1.0.0
	 */
	private function wp_import_multiple_upload_form( $action ) {
		/**
		 * Filters the maximum allowed upload size for import files.
		 *
		 * @since 2.3.0
		 *
		 * @see wp_max_upload_size()
		 *
		 * @param int $max_upload_size Allowed upload size. Default 1 MB.
		 */
		$bytes      = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
		$size       = size_format( $bytes );
		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['error'] ) ) :
			?>
			<div class="error">
				<p><?php esc_html_e( 'Before you can upload your import file, you will need to fix the following error:' ); ?></p>
				<p><strong><?php echo esc_html( $upload_dir['error'] ); ?></strong></p>
			</div>
			<?php
		else :
			?>
			<form enctype="multipart/form-data" id="import-upload-form" method="post" class="wp-upload-form" action="<?php echo esc_url( wp_nonce_url( $action, 'import-upload' ) ); ?>">
				<p>
					<label for="upload">
						<?php esc_html_e( 'Choose a file from your computer:', 'quickdocs' ); ?>
					</label>
					<?php

					// translators: Placeholder is the maximum upload size.
					echo esc_html( sprintf( __( '(Maximum size: %s)', 'quickdocs' ), $size ) );

					?>

					<input type="file" multiple id="upload" name="imports[]" size="25" accept=".html, .md, .txt" />
					<input type="hidden" name="action" value="save" />
					<input type="hidden" name="max_file_size" value="<?php echo esc_html( $bytes ); ?>" />
				</p>
				<?php submit_button( esc_html__( 'Upload files and import', 'quickdocs' ), 'primary' ); ?>
			</form>
			<?php
		endif;
	}

	/**
	 * Displays the welcome message and upload form for the importer.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function greet(): void {
		?>
		<div class="narrow">
			<p>
				<?php esc_html_e( 'You can import markdown, plain text, or HTML files to be converted to new documetation articles.', 'quickdocs' ); ?>
			</p>


			<?php $this->wp_import_multiple_upload_form( 'admin.php?import=quickdocs&amp;step=1' ); ?>
		</div>
		<?php
	}

	/**
	 * Imports all the uploaded files and displays the success/failure message.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function handle_upload(): void {
		if (!array_key_exists('imports', $_FILES) || empty($_FILES['imports'])) { // phpcs:ignore -- This is sanitized as we loop through each item.
			the_admin_notice( 'error', __( 'No files uploaded.', 'quickdocs' ) );
			$this->greet();
			return;
		}

		$files       = $this->organise_files_array(wp_unslash($_FILES['imports'])); // phpcs:ignore -- This is sanitized as we loop through each item.
		$files_count = count( $files );
		$errors      = 0;

		foreach ( $files as $file ) {
			if ( ! $this->detect_type_and_import( $file ) ) {
				$errors++;
			}
		}

		echo '<p>' . esc_html__( 'All done.', 'quickdocs' ) . '</p>';
		// translators: First placeholder is the number of errors, second placeholder is total uploaded file count.
		echo '<p>' . esc_html( sprintf( __( '%1$d out of %2$d imports failed.' ), $errors, $files_count ) ) . '</p>';
		echo '<p><a href=' . esc_url( admin_url( 'edit.php?post_type=wrd_docs' ) ) . '>' . esc_html( __( 'View documentation', 'quickdocs' ) ) . '</a></p>';

		if ( 0 === $errors ) {
			if ( $files_count > 1 ) {
				the_admin_notice( 'success', __( 'All imports successful.', 'quickdocs' ) );
			} else {
				the_admin_notice( 'success', __( 'Import successful.', 'quickdocs' ) );
			}
		} else {
			// translators: First placeholder is the number of errors, second placeholder is total uploaded file count.
			$msg = sprintf( __( '%1$d out of %2$d imports failed.' ), esc_html( $errors ), esc_html( $files_count ) );
			the_admin_notice( 'error', $msg );
		}
	}

	/**
	 * Organises an array of files to be used as individual array items.
	 *
	 * @param array $file_post Array of $_FILES to be parsed.
	 *
	 * @return array Organised files array.
	 *
	 * @since 1.0.0
	 */
	private function organise_files_array( array $file_post ): array {
		// From https://www.php.net/manual/en/features.file-upload.multiple.php#53240.
		$file_ary   = array();
		$file_count = count( $file_post['name'] );
		$file_keys  = array_keys( $file_post );

		for ( $i = 0; $i < $file_count; $i++ ) {
			foreach ( $file_keys as $key ) {
				$file_ary[ $i ][ $key ] = $file_post[ $key ][ $i ];
			}
		}

		return $file_ary;
	}

	/**
	 * Detects the files extension and imports it using the relevant function.
	 *
	 * If the file does not have a .md or .html extension then it defaults to the plain text importer.
	 *
	 * @param array $file An organised single file, passed through organise_files_array().
	 *
	 * @return bool True if the post was successfully created, false on error.
	 *
	 * @since 1.0.0
	 */
	private function detect_type_and_import( array $file ): bool {
		$ext = pathinfo( $file['name'], PATHINFO_EXTENSION );

		switch ( $ext ) {
			case 'md':
				return $this->import_markdown( $file );

			case 'html':
				return $this->import_html( $file );

			case 'txt':
			default:
				return $this->import_plaintext( $file );
		}
	}

	/**
	 * Imports a markdown file.
	 *
	 * @param array $file An organised single file, passed through organise_files_array().
	 *
	 * @return bool True if the post was successfully created, false on error.
	 *
	 * @since 1.0.0
	 */
	private function import_markdown( array $file ): bool {
		require Quickdocs_Plugin::PLUGIN_DIR . '/vendor/autoload.php';

		$file_name = $file['name'];
		$content   = $this->get_file_contents( $file );

		// Parse markdown.
		$parsedown = new \Parsedown();
		$content   = $parsedown->text( $content );
		$title     = $this->html_get_h1_text( $content, $this->file_name_to_title( $file_name ) );

		return $this->create_post( $title, $content, $file_name );
	}

	/**
	 * Imports a plain text file.
	 *
	 * The file name is used as the title using file_name_to_title().
	 *
	 * @param array $file -- An organised single file, passed through organise_files_array().
	 *
	 * @return bool True if the post was successfully created, false on error.
	 *
	 * @since 1.0.0
	 */
	private function import_plaintext( array $file ): bool {
		$file_name = $file['name'];
		$content   = $this->get_file_contents( $file );
		$title     = $this->file_name_to_title( $file_name );

		return $this->create_post( $title, $content, $file_name );
	}


	/**
	 * Imports a HTML file.
	 *
	 * This function will try to get only the content of the first <main> element or the <body> element if that does not exist.
	 * The title will be automatically detected by searching for a <h1> element and will fallback to the file name using file_name_to_title().
	 *
	 * @param array $file -- An organised single file, passed through organise_files_array().
	 *
	 * @return bool True if the post was successfully created, false on error.
	 *
	 * @since 1.0.0
	 */
	private function import_html( array $file ): bool {
		$file_name = $file['name'];
		$content   = $this->get_file_contents( $file );
		$title     = $this->html_get_h1_text( $content, $this->file_name_to_title( $file_name ) );

		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$dom->loadHTML( $content );
		$main_node_list = $dom->getElementsByTagName( 'main' );
		$body_node_list = $dom->getElementsByTagName( 'body' );

		// Prefer to get content from the <main> if it exists. Should be more useful.
		if ( $main_node_list->length > 0 ) {
			$content = $this->html_get_inner_html( $main_node_list->item( 0 ) );
		} elseif ( $body_node_list->length > 0 ) {
			$content = $this->html_get_inner_html( $body_node_list->item( 0 ) );
		}
		libxml_clear_errors();

		return $this->create_post( $title, $content, $file_name );
	}

	/**
	 * Searchs a HTML string to get the content of the first <h1> tag.
	 *
	 * @param string $content The HTML to search through.
	 * @param string $fallback_title The text to return if there is no <h1> tag.
	 *
	 * @return string The content of the first <h1> tag, otherwise the $fallback_title.
	 *
	 * @since 1.0.0
	 */
	private function html_get_h1_text( string $content, string $fallback_title ): string {
		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$dom->loadHTML( $content );
		$h1_node_list = $dom->getElementsByTagName( 'h1' );

		if ( $h1_node_list->length > 0 ) {
			return trim( $h1_node_list->item( 0 )->textContent );
		}

		return $fallback_title;
	}

	/**
	 * Gets the inner HTML of a DOMElement.
	 *
	 * @param DOMElement $element The element to get the content from.
	 *
	 * @return string The inner HTML of the element.
	 *
	 * @since 1.0.0
	 */
	private function html_get_inner_html( DOMElement $element ): string {
		$doc  = $element->ownerDocument; // phpcs:ignore -- We don't control this property name.
		$html = '';

		foreach ($element->childNodes as $node) { // phpcs:ignore -- We don't control this property name.
			$html .= $doc->saveHTML( $node );
		}

		return $html;
	}


	/**
	 * Gets the contents of an uploaded file.
	 *
	 * @param array $file Organised file array, should be an index of the $_FILES array passed through organise_files_array().
	 *
	 * @return string The contents of the file.
	 *
	 * @since 1.0.0
	 */
	private function get_file_contents( array $file ): string {
		if ( ! isset( $file['tmp_name'] ) ) {
			return '';
		}

		$contents = file_get_contents($file['tmp_name']); // phpcs:ignore -- This is not a remote file.

		if ( ! $contents ) {
			return '';
		}

		return $contents;
	}

	/**
	 * Attempts to create a nice title from a file name.
	 *
	 * This function removes the extension, replaces hyphens and underscores with spaces and capitalizes the first letter of every word.
	 *
	 * @param string $file_name The name of the file.
	 *
	 * @return string A nice readable title.
	 *
	 * @since 1.0.0
	 */
	private function file_name_to_title( string $file_name ): string {
		$file_wo_extension = pathinfo( $file_name, PATHINFO_FILENAME );
		$file_w_spaces     = str_replace( '_-', ' ', $file_wo_extension );
		return ucwords( $file_w_spaces );
	}

	/**
	 * Checks if a documentation article exists with the title given.
	 *
	 * @param string $title The title to check for. Will be converted to a slug.
	 *
	 * @return bool True if there is a documentation article with the slug.
	 */
	private function post_exists( string $title ): bool {
		$post = get_page_by_path( sanitize_title( $title ), OBJECT, Quickdocs_Posttype::POST_TYPE );
		return (bool) $post;
	}

	/**
	 * Helper function to create a new documentation article.
	 *
	 * @param string $title The title for the article.
	 *
	 * @param string $content The body content of the article.
	 *
	 * @param string $file_name The name of the file being uploaded. Used for status reporting.
	 *
	 * @since 1.0.0
	 */
	private function create_post( string $title, string $content, string $file_name ): bool {
		$content = wp_kses( $content, 'post' );

		if ( $this->post_exists( $title ) ) {
			// translators: Placeholder is the name of the file being imported.
			echo '<p>' . esc_html( sprintf( __( 'Article already exists with the same title as file %s.', 'quickdocs' ), $file_name ) ) . '</p>';
			return false;
		}

		$id = wp_insert_post(
			array(
				'post_title'   => sanitize_text_field( $title ),
				'post_content' => wp_kses( $content, 'post' ),
				'post_name'    => sanitize_title( $title ),
				'post_status'  => 'draft',
				'post_author'  => get_current_user_id(),
				'post_type'    => Quickdocs_Posttype::POST_TYPE,
			)
		);

		if ( $id > 0 ) {
			// translators: Placeholder is the name of the file being imported.
			echo '<p>' . esc_html( sprintf( __( 'Article created for file %s.', 'quickdocs' ), $file_name ) ) . '</p>';
			return true;
		} {
			// translators: Placeholder is the name of the file being imported.
			echo '<p>' . esc_html( sprintf( __( 'Could not create article for file %s.', 'quickdocs' ), $file_name ) ) . '</p>';
			return false;
		}
	}
}
