<?php

namespace Palasthotel\WordPress\MyPlugin;

use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Example endpoints. Every route needs a permission_callback that checks a capability -
 * __return_true only for data that is public anyway.
 */
class REST extends Components\Component {

	const NAMESPACE = "my-plugin/v1";

	public function onCreate() {
		add_action( 'rest_api_init', [ $this, 'init_rest_api' ] );
	}

	public function init_rest_api() {
		register_rest_route(
			static::NAMESPACE,
			'/items/(?P<post_id>\d+)',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'can_edit_post' ],
					'args'                => $this->post_id_arg(),
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'add_item' ],
					'permission_callback' => [ $this, 'can_edit_post' ],
					'args'                => array_merge(
						$this->post_id_arg(),
						[
							"value" => [
								'type'              => 'string',
								'required'          => true,
								'sanitize_callback' => 'sanitize_text_field',
							],
						]
					),
				],
			]
		);
	}

	private function post_id_arg(): array {
		return [
			"post_id" => [
				'type'              => 'integer',
				'required'          => true,
				'sanitize_callback' => 'absint',
			],
		];
	}

	public function can_edit_post( WP_REST_Request $request ): bool {
		return current_user_can( 'edit_post', $request->get_param( "post_id" ) );
	}

	public function get_items( WP_REST_Request $request ) {
		return rest_ensure_response(
			$this->plugin->database->getItems( $request->get_param( "post_id" ) )
		);
	}

	public function add_item( WP_REST_Request $request ) {
		$result = $this->plugin->database->addItem(
			$request->get_param( "post_id" ),
			$request->get_param( "value" )
		);
		if ( false === $result ) {
			return new WP_Error( "my_plugin_insert_failed", __( 'Could not save the item.', 'my-plugin' ), [ "status" => 500 ] );
		}

		return $this->get_items( $request );
	}

}
