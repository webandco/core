<?php
/**
 * ownCloud - files_external
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Vincent Petry <pvince81@owncloud.com>
 * @copyright Vincent Petry 2014
 */

namespace OCA\Files_External\Controller;


use \OCP\IConfig;
use \OCP\IUserSession;
use \OCP\IRequest;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Controller;

class StoragesController extends Controller {

	/**
	 * @var IUserSession
	 */
	private $userSession;

	/**
	 * @var IConfig
	 */
	private $config;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param IUserSession $userSession
	 */
    public function __construct($AppName, IRequest $request, IConfig $config, IUserSession $userSession){
        parent::__construct($AppName, $request);
		$this->userSession = $userSession;
		$this->config = $config;
    }

	public function create() {
		// TODO
	}

	public function update($id) {
		// TODO
	}

	public function destroy($id) {
		// TODO
	}

}

