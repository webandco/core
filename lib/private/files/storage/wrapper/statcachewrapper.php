<?php

/**
 * Copyright (c) 2015 Vincent Petry
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\Files\Storage\Wrapper;

use OC\Files\Filesystem;
use OC\Files\Storage\Wrapper\Wrapper;

/**
 * Class StatCacheWrapper
 *
 * Caches the result of stat() and other similar operations.
 * This prevents calling the underlying storage too often when
 * querying information about the same known files.
 */
class StatCacheWrapper extends Wrapper {

	/**
	 * @var \OC\Cache\ArrayCache
	 */
	private $cache;

	public function file_put_contents($path, $data) {
		$result = $this->storage->file_put_contents($path, $data);
		if ($result !== false) {
			$this->cache->remove($path);
		}
	}

	public function copy($path1, $path2) {
		$result = $this->storage->copy($path1, $path2);
		if ($result) {
			$this->cache->remove($path2);
		}
	}

	public function rename($path1, $path2) {
		$result = $this->storage->rename($path1, $path2);
		if ($result) {
			$this->cache->remove($path1);
			$this->cache->remove($path2);
		}
	}

	public function fopen($path, $mode) {
		$result = $this->storage->fopen($path, $mode);
		if (!empty($result)) {
			$this->cache->remove($path);
		}
		return $result;
	}

	public function unlink($path) {
		$result = $this->storage->unlink($path);
		if ($result) {
			$this->cache->remove($path);
		}
		return $result;
	}

	public function rmdir($path) {
		$result = $this->storage->rmdir($path);
		if ($result) {
			$this->cache->remove($path);
		}
		return $result;
	}

	public function mkdir($path) {
		$result = $this->storage->mkdir($path);
		if ($result) {
			$this->cache->set($path, true);
		}
		return $result;
	}

}
