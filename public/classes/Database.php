<?php

namespace Palasthotel\WordPress\MyPlugin;

/**
 * Example of an own table. Delete this class, its use in Plugin, Update and
 * uninstall.php if the plugin gets along with options and post meta.
 */
class Database extends Components\Database {

	public string $table;

	public function init() {
		$this->table = $this->wpdb->prefix . "my_plugin_items";
	}

	/**
	 * @param int $postId
	 *
	 * @return array[]
	 */
	public function getItems( int $postId ): array {
		// A local $wpdb, because the sniffs only recognise $wpdb->prepare() as prepared.
		// %i quotes the table name as an identifier (WordPress 6.2+).
		$wpdb = $this->wpdb;

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT id, post_id, value, created FROM %i WHERE post_id = %d ORDER BY id", $this->table, $postId ),
			ARRAY_A
		);
	}

	/**
	 * @param int $postId
	 * @param string $value
	 *
	 * @return bool|int
	 */
	public function addItem( int $postId, string $value ) {
		return $this->wpdb->insert(
			$this->table,
			[
				"post_id" => $postId,
				"value"   => $value,
			],
			[ "%d", "%s" ]
		);
	}

	/**
	 * create tables if they do not exist
	 */
	public function createTables() {
		parent::createTables();
		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->table
			(
			 id bigint(20) unsigned auto_increment,
			 post_id bigint(20) unsigned NOT NULL,
			 value varchar(255) NOT NULL,
			 created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			 primary key (id),
			 key (post_id)
			) {$this->wpdb->get_charset_collate()};" );
	}

}
