<?php

/**
 * ownCloud - manage encryption modules
 *
 * @copyright (C) 2015 ownCloud, Inc.
 *
 * @author Bjoern Schiessle <schiessle@owncloud.com>
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

namespace OCP\Encryption {

	/**
	 * This class provides access to files encryption apps.
	 *
	 */
	interface IManager {

		/**
		 * Check if encryption is available (at least one encryption module needs to be enabled)
		 *
		 * @return bool true if enabled, false if not
		 */
		function isEnabled();

		/**
		 * Registers an address book
		 *
		 * @param \OCP\IEncryptionModule $module
		 * @return void
		 */
		function registerEncryptionModule(\OCP\IEncryptionModule $module);

		/**
		 * Unregisters an address book
		 *
		 * @param \OCP\IEncryptionModule $module
		 * @return void
		 */
		function unregisterEncryptionModule(\OCP\IEncryptionModule $module);

		/**
		 * get a list of all encryption modules
		 *
		 * @return array
		 */
		function getEncryptionModules();

		/**
		 * get a specific encryption module
		 *
		 * @param string $module unique key of encryption module, if no module is defined we take the default module
		 * @return \OCP\IEncryptionModule
		 */
		function getEncryptionModule($module = null);

	}

}
