<?php

/**
 * ownCloud - perform an async http job
 *
 * @copyright (C) 2015 ownCloud, Inc.
 *
 * @author Thomas Müller <deepdiver@owncloud.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Files_Sharing\External\BackgroundJob;

use OCP\BackgroundJob\IJobList;
use OCP\IConfig;

class AsyncHttpJob extends \OC\BackgroundJob\QueuedJob {

	const MAX_TRIES = 5;

	/** @var IConfig */
	protected $config;
	/** @var IJobList */
	private $jobList;

	public function __construct(IConfig $config = null) {
		$this->config = $config;
		if (is_null($config)) {
			$this->config = \OC::$server->getConfig();
		}
	}

	public function execute($jobList, $logger = null) {
		$this->jobList = $jobList;
		parent::execute($jobList, $logger);
	}

	protected function run($argument) {
		$url = $argument[0];
		$fields = $argument[1];
		$uid = $argument[2];
		$tries = isset($argument[3]) ? $argument[3] : 0;

		$response = $this->post($url, $fields, $uid);
		$status = json_decode($response['result'], true);

		$success = ($response['success'] && $status['ocs']['meta']['statuscode'] === 100);
		if ($success) {
			return;
		}

		// queue it again
		if ($tries < self::MAX_TRIES) {
			$this->jobList->add(new AsyncHttpJob(), [$url, $fields, $uid, $tries+1]);
		}
	}

	/**
	 * send request
	 *
	 * @param string $url
	 * @param array $data
	 * @param string $uid
	 * @return array
	 */
	protected function post($url, $data, $uid) {
		$certificateManager = new \OC\Security\CertificateManager($uid, new \OC\Files\View());
		$httpHelper = new \OC\HTTPHelper($this->config, $certificateManager);
		return $httpHelper->post($url, $data);
	}
}
