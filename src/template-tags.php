<?php

/**
 * Contains a range of template tags used to make templates easier.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

/**
 * Checks if a post is a documentation article.
 *
 * @param WP_Post|int|null $post Optional. The post to check. Defaults to global post.
 *
 * @return bool True if the post is a documentation article, otherwise false.
 *
 * @since 1.0.0
 */
function is_post_documentation( $post = null ): bool {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}

	return Quickdocs_Posttype::POST_TYPE === $post->post_type;
}

/**
 * Checks if the query is for a documentation article.
 *
 * @return bool True if the query is for a documentation article, otherwise false.
 *
 * @since 1.0.0
 */
function is_singular_documentation(): bool {
	return is_singular( Quickdocs_Posttype::POST_TYPE );
}

/**
 * Checks if the query is for a documentation archive.
 *
 * @return bool True if the query is for a documentation archive, otherwise false.
 *
 * @since 1.0.0
 */
function is_archive_documentation(): bool {
	return is_post_type_archive( Quickdocs_Posttype::POST_TYPE );
}

/**
 * Gets the number of people who found an article unhelpful.
 *
 * @param WP_Post|int|null $post Optional. The post to check. Defaults to global post.
 *
 * @return int The unhelpful count.
 *
 * @since 1.0.0
 */
function get_unhelpful_count( $post = null ): int {
	$post = get_post( $post );
	if ( ! is_post_documentation( $post ) ) {
		return 0;
	}

	return (int) get_post_meta( $post->ID, Quickdocs_Posttype::UNHELPFUL_META_KEY, true );
}

/**
 * Adds one to the unhelpful count
 *
 * @param WP_Post|int|null $post Optional. The post to check. Defaults to global post.
 *
 * @return int The new unhelpful count.
 *
 * @since 1.0.0
 */
function add_unhelpful( $post = null ): int {
	$post = get_post( $post );
	if ( ! is_post_documentation( $post ) ) {
		return 0;
	}

	$unhelpful = get_unhelpful_count( $post );
	$unhelpful++;
	update_post_meta( $post->ID, Quickdocs_Posttype::UNHELPFUL_META_KEY, $unhelpful );
	return $unhelpful;
}

/**
 * Gets the number of people who found an article helpful.
 *
 * @param WP_Post|int|null $post Optional. The post to check. Defaults to global post.
 *
 * @return int The helpful count.
 *
 * @since 1.0.0
 */
function get_helpful_count( $post = null ): int {
	$post = get_post( $post );
	if ( ! is_post_documentation( $post ) ) {
		return 0;
	}

	return (int) get_post_meta( $post->ID, Quickdocs_Posttype::HELPFUL_META_KEY, true );
}

/**
 * Adds one to the helpful count
 *
 * @param WP_Post|int|null $post Optional. The post to check. Defaults to global post.
 *
 * @return int The new helpful count.
 *
 * @since 1.0.0
 */
function add_helpful( $post = null ): int {
	$post = get_post( $post );
	if ( ! is_post_documentation( $post ) ) {
		return 0;
	}

	$helpful = get_helpful_count( $post );
	$helpful++;
	update_post_meta( $post->ID, Quickdocs_Posttype::HELPFUL_META_KEY, $helpful );
	return $helpful;
}

/**
 * @since 1.0.0
 */
function object_has_sidebar_menu( $object_type, $object_id ) {
	$meta = get_metadata( $object_type, $object_id, Quickdocs_Menus::MENU_KEY, true );

	if ( $meta && is_nav_menu( $meta ) ) {
		return wp_get_nav_menu_object( $meta );
	}

	return false;
}

/**
 * @return WP_Term|false Menu item term.
 */
function get_sidebar_menu( $post = null ) {
	$post = get_post( $post );

	// Check post.
	$post_menu = object_has_sidebar_menu( 'post', $post->ID );
	if ( $post_menu ) {
		return $post_menu;
	}

	$topics = wp_get_post_terms(
		$post->ID,
		Quickdocs_Posttype::TOPICS_TAXONOMY,
		array(
			'hide_empty' => false,
		)
	);

	// Check top level topic.
	foreach ( $topics as $topic ) {
		// Check if the topic has a menu, otherwise check if it inherits one.
		$topic_id = $topic->term_id;

		$topic_menu = object_has_sidebar_menu( 'term', $topic_id );
		if ( $topic_menu ) {
			return $topic_menu;
		}

		// Check ancestor topics for inheritance.
		$ancestor_topics = get_ancestors( $topic_id, Quickdocs_Posttype::TOPICS_TAXONOMY, 'taxonomy' );

		foreach ( $ancestor_topics as $ancestor_topic_id ) {
			$inherited_topic_menu = object_has_sidebar_menu( 'term', $ancestor_topic_id );
			if ( $inherited_topic_menu ) {
				return $inherited_topic_menu;
			}
		}
	}

	// Check global menu location.
	$locations = get_nav_menu_locations();
	if ( array_key_exists( Quickdocs_Menus::MENU_KEY, $locations ) ) {
		return wp_get_nav_menu_object( $locations[ Quickdocs_Menus::MENU_KEY ] );
	}

	// Give up.
	return false;
}

