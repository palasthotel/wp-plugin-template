<?php

use Palasthotel\WordPress\MyPlugin\Plugin;

/**
 * Functions for themes and other plugins. Keep this the only public API besides the
 * hooks in Plugin, so the classes can change without breaking anyone.
 */

function my_plugin_plugin(): Plugin {
	return Plugin::instance();
}

/**
 * @param array $args e.g. [ "text" => "Hello" ]
 *
 * @return string the rendered example template
 */
function my_plugin_render_example( array $args = [] ): string {
	return my_plugin_plugin()->templates->render( Plugin::TEMPLATE_EXAMPLE, $args );
}
