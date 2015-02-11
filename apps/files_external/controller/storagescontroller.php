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
use \OCP\AppFramework\Http\DataResponse;
use \OCP\AppFramework\Controller;
use OC\AppFramework\Http;
use \OCA\Files_external\Service\StoragesService;
use \OCA\Files_external\NotFoundException;

class StoragesController extends Controller {

	/**
	 * @var \OCP\IL10N
	 */
	private $l10n;

	/**
	 * @var StoragesService
	 */
	private $service;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param StoragesService $storagesService
	 */
	public function __construct(
		$AppName,
		IRequest $request,
		\OCP\IL10N $l10n,
		StoragesService $storagesService
	){
        parent::__construct($AppName, $request);
		$this->l10n = $l10n;
		$this->service = $storagesService;
    }

	/**
	 * Validate storage
	 *
	 * @param string $mountPoint storage mount point
	 * @param string $backendClass backend class name
	 * @param bool $isPersonal whether the mount point is personal
	 *
	 * @return DataResponse|null returns response in case of validation error
	 */
	private function validate($storage) {
		$mountPoint = \OC\Files\Filesystem::normalizePath($storage['mountPoint']);
		if ($mountPoint === '' || $mountPoint === '/') {
			return new DataResponse(
				array(
					'message' => (string)$this->l10n->t('Invalid mount point')
				),
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		// TODO: validate that other attrs are set

		$backends = \OC_Mount_Config::getBackends();
		if (!isset($backends[$storage['backendClass']])) {
			// invalid backend
			return new DataResponse(
				array(
					'message' => (string)$this->l10n->t('Invalid storage backend "%s"', array($backendClass))
				),
				Http::STATUS_UNPROCESSABLE_ENTITY
			);
		}

		if ($isPersonal) {
			// Verify that the mount point applies for the current user
			// Prevent non-admin users from mounting local storage and other disabled backends
			$allowedBackends = \OC_Mount_Config::getPersonalBackends();
			if (!isset($allowedBackends[$backendClass])) {
				return new DataResponse(
					array(
						'message' => (string)$this->l10n->t('Invalid storage backend "%s"', array($backendClass))
					),
					Http::STATUS_UNPROCESSABLE_ENTITY
				);
			}
		}

		return null;
	}


	/**
	 * Create an external storage entry.
	 *
	 * @param bool $isPersonal whether the mount point is personal
	 * @param string $mountPoint storage mount point
	 * @param string $backendClass backend class name
	 * @param array $backendOptions backend-specific options
	 * @param array $applicableUsers users for which to mount the storage
	 * @param array $applicableGroups groups for which to mount the storage
	 * @param int $priority priority
	 *
	 * @return DataResponse
	 */
	public function create(
		$isPersonal,
		$mountPoint,
		$backendClass,
		$backendOptions,
		$applicableUsers,
		$applicableGroups,
		$priority
	) {
		$newStorage = [
			'mountPoint' => $mountPoint,
			'backendClass' => $backendClass,
			'backendOptions' => $backendOptions,
			'applicableUsers' => $applicableUsers,
			'applicableGroups' => $applicableGroups,
			'priority' => $priority,
		];

		$response = $this->validate($newStorage);
		if (!empty($response)) {
			return $response;
		}

		$newStorage = $this->service->addStorage($newStorage, $isPersonal);

		return new DataResponse(
			$newStorage,
			Http::STATUS_CREATED
		);
	}

	/**
	 * Update an external storage entry.
	 *
	 * @param int $id storage id
	 * @param bool $isPersonal whether the mount point is personal
	 * @param string $mountPoint storage mount point
	 * @param string $backendClass backend class name
	 * @param array $backendOptions backend-specific options
	 * @param array $applicableUsers users for which to mount the storage
	 * @param array $applicableGroups groups for which to mount the storage
	 * @param int $priority priority
	 *
	 * @return DataResponse
	 */
	public function update(
		$id,
		$isPersonal,
		$mountPoint,
		$backendClass,
		$backendOptions,
		$applicableUsers,
		$applicableGroups,
		$priority
	) {
		$storage = [
			'id' => $id,
			'mountPoint' => $mountPoint,
			'backendClass' => $backendClass,
			'backendOptions' => $backendOptions,
			'applicableUsers' => $applicableUsers,
			'applicableGroups' => $applicableGroups,
			'priority' => $priority,
		];

		$response = $this->validate($storage);
		if (!empty($response)) {
			return $response;
		}

		try {
			$storage = $this->service->updateStorage($storage, $isPersonal);
		} catch (NotFoundException $e) {
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Storage with id "%i" not found', array($id))
				],
			   	Http::STATUS_NOT_FOUND
			);
		}

		return new DataResponse(
			$storage,
			Http::STATUS_CREATED
		);

	}

	/**
	 * Deletes the storage with the given id.
	 *
	 * @param int $id storage id
	 * @param bool $isPersonal whether the storage is personal
	 *
	 * @return DataResponse
	 */
	public function destroy($id, $isPersonal) {
		try {
			$this->service->removeStorage($id, $isPersonal);
		} catch (NotFoundException $e) {
			return new DataResponse(
				[
					'message' => (string)$this->l10n->t('Storage with id "%i" not found', array($id))
				],
			   	Http::STATUS_NOT_FOUND
			);
		}

		return new DataResponse([], Http::STATUS_NO_CONTENT);
	}

}

