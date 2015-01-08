<?php
/**
 * Copyright (c) 2015 Vincent Petry <pvince81@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * Tests for the converting of legacy storages to home storages.
 *
 * @see \OC\Repair\RepairBogusLeadingSlash
 */
class TestRepairBogusLeadingSlash extends \Test\TestCase {

	/**
	 * @var \OC\DB\Connection
	 */
	private $connection;

	/**
	 * @var \OC\Repair\RepairBogusLeadingSlash
	 */
	private $repair;

	/**
	 * @var \OCP\Files\Storage
	 */
	private $storage;

	/**
	 * @var \OC\Files\Cache\Cache
	 */
	private $cache;

	protected function setUp() {
		parent::setUp();

		$this->connection = \OC_DB::getConnection();
		$this->repair = new \OC\Repair\RepairBogusLeadingSlash($this->connection);
		$this->storage = new \OC\Files\Storage\Temporary(array());
		$this->cache = new \OC\Files\Cache\Cache($this->storage);
	}

	protected function tearDown() {
		$sql = 'DELETE FROM `*PREFIX*storages`';
		$this->connection->executeQuery($sql);
		$sql = 'DELETE FROM `*PREFIX*filecache`';
		$this->connection->executeQuery($sql);
		parent::tearDown();
	}

	/**
	 * Test that a single entry's slash gets stripped
	 */
	public function testStripSlashForSingleEntry() {
		$testFile = array(
			'size' => 123,
			'mtime' => 50,
			'mimetype' => 'text/plain'
		);
		$testDir = array(
			'size' => 123,
			'mtime' => 55,
			'mimetype' => 'httpd/unix-directory'
		);
		$rootId = $this->cache->put('', $testDir);
		$this->cache->put('/bogus.txt', $testFile);
		$this->cache->put('/bogusfolder', $testDir);
		$this->cache->put('/bogusfolder/test.txt', $testFile);
		// sometimes they get a parent, sometimes they don't...
		$this->cache->put('/boguswithparent.txt', array_merge($testFile, array('parent' => $rootId)));
		$this->cache->put('regular.txt', $testFile);
		$this->cache->put('regularfolder', $testDir);
		$this->cache->put('regularfolder/test2.txt', $testFile);

		$this->assertNotNull($this->cache->get('/bogus.txt'));
		$this->assertNotNull($this->cache->get('/bogusfolder'));
		$this->assertNotNull($this->cache->get('/bogusfolder/test.txt'));
		$this->assertNotNull($this->cache->get('/boguswithparent.txt'));
		$this->assertNotNull($this->cache->get('regular.txt'));
		$this->assertNotNull($this->cache->get('regularfolder'));
		$this->assertNotNull($this->cache->get('regularfolder/test2.txt'));

		$this->repair->run();

		// TODO: also assert that the correct parent has been set
		$this->assertNull($this->cache->get('/bogus.txt'));
		$this->assertNull($this->cache->get('/bogusfolder'));
		$this->assertNull($this->cache->get('/bogusfolder/test.txt'));
		$this->assertNull($this->cache->get('/boguswithparent.txt'));
		$this->assertNotNull($this->cache->get('bogus.txt'));
		$this->assertNotNull($this->cache->get('bogusfolder'));
		$this->assertNotNull($this->cache->get('bogusfolder/test.txt'));
		$this->assertNotNull($this->cache->get('boguswithparent.txt'));
		$this->assertNotNull($this->cache->get('regular.txt'));
		$this->assertNotNull($this->cache->get('regularfolder'));
		$this->assertNotNull($this->cache->get('regularfolder/test2.txt'));
	}

	/**
	 * Test that a bogus entry gets properly merged into
	 * the correct entry.
	 */
	public function testMergeDuplicateEntry() {
		$testFile = array(
			'size' => 123,
			'mtime' => 50,
			'mimetype' => 'text/plain'
		);
		$testDir = array(
			'size' => 123,
			'mtime' => 55,
			'mimetype' => 'httpd/unix-directory'
		);
		$rootId = $this->cache->put('', $testDir);
		$this->cache->put('/bogus.txt', $testFile);
		$this->cache->put('bogus.txt', $testFile);
		$this->cache->put('/bogusfolder', $testDir);
		$this->cache->put('/bogusfolder/test.txt', $testFile);
		$this->cache->put('bogusfolder', $testDir);
		$this->cache->put('bogusfolder/test.txt', $testFile);
		// sometimes they get a parent, sometimes they don't...
		$this->cache->put('/boguswithparent.txt', array_merge($testFile, array('parent' => $rootId)));
		$this->cache->put('regular.txt', $testFile);
		$this->cache->put('regularfolder', $testDir);
		$this->cache->put('regularfolder/test2.txt', $testFile);

		$this->assertNotNull($this->cache->get('/bogus.txt'));
		$this->assertNotNull($this->cache->get('/bogusfolder'));
		$this->assertNotNull($this->cache->get('/bogusfolder/test.txt'));
		$this->assertNotNull($this->cache->get('/boguswithparent.txt'));
		$this->assertNotNull($this->cache->get('regular.txt'));
		$this->assertNotNull($this->cache->get('regularfolder'));
		$this->assertNotNull($this->cache->get('regularfolder/test2.txt'));

		$this->repair->run();

		$this->assertNull($this->cache->get('/bogus.txt'));
		$this->assertNull($this->cache->get('/bogusfolder'));
		$this->assertNull($this->cache->get('/bogusfolder/test.txt'));
		$this->assertNull($this->cache->get('/boguswithparent.txt'));
		$this->assertNotNull($this->cache->get('bogus.txt'));
		$this->assertNotNull($this->cache->get('bogusfolder'));
		$this->assertNotNull($this->cache->get('bogusfolder/test.txt'));
		$this->assertNotNull($this->cache->get('boguswithparent.txt'));
		$this->assertNotNull($this->cache->get('regular.txt'));
		$this->assertNotNull($this->cache->get('regularfolder'));
		$this->assertNotNull($this->cache->get('regularfolder/test2.txt'));

	}
}
