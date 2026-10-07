<?php
/**
 * Plugin Name:       My Plugin - DEV
 * Description:       Dev inc file
 * Version:           X.X.X
 * Requires at least: X.X
 * Tested up to:      X.X.X
 * Author:            Palasthotel <webmaster@palasthotel.de>
 * Author URI:        https://palasthotel.de
 * Domain Path:       /public/languages
 */

defined( 'ABSPATH' ) || exit;

use Palasthotel\WordPress\MyPlugin\Plugin;

include dirname( __FILE__ ) . "/public/plugin.php";

// public/plugin.php registers its activation hooks for its own file, which is not the
// one WordPress activates while the repository itself is the plugin.
register_activation_hook( __FILE__, function ( $networkWide ) {
	Plugin::instance()->onActivation( $networkWide );
} );

register_deactivation_hook( __FILE__, function ( $networkWide ) {
	Plugin::instance()->onDeactivation( $networkWide );
} );
