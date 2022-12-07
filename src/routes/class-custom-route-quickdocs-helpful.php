<?php

/**
 * Contains the Custom_Rest_Field_Quickdocs_Search class.
 *
 * @since 1.0.0
 *
 * @package Quickdocs
 */

namespace quickdocs;

use WP_REST_Request;

/**
 * Adds rest API endpoint to access how many people thought a documentation article was helpful and add to it.
 *
 * @since 1.0.0
 */
class Custom_Route_Quickdocs_Helpful {


	/**
	 * Registers all rest API routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$version   = 1;
		$namespace = "quickdocs/v$version";

		register_rest_route(
			$namespace,
			'/helpful/(?P<id>[0-9\/]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_helpful' ),
					'permission_callback' => '__return_true',
				),

				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'add_helpful' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		register_rest_route(
			$namespace,
			'/unhelpful/(?P<id>[0-9\/]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_unhelpful' ),
					'permission_callback' => '__return_true',
				),

				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'add_unhelpful' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Returns the number of people who found a post helpful.
	 *
	 * @param WP_REST_Request $request The rest request being made.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public function get_helpful( WP_REST_Request $request ): int {
		return get_helpful_count( $request['id'] );
	}

	/**
	 * Returns the number of people who found a post unhelpful.
	 *
	 * @param WP_REST_Request $request The rest request being made.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public function get_unhelpful( WP_REST_Request $request ): int {
		return get_unhelpful_count( $request['id'] );
	}

	/**
	 * Adds one to and returns the number of people who found a post helpful.
	 *
	 * @param WP_REST_Request $request The rest request being made.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public function add_helpful( WP_REST_Request $request ): int {
		return add_helpful( $request['id'] );
	}

	/**
	 * Adds one to and returns the number of people who found a post unhelpful.
	 *
	 * @param WP_REST_Request $request The rest request being made.
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public function add_unhelpful( WP_REST_Request $request ): int {
		return add_unhelpful( $request['id'] );
	}
}
