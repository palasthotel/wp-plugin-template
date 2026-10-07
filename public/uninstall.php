<?php
/**
 * Runs when the plugin is deleted under Plugins - not on deactivation. Removes
 * everything the plugin stored, on every site of a network.
 *
 * @package Palasthotel\WordPress\MyPlugin
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$my_plugin_uninstall_site = function () {
	global $wpdb;

	delete_option( 'my_plugin_data_version' );
	delete_option( 'my_plugin_settings' );

	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}my_plugin_items" );
};

if ( is_multisite() ) {
	foreach ( get_sites( [ 'fields' => 'ids', 'number' => 0 ] ) as $my_plugin_site_id ) {
		switch_to_blog( $my_plugin_site_id );
		$my_plugin_uninstall_site();
		restore_current_blog();
	}
} else {
	$my_plugin_uninstall_site();
}