function get_sidebar_menu_parent( $post = null ) {
	$menu = get_sidebar_menu( $post );
	if ( ! $menu ) {
		return null;
	}

	$parent_id = get_term_meta( $menu->term_id, Quickdocs_Menus::MENU_PARENT_KEY, true );
	if ( ! $parent_id || $parent_id <= 0 ) {
		return null;
	}

	return get_post( $parent_id );
}

/**
 * @return WP_Post[] Array of menu items
 */
function get_sidebar_menu_items( $post = null ): array {
	$menu = get_sidebar_menu( $post );

	if ( $menu ) {
		return wp_get_nav_menu_items( $menu );
	}

	return array();
}

function get_adjacent_menu_item( $post = null, $step = 1 ) {
	$post = get_post( $post );
	$menu = wp_get_nav_menu_items( get_sidebar_menu( $post ) );

	foreach ( $menu as $i => $item ) {
		if ( (int) $item->object_id === (int) $post->ID ) {
			if ( ! array_key_exists( $i + $step, $menu ) ) {
				return null;
			}

			$key  = $i + $step;
			$item = $menu[ $key ];

			// Traverse through the items until we find a proper link (not an empty custom link header) or hit the end.
			while ( true ) {
				if ( $item->url && '#' !== $item->url ) {
					return $item;
				}

				if ( ! array_key_exists( $key + $step, $menu ) ) {
					// There is no next item.
					return null;
				}

				$key += $step;
				$item = $menu[ $key ];
			}
		}
	}

	return null;
}

function get_previous_menu_item( $post = null ) {
	return get_adjacent_menu_item( $post, -1 );
}

function get_next_menu_item( $post = null ) {
	return get_adjacent_menu_item( $post, 1 );
}

function nav_menu_item_link( $menu_item, $class = '' ) {
	if ( ! $menu_item ) {
		return null;
	}

	$atts           = array();
	$atts['class']  = $class;
	$atts['title']  = ! empty( $menu_item->attr_title ) ? $menu_item->attr_title : '';
	$atts['target'] = ! empty( $menu_item->target ) ? $menu_item->target : '';

	if ( '_blank' === $menu_item->target && empty( $menu_item->xfn ) ) {
		$atts['rel'] = 'noopener';
	} else {
		$atts['rel'] = $menu_item->xfn;
	}

	$atts['href']         = ! empty( $menu_item->url ) ? $menu_item->url : '';
	$atts['aria-current'] = $menu_item->current ? 'page' : '';

	$atts = apply_filters( 'nav_menu_link_attributes', $atts, $menu_item, array(), 0 );

	$attributes = '';
	foreach ( $atts as $attr => $value ) {
		if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
			$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
			$attributes .= ' ' . $attr . '="' . $value . '"';
		}
	}

	$title = apply_filters( 'the_title', $menu_item->title, $menu_item->ID );
	$title = apply_filters( 'nav_menu_item_title', $title, $menu_item, array(), 0 );

	return '<a' . $attributes . '>' . $title . '</a>';
}
function get_previous_menu_item_link( $post = null, $class = '' ) {
	$menu_item = get_previous_menu_item( $post );
	return nav_menu_item_link( $menu_item, $class );
}

function get_next_menu_item_link( $post = null, $class = '' ) {
	$menu_item = get_next_menu_item( $post );
	return nav_menu_item_link( $menu_item, $class );
}

function get_specific_topic( $post = null ) {
	$post = get_post();
	if ( ! $post ) {
		return null;
	}

	// https://stackoverflow.com/questions/27789560/determine-lowest-level-taxonomy-term

	$terms = wp_get_post_terms(
		$post->ID,
		Quickdocs_Posttype::TOPICS_TAXONOMY,
		array(
			'orderby' => 'id',
			'order'   => 'DESC',
		)
	);

	$deepest_term = false;
	$max_depth    = -1;

	foreach ( $terms as $term ) {
		$ancestors  = get_ancestors( $term->term_id, Quickdocs_Posttype::TOPICS_TAXONOMY );
		$term_depth = count( $ancestors );

		if ( $term_depth > $max_depth ) {
			$deepest_term = $term;
			$max_depth    = $term_depth;
		}
	}

	if ( $deepest_term ) {
		$term = get_term( $deepest_term, Quickdocs_Posttype::TOPICS_TAXONOMY );
		return $term->name;
	}

	return null;
}

