<?php

namespace Palasthotel\WordPress\MyPlugin;

/**
 * Migrates stored data between plugin versions. Raise DATA_VERSION and add a method
 * update_<n>() for every change of the data structure; it runs once per site on the
 * next admin request - an update through wordpress.org does not trigger activation.
 */
class Update extends Components\Update {

	const DATA_VERSION = 1;

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		add_action( 'admin_init', [ $this, 'checkUpdates' ] );
	}

	function getVersion(): int {
		return self::DATA_VERSION;
	}

	function getCurrentVersion(): int {
		return intval( get_option( Plugin::OPTION_DATA_VERSION, 0 ) );
	}

	function setCurrentVersion( int $version ) {
		update_option( Plugin::OPTION_DATA_VERSION, $version );
	}

	public function update_1() {
		$this->plugin->database->createTables();
	}

}
