<?php

/**
 * Contains the Table_Of_Contents class.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

use \DOMDocument;
use \DOMXPath;

/**
 * Scans HTML to create a table of contents from it's headings.
 *
 * @since 1.0.0
 */
class Table_Of_Contents {


	/**
	 * The HTML document to scan.
	 *
	 * @var DOMDocument $document
	 *
	 * @since 1.0.0
	 */
	private DOMDocument $document;

	/**
	 * The HTML content to scan.
	 *
	 * @var string $content
	 *
	 * @since 1.0.0
	 */
	private string $content;

	/**
	 * The deepest heading to include in the table of contents.
	 *
	 * @var int $heading_depth
	 *
	 * @since 1.0.0
	 */
	private int $heading_depth = 3;

	/**
	 * The headings found in the document.
	 *
	 * @var array $headings
	 *
	 * @since 1.0.0
	 */
	public array $headings = array();

	/**
	 * Creates a table of contents from a string of content.
	 *
	 * @param string $content The HTML content to scan.
	 *
	 * @param int    $heading_depth The deepest heading level to include. Defaults to 3.
	 *
	 * @since 1.0.0
	 */
	public function __construct( string $content, int $heading_depth = 3 ) {
		$this->set_content( $content );
		$this->set_heading_depth( $heading_depth );
	}

	/**
	 * Adds IDs to all headings.
	 *
	 * @param string $content The HTML content to scan.
	 *
	 * @return string $content The parsed content.
	 *
	 * @since 1.0.0
	 */
	public static function add_ids_to_headings( $content ) {
		$content = preg_replace_callback(
			'/(\<h[1-6](.*?))\>(.*)(<\/h[1-6]>)/i',
			function ( $matches ) {
				if ( ! stripos( $matches[0], 'id=' ) ) :
					$matches[0] = $matches[1] . $matches[2] . ' id="' . sanitize_title( $matches[3] ) . '">' . $matches[3] . $matches[4];
				endif;
				return $matches[0];
			},
			$content
		);

		return $content;
	}

	/**
	 * Creates a table of contents from a WordPress post.
	 *
	 * @param int|WP_Post|null $post  Optional. Post ID or post object. Defaults to global post.
	 *
	 * @param int              $heading_depth The deepest heading level to include. Defaults to 3.
	 *
	 * @return Table_Of_Contents The table of contents for this post.
	 *
	 * @since 1.0.0
	 */
	public static function from_post( $post = null, int $heading_depth = 3 ): Table_Of_Contents {
		$post    = get_post( $post );
		$content = get_the_content( $post );
		$content = apply_filters( 'the_content', $content );

		return new Table_Of_Contents( "<html><body>$content</body></html>", $heading_depth );
	}

	/**
	 * Gets all the heading nodes.
	 *
	 * @return DOMNodeList|false
	 *
	 * @since 1.0.0
	 */
	private function get_headings_node_list() {
		 $query = '';

		for ( $i = 1; $i <= $this->heading_depth; $i++ ) {
			if ( 1 === $i ) {
				$query .= "//h$i";
			} else {
				$query .= " | //h$i";
			}
		}

		$xpath = new DOMXPath( $this->document );
		return $xpath->query( $query );
	}

	/**
	 * Sets the content.
	 *
	 * @param string $content HTML string to scan.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function set_content( string $content ): void {
		libxml_use_internal_errors( true );

		$this->content  = $content;
		$this->document = new DOMDocument();
		$this->document->loadHTML( $this->content );
	}

	/**
	 * Sets the heading depth.
	 *
	 * @param int $heading_depth Depth of heading to search to.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function set_heading_depth( int $heading_depth ): void {
		$this->heading_depth = $heading_depth;
	}

	/**
	 * Gets an array of all headings.
	 *
	 * Return array items include the following:
	 *  heading -- The tag name of the heading
	 *  depth -- The depth of the heading tag.
	 *  id -- The ID attribute of the heading.
	 *  text -- The text content of the heading.
	 *
	 * @return array $headings Array of headings in the content.
	 */
	public function get_headings(): array {
		$nodes    = $this->get_headings_node_list();
		$headings = array();

		foreach ( $nodes as $node ) {
			$id = $node->getAttribute( 'id' );

			$headings[] = (object) array(
				'heading' => $node->tagName, // phpcs:ignore -- We have no control over the naming of this property.
				'depth'   => intval($node->tagName[1]) - 1, // phpcs:ignore -- We have no control over the naming of this property.
				'id'      => $id,
				'text'    => $node->textContent, // phpcs:ignore -- We have no control over the naming of this property.
			);
		}

		libxml_clear_errors();
		return $headings;
	}

	/**
	 * Returns an ordered, nested list of the headings with anchor links to each.
	 *
	 * @return string The ordered, nested list of headings.
	 */
	public function get_headings_list(): string {
		if ( ! $this->get_headings() ) {
			return '';
		}

		$html       = '<nav class="toc">';
		$prev_depth = 0;

		foreach ( $this->get_headings() as $heading ) {
			if ( $heading->depth > $prev_depth ) {
				$html .= "<ol class='toc-level toc-level-depth-$heading->depth'>";
			} else {
				$html .= str_repeat( '</li></ol>', $prev_depth - $heading->depth );
				$html .= '</li>';
			}

			$html .= "<li class='toc-item toc-item-depth-$heading->depth'><a class='toc-link' href='#$heading->id'>$heading->text</a>";

			$prev_depth = $heading->depth;
		}

		$html .= str_repeat( '</li></ol>', $prev_depth ) . '</nav>';
		return trim( $html );
	}
}

add_filter( 'the_content', array( __NAMESPACE__ . '\\Table_Of_Contents', 'add_ids_to_headings' ) );
