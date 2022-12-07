<?php

/**
 * Contains the Custom_Rest_Field_Quickdocs_Search class.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

/**
 * Creates an rest API field when searching documentation articles to get the relevent content highlighted.
 *
 * @since 1.0.0
 */
class Custom_Rest_Field_Quickdocs_Search {


	/**
	 * The object type of this endpoint.
	 *
	 * Defaults to 'wrd_docs'.
	 *
	 * @var string $object_type
	 */
	public string $object_type = Quickdocs_Posttype::POST_TYPE;

	/**
	 * The name of the field.
	 *
	 * Defaults to 'search_query_highlight'.
	 *
	 * @var string $field
	 */
	public string $field = 'search_query_highlight';

	/**
	 * Registers the field.
	 *
	 * @return void
	 */
	public function register_fields(): void {
		register_rest_field(
			$this->object_type,
			$this->field,
			array(
				'get_callback' => function ( $post, $field, $request ) {
					$content = $post['content']['rendered'];

					if ( ! $request->get_param( 'search' ) ) {
						return $this->shorten_content( $content );
					}

					return $this->highlight_and_shorten_content( $content, $request->get_param( 'search' ) );
				},
			)
		);
	}

	/**
	 * Highlights the searched query in a section of text trims it to length around the first occurance.
	 *
	 * Mark tag will have the class 'search-query-highlight'
	 *
	 * @param string $content The text to search. All tags will be stripped.
	 *
	 * @param string $highlight_substr The keyword to highlight.
	 *
	 * @param int    $length Optional. The maximum length of the returned string. Will be centered around the first highlighted section.
	 *
	 * @param string $teaser Optional. String to append when cutting off the end of the content. Defaults to '...'.
	 *
	 * @return string The highlighted, stripped & shortened content.
	 *
	 * @see highlight_content();
	 *
	 * @see shorten_content();
	 *
	 * @since 1.0.0
	 */
	private function highlight_and_shorten_content( string $content, string $highlight_substr, int $length = 120, string $teaser = '...' ): string {
		$content         = wp_strip_all_tags( $content );
		$first_occurance = stripos( $content, $highlight_substr );
		$first_occurance = max( $first_occurance - 20, 0 ); // Go back by 20 chars to see what's written before the keyword.

		$shortened = $this->shorten_content( $content, $first_occurance, $length, $teaser );

		return $this->highlight_content( $shortened, $highlight_substr );
	}

	/**
	 * Highlights a section of text and wraps the searched for text in a mark tag.
	 *
	 * Mark tag will have the class 'search-query-highlight'
	 *
	 * @param string $content The text to search. All tags will be stripped.
	 *
	 * @param string $highlight_substr The keyword to highlight.
	 *
	 * @return string The highlighted & stripped content.
	 *
	 * @since 1.0.0
	 */
	private function highlight_content( string $content, string $highlight_substr ): string {
		$content          = wp_strip_all_tags( $content );
		$highlight_substr = trim( wp_strip_all_tags( $highlight_substr ) );

		$highlighted = preg_replace( '/\p{L}*?' . preg_quote( $highlight_substr ) . '\p{L}*/ui', "<mark class='search-query-highlight'>$0</mark>", $content );
		return $highlighted;
	}

	/**
	 * Highlights the searched query in a section of text trims it to length around the first occurance.
	 *
	 * Mark tag will have the class 'search-query-highlight'
	 *
	 * @param string $content The text to search. All tags will be stripped.
	 *
	 * @param int    $offset Optional. The distance from the start to begin trimming. Defaults to 0.
	 *
	 * @param int    $length Optional. The maximum length of the returned string. Will be centered around the first highlighted section.
	 *
	 * @param string $teaser Optional. String to append when cutting off the end of the content. Defaults to '...'.
	 *
	 * @return string The stripped & shortened content.
	 *
	 * @since 1.0.0
	 */
	private function shorten_content( string $content, int $offset = 0, int $length = 120, string $teaser = '...' ): string {
		$content           = wp_strip_all_tags( $content );
		$shortened         = substr( $content, $offset, $length );
		$trimmed           = trim( $shortened );
		$unpunctuated_ends = trim( $trimmed, '\'.,' );

		return $unpunctuated_ends . $teaser;
	}
}