function the_specific_topic( $post = null ) {
	echo esc_html( get_specific_topic( $post ) );
}

function get_generic_topic( $post = null ) {
	$post = get_post();
	if ( ! $post ) {
		return null;
	}

	$terms = wp_get_post_terms(
		$post->ID,
		Quickdocs_Posttype::TOPICS_TAXONOMY,
		array(
			'orderby' => 'id',
			'order'   => 'DESC',
		)
	);

	foreach ( $terms as $term ) {
		$ancestors = get_ancestors( $term->term_id, Quickdocs_Posttype::TOPICS_TAXONOMY );
		$term      = get_term( end( $ancestors ), Quickdocs_Posttype::TOPICS_TAXONOMY );
		
		if(!is_wp_error( $term ) && $term){
			return $term->name;
		}
	}

	return null;
}

function the_generic_topic( $post = null ) {
	echo esc_html( get_generic_topic( $post ) );
}

function get_article_states( $post = null ): array {
	$post = get_post( $post );
	if ( ! is_post_documentation( $post ) ) {
		return array();
	}

	$state = get_post_meta( $post->ID, 'quickdocs_state', true );

	if ( ! is_array( $state ) ) {
		$state = array();
	}

	return $state;
}

function get_article_state_icon( string $state ): string {
	$icons = array(
		// Material Icons: Inventory.
		'archived'     => '<span class="article_state_icon article_state_icon--archived" aria-label="' . __( 'Archived', ' quickdocs' ) . '" title="' . __( 'Archived', ' quickdocs' ) . '"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#7E8E97"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M20 2H4c-1 0-2 .9-2 2v3.01c0 .72.43 1.34 1 1.69V20c0 1.1 1.1 2 2 2h14c.9 0 2-.9 2-2V8.7c.57-.35 1-.97 1-1.69V4c0-1.1-1-2-2-2zm-5 12H9v-2h6v2zm5-7H4V4l16-.02V7z"/></svg></span>',

		// Material Icons: Warning.
		'incomplete'   => '<span class="article_state_icon article_state_icon--incomplete" aria-label="' . __( 'Incomplete', ' quickdocs' ) . '" title="' . __( 'Incomplete', ' quickdocs' ) . '"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#F8841E"><path d="M0 0h24v24H0z" fill="none"/><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg></span>',

		// Material Icons: Delete.
		'deprecated'   => '<span class="article_state_icon article_state_icon--deprecated" aria-label="' . __( 'Deprecated', ' quickdocs' ) . '" title="' . __( 'Deprecated', ' quickdocs' ) . '"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#F81E1E"><path d="M0 0h24v24H0z" fill="none"/><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></span>',

		// Material Icons: Science.
		'experimental' => '<span class="article_state_icon article_state_icon--experimental" aria-label="' . __( 'Experimental', ' quickdocs' ) . '" title="' . __( 'Experimental', ' quickdocs' ) . '"><svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#85A700"><g><rect fill="none" height="24" width="24"/></g><g><path d="M19.8,18.4L14,10.67V6.5l1.35-1.69C15.61,4.48,15.38,4,14.96,4H9.04C8.62,4,8.39,4.48,8.65,4.81L10,6.5v4.17L4.2,18.4 C3.71,19.06,4.18,20,5,20h14C19.82,20,20.29,19.06,19.8,18.4z"/></g></svg></span>',

		// Material Icons: Star.
		'premium'      => '<span class="article_state_icon article_state_icon--premium" aria-label="' . __( 'Premium', ' quickdocs' ) . '" title="' . __( 'Premium', ' quickdocs' ) . '"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="var(--clr-active)"><path d="M0 0h24v24H0z" fill="none"/><path d="M0 0h24v24H0z" fill="none"/><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg></span>',
	);

	if ( 'normal' === $state ) {
		return '';
	}
	if ( ! array_key_exists( $state, $icons ) ) {
		return '';
	}

	return $icons[ $state ];
}

