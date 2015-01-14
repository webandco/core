<?php
/**
 * Copyright (c) 2013 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\Memcache;

use \OCP\ICacheFactory;

class Factory implements ICacheFactory {
	/**
	 * @var string $globalPrefix
	 */
	private $globalPrefix;

	/**
	 * @var string[] $distributedCaches
	 */
	private $distributedCaches = [
		'\\OC\\Memcache\\Redis',
		'\\OC\\Memcache\\Memcached',
	];

	/**
	 * @var string[] $localCaches
	 */
	private $localCaches = [
		'\\OC\\Memcache\\APCu',
		'\\OC\\Memcache\\APC',
		'\\OC\\Memcache\\XCache',
	];

	/**
	 * @param string $globalPrefix
	 */
	public function __construct($globalPrefix) {
		$this->globalPrefix = $globalPrefix;
	}

	/**
	 * @param string[] $cacheList
	 * @return string|null
	 */
	private function getAvailable(array $cacheList) {
		foreach ($cacheList as $cache) {
			if ($cache::isAvailable()) {
				return $cache;
			}
		}
	}

	/**
	 * check distributed backend availability
	 *
	 * @return string|null
	 */
	public function getAvailableDistributed() {
		return $this->getAvailable($this->distributedCaches);
	}

	/**
	 * check local backend availability
	 *
	 * @return string|null
	 */
	public function getAvailableLocal() {
		return $this->getAvailable($this->localCaches);
	}

	/**
	 * create a distributed cache instance
	 *
	 * @param string $prefix
	 * @return \OC\Memcache\Cache|null
	 */
	public function createDistributed($prefix = '') {
		if ($cache = $this->getAvailableDistributed()) {
			return new $cache($this->globalPrefix . '/' . $prefix);
		}
	}

	/**
	 * create a local cache instance
	 *
	 * @param string $prefix
	 * @return \OC\Memcache\Cache|null
	 */
	public function createLocal($prefix = '') {
		if ($cache = $this->getAvailableLocal()) {
			return new $cache($this->globalPrefix . '/' . $prefix);
		}
	}

	/**
	 * get a cache instance, or Null backend if no backend available
	 * tries to get a distributed backend first, else a local backend
	 *
	 * @param string $prefix
	 * @return \OC\Memcache\Cache
	 */
	public function create($prefix = '') {
		if ($cache = $this->createDistributed($prefix)) {
			return $cache;
		}
		if ($cache = $this->createLocal($prefix)) {
			return $cache;
		}
		return new Null();
	}

	/**
	 * check memcache availability
	 *
	 * @return bool
	 */
	public function isAvailable() {
		return $this->getAvailableDistributed() || $this->getAvailableLocal();
	}

	/**
	 * @see \OC\Memcache\Factory::createLocal()
	 * @param string $prefix
	 * @return \OC\Memcache\Cache|null
	 */
	public function createLowLatency($prefix = '') {
		return $this->createLocal($prefix);
	}

	/**
	 * @see \OC\Memcache\Factory::getAvailableLocal()
	 * @return bool
	 */
	public function isAvailableLowLatency() {
		return (bool)$this->getAvailableLocal();
	}


}
