<?php

namespace Palasthotel\WordPress\MyPlugin;

/**
 * Registers and enqueues scripts and styles. Strings for JavaScript are translated
 * here and handed over with wp_localize_script - no @wordpress/i18n in the bundle.
 */
class Assets extends Components\Component {

	private Components\Assets $helper;

	public function onCreate() {
		$this->helper = new Components\Assets( $this->plugin->path, $this->plugin->url );
		add_action( 'init', [ $this, 'register' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin' ] );
	}

	public function register() {
		$this->helper->registerStyle(
			Plugin::HANDLE_ADMIN_CSS,
			"assets/admin.css"
		);
		$this->helper->registerScript(
			Plugin::HANDLE_ADMIN_JS,
			"assets/admin.js"
		);
	}

	public function enqueue_admin( string $hook_suffix ) {
		// only on our own settings page, not on every admin screen
		if ( "settings_page_" . Settings::PAGE !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style( Plugin::HANDLE_ADMIN_CSS );
		wp_enqueue_script( Plugin::HANDLE_ADMIN_JS );
		wp_localize_script(
			Plugin::HANDLE_ADMIN_JS,
			"MyPlugin",
			[
				"rest" => [
					"namespace" => REST::NAMESPACE,
				],
				"i18n" => [
					"label_empty" => __( 'The label is empty.', 'my-plugin' ),
				],
			]
		);
	}

}