function the_article_state_message( string $state ) {
	$messages = array(
		'archived'     => __( 'This article has been archived and may no longer be up to date with the most current information.', 'quickdocs' ),
		'incomplete'   => __( 'This article is currently incomplete. We\'re working on improving it and thank you for your patience.', 'quickdocs' ),
		'deprecated'   => __( 'This feature is deprecated and is not recommended for usage. Although it may still work, available may cease at any time.', 'quickdocs' ),
		'experimental' => __( 'This feature is experimental and is not recommended for standard usage. It may change or be removed at any time.', 'quickdocs' ),
		'premium'      => __( 'This feature is only available to members of our premium package.', 'quickdocs' ),
	);

	if ( 'normal' === $state ) {
		return '';
	}
	if ( ! array_key_exists( $state, $messages, true ) ) {
		return '';
	}

	?>

	<div role="status" class="article_state_msg article_state_msg--<?php echo esc_attr( $state ); ?>">
		<?php echo wp_kses( get_article_state_icon( $state ), 'post' ); ?>
		<?php echo esc_html( $messages[ $state ] ); ?>
	</div>

	<?php
}

function the_admin_notice( string $type = 'info', string $message = '' ) {
	echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible">';
	echo '<p>' . esc_html( $message ) . '</p>';
	echo '</div>';
}

/**
 * Adapted from https://developer.wordpress.org/reference/functions/wp_dropdown_pages/
 */
function dropdown_documentation( array $args = array() ) {
	$defaults = array(
		'post_type'             => Quickdocs_Posttype::POST_TYPE,
		'number_posts'          => -1,
		'selected'              => 0,
		'echo'                  => 1,
		'dropdown_name'         => 'page_id',
		'id'                    => '',
		'class'                 => '',
		'show_option_none'      => '',
		'show_option_no_change' => '',
		'option_none_value'     => '',
		'value_field'           => 'ID',
	);

	$parsed_args = wp_parse_args( $args, $defaults );

	$pages  = get_posts( $parsed_args );
	$output = '';
	// Back-compat with old system where both id and name were based on $name argument.
	if ( empty( $parsed_args['id'] ) ) {
		$parsed_args['id'] = $parsed_args['dropdown_name'];
	}

	if ( ! empty( $pages ) ) {
		$class = '';
		if ( ! empty( $parsed_args['class'] ) ) {
			$class = " class='" . esc_attr( $parsed_args['class'] ) . "'";
		}

		$output = "<select name='" . esc_attr( $parsed_args['dropdown_name'] ) . "'" . $class . " id='" . esc_attr( $parsed_args['id'] ) . "'>\n";
		if ( $parsed_args['show_option_no_change'] ) {
			$output .= "\t<option value=\"-1\">" . $parsed_args['show_option_no_change'] . "</option>\n";
		}
		if ( $parsed_args['show_option_none'] ) {
			$output .= "\t<option value=\"" . esc_attr( $parsed_args['option_none_value'] ) . '">' . $parsed_args['show_option_none'] . "</option>\n";
		}

		foreach ( $pages as $page ) {
			// Restores the more descriptive, specific name for use within this method.
			if ( ! isset( $parsed_args['value_field'] ) || ! isset( $page->{$parsed_args['value_field']} ) ) {
				$parsed_args['value_field'] = 'ID';
			}

			$output .= "\t<option value=\"" . esc_attr( $page->{$parsed_args['value_field']} ) . '"';
			if ( $page->ID === $parsed_args['selected'] ) {
				$output .= ' selected="selected"';
			}
			$output .= '>';

			$title = $page->post_title;
			if ( '' === $title ) {
				/* translators: %d: ID of a post. */
				$title = sprintf( __( '#%d (no title)' ), $page->ID );
			}

			/**
			 * Filters the page title when creating an HTML drop-down list of pages.
			 *
			 * @since 3.1.0
			 *
			 * @param string  $title Page title.
			 * @param WP_Post $page  Page data object.
			 */
			$title = apply_filters( 'list_pages', $title, $page );

			$output .= esc_html( $title );
			$output .= "</option>\n";
		}
		$output .= "</select>\n";
	}

	/**
	 * Filters the HTML output of a list of pages as a dropdown.
	 *
	 * @since 2.1.0
	 * @since 4.4.0 `$parsed_args` and `$pages` added as arguments.
	 *
	 * @param string    $output      HTML output for dropdown list of pages.
	 * @param array     $parsed_args The parsed arguments array. See wp_dropdown_pages()
	 *                               for information on accepted arguments.
	 * @param WP_Post[] $pages       Array of the page objects.
	 */
	$html = apply_filters( 'wp_dropdown_pages', $output, $parsed_args, $pages );

	if ( $parsed_args['echo'] ) {
		echo wp_kses( $html, 'post' );
	}

	return $html;
}
