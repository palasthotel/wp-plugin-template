<?php
/**
 * Plugin Name:       My Plugin
 * Plugin URI:        https://github.com/palasthotel/wp-my-plugin
 * Description:       One sentence on what the plugin does.
 * Version:           0.1.0
 * Author:            Palasthotel <webmaster@palasthotel.de>
 * Author URI:        https://palasthotel.de
 * Text Domain:       my-plugin
 * Domain Path:       /languages
 * Requires at least: 6.4
 * Tested up to:      7.1.2
 * Requires PHP:      8.1
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @copyright Palasthotel
 * @package Palasthotel\WordPress\MyPlugin
 */

namespace Palasthotel\WordPress\MyPlugin;

defined( 'ABSPATH' ) || exit;

// PSR-4 for classes/. A few lines instead of composer: nothing to install before the
// repository runs as a plugin, and no vendor/ in the payload.
spl_autoload_register( function ( string $class ) {
	$prefix = __NAMESPACE__ . '\\';
	if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
		return;
	}
	$file = __DIR__ . '/classes/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
	if ( is_file( $file ) ) {
		require $file;
	}
} );

class Plugin extends Components\Plugin {

	const DOMAIN = "my-plugin";

	// ----------------------------------------------------
	// asset handles
	// ----------------------------------------------------
	const HANDLE_ADMIN_JS = "my-plugin-admin";
	const HANDLE_ADMIN_CSS = "my-plugin-admin";

	// ----------------------------------------------------
	// hooks
	// ----------------------------------------------------
	const FILTER_ADD_TEMPLATES_PATHS = "my_plugin_add_templates_paths";

	// ----------------------------------------------------
	// templates
	// ----------------------------------------------------
	const THEME_FOLDER = "plugin-parts";
	const TEMPLATE_EXAMPLE = "my-plugin-example.php";

	// ----------------------------------------------------
	// options
	// ----------------------------------------------------
	const OPTION_DATA_VERSION = "my_plugin_data_version";
	const OPTION_SETTINGS = "my_plugin_settings";

	// ----------------------------------------------------
	// initialize plugin features
	// ----------------------------------------------------
	public Database $database;
	public Assets $assets;
	public Settings $settings;
	public REST $rest;
	public Templates $templates;
	public Update $update;

	public function onCreate() {

		$this->loadTextdomain( Plugin::DOMAIN, "languages" );

		$this->database  = new Database();
		$this->assets    = new Assets( $this );
		$this->settings  = new Settings( $this );
		$this->rest      = new REST( $this );
		$this->templates = new Templates( $this );
		$this->update    = new Update( $this );

	}

	/**
	 * on plugin activation, for every site of a network if activated network wide
	 */
	public function onSiteActivation() {
		parent::onSiteActivation();
		$this->database->createTables();
	}

}

Plugin::instance();

require_once __DIR__ . "/public-functions.php";
