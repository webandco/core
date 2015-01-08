<?php
/**
 * Copyright (c) 2015 Vincent Petry <pvince81@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\Repair;

use OC\Hooks\BasicEmitter;

/**
 * Some bugs might cause file paths to be stored with a leading slash
 * in the "path" field in the file cache.
 */
class RepairBogusLeadingSlash extends BasicEmitter {
	/**
	 * @var \OC\DB\Connection
	 */
	protected $connection;

	/**
	 * @param \OC\DB\Connection $connection
	 */
	public function __construct($connection) {
		$this->connection = $connection;

		$this->findStorageInCacheStatement = $this->connection->prepare(
			'SELECT DISTINCT `storage` FROM `*PREFIX*filecache`'
			. ' WHERE `storage` in (?, ?)'
		);
		$this->renameStorageStatement = $this->connection->prepare(
			'UPDATE `*PREFIX*storages`'
			. ' SET `id` = ?'
			. ' WHERE `id` = ?'
		);
	}

	public function getName() {
		return 'Repair bogus file paths with leading slash';
	}

	/**
	 * Returns whether there are bogus entries to fix
	 *
	 * @return bool bogus entries exist
	 */
	private function mustFix() {
		$sql = 'SELECT 1 FROM `*PREFIX*filecache`'
			. ' WHERE `path` LIKE \'/%\'';
		$result = $this->connection->executeQuery($sql);
		if ($result->fetch()) {
			return true;
		}
		return false;
	}

	/**
	 * Delete bogus file cache entries
	 */
	public function run() {
		if (!$this->mustFix()) {
			// nothing to do
			return;
		}

		$sql = 'DELETE FROM `*PREFIX*filecache`'
			. ' WHERE `path` LIKE \'/%\'';
		$count = $this->connection->executeUpdate($sql);
		$this->emit('\OC\Repair', 'info', array('Deleted ' . $count . ' bogus storage entries'));
	}
}
