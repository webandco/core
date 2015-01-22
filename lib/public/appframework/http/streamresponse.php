<?php
/**
 * @author Bernhard Posselt
 * @copyright 2015 Bernhard Posselt <dev@bernhard-posselt.com>
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCP\AppFramework\Http;

/**
 * Class StreamResponse
 *
 * @package OCP\AppFramework\Http
 */
class StreamResponse extends Response implements ICallbackResponse {
	/** @var string */
	private $filePath;

	/**
	 * @param string $filePath the path to the file which should be streamed
	 */
	public function __construct ($filePath) {
		$this->filePath = $filePath;
	}


	/**
	 * Streams the file using readfile
	 */
	public function callback () {
		@readfile($this->filePath);
	}

}
