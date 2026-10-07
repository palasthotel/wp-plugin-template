<?php

namespace Palasthotel\WordPress\MyPlugin;

/**
 * Finds templates in the theme first (plugin-parts/), then in paths other plugins add
 * via the my_plugin_add_templates_paths filter, then in templates/ of this plugin.
 */
class Templates extends Components\Component {

	private Components\Templates $component;

	public function onCreate() {
		$this->component = new Components\Templates( $this->plugin->path );
		$this->component->useThemeDirectory( Plugin::THEME_FOLDER );
		$this->component->useAddTemplatePathsFilter( Plugin::FILTER_ADD_TEMPLATES_PATHS );
	}

	/**
	 * @return string|false
	 */
	public function getPath( string $template ) {
		return $this->component->get_template_path( $template );
	}

	/**
	 * Renders a template and returns the output. $args are available as $args in the
	 * template; escape them there, at the point of output.
	 */
	public function render( string $template, array $args = [] ): string {
		$path = $this->getPath( $template );
		if ( ! is_string( $path ) ) {
			return "";
		}
		ob_start();
		include $path;

		return ob_get_clean();
	}

}
