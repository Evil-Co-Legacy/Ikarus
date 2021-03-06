<?php
/**
 * This file is part of the Ikarus Framework.
 * The Ikarus Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * The Ikarus Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * You should have received a copy of the GNU Lesser General Public License
 * along with the Ikarus Framework. If not, see <http://www.gnu.org/licenses/>.
 */
namespace ikarus\system\cache;

use ikarus\system\exception\StrictStandardException;
use ikarus\system\exception\SystemException;
use ikarus\system\Ikarus;
use ikarus\util\ClassUtil;

/**
 * Manages all cache sources
 * @author                    Johannes Donath
 * @copyright                 2012 Evil-Co.de
 * @package                   de.ikarus-framework.core
 * @subpackage                system
 * @category                  Ikarus Framework
 * @license                   GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version                   2.0.0-0001
 */
class CacheManager {

	/**
	 * Contains a prefix for adapter class names
	 * @var                                string
	 */
	const ADAPTER_CLASS_PREFIX = 'ikarus\\system\\cache\\adapter\\';

	/**
	 * Contains all active cache connections
	 * @var                                array<ikarus\system\cache\adapter\ICacheAdapter>
	 */
	protected $connections = array();

	/**
	 * Contains the current default adapter
	 * @var                                ikarus\system\cache\adapter\ICacheAdapter;
	 */
	protected $defaultAdapter = null;

	/**
	 * Contains predefined adapter fallbacks
	 * @var                                array<string>
	 */
	protected $fallbacks = array();

	/**
	 * Contains a list of loaded adapters
	 * @var                                array<string>
	 */
	protected $loadedAdapters = array();

	/**
	 * Creates a new cache connection
	 * @param                        string $adapterName
	 * @param                        array  $parameters
	 * @param                        string $linkID
	 * @throws                        SystemException
	 * @return                        ikarus\system\cache\adapter.ICacheAdapter
	 * @api
	 */
	public function createConnection ($adapterName, array $parameters = array(), $linkID = null) {
		// validate adapter name
		if (!$this->adapterIsLoaded ($adapterName)) throw new SystemException("Cannot start adapter '%s': The adapter is not loaded", $adapterName);

		// get class name
		$className = static::ADAPTER_CLASS_PREFIX . $adapterName;

		try {
			// create instance
			$instance = new $className($parameters);
		} Catch (SystemException $ex) {
			if (!isset($this->fallbacks[$linkID])) throw $ex;
			$instance = $this->getConnection ($this->fallbacks[$linkID]);
		}

		if ($linkID !== null) $this->connections[$linkID] = $instance;

		return $this->connections[] = $instance;
	}

	/**
	 * Returns the current default adapter
	 * @return                        ikarus\system\cache\adapter.ICacheAdapter
	 * @api
	 */
	public function getDefaultAdapter () {
		return $this->defaultAdapter;
	}

	/**
	 * Returns true if the given adapter is already loaded
	 * @param                        string $adapterName
	 * @return                        boolean
	 * @api
	 */
	public function adapterIsLoaded ($adapterName) {
		return array_key_exists ($adapterName, $this->loadedAdapters);
	}

	/**
	 * Returns a connection.
	 * @param                        string $linkID
	 * @return                        array<ikarus\system\cache\adapter\ICacheAdapter>
	 * @api
	 */
	public function getConnection ($linkID) {
		// strict standards
		if (!array_key_exists ($linkID, $this->connections)) throw new StrictStandardException('Cannot access non-existing cache connection "%s"', $linkID);

		// return connection
		return $this->connections[$linkID];
	}

	/**
	 * Loads an adapter
	 * @param                        string $adapterName
	 * @throws                        StrictStandardException
	 * @return                        boolean
	 * @api
	 */
	public function loadAdapter ($adapterName) {
		// get class name
		$className = static::ADAPTER_CLASS_PREFIX . $adapterName;

		// validate adapter
		if (!class_exists ($className)) throw new StrictStandardException("The cache adapter class '%s' for adapter '%s' does not exist", $className, $adapterName);
		if (!ClassUtil::isInstanceOf ($className, 'ikarus\system\cache\adapter\ICacheAdapter')) throw new StrictStandardException("The cache adapter class '%s' of adapter '%s' is not an implementation of ikarus\\system\\cache\\adapter\\ICacheAdapter");

		// check for php side support
		if (!call_user_func (array($className, 'isSupported'))) return false;

		// add to loaded adapter list
		$this->loadedAdapters[$adapterName] = $className;

		return true;
	}

	/**
	 * Loads all available adapters
	 * @return                        void
	 * @internal                        This will be called by Ikarus during it's init period.
	 */
	protected function loadAdapters () {
		$sql = "SELECT
				*
			FROM
				ikarus" . IKARUS_N . "_cache_adapter";
		$stmt = Ikarus::getDatabaseManager ()->getDefaultAdapter ()->prepareStatement ($sql);
		$resultList = $stmt->fetchList ();

		foreach ($resultList as $result) {
			$this->loadAdapter ($result->adapterClass);
		}
	}

	/**
	 * Sets the default cache adapter
	 * @param                        ikarus\system\cache\adapter\ICacheAdapter $handle
	 * @return                        void
	 * @api
	 */
	public function setDefaultAdapter (adapter\ICacheAdapter $handle) {
		// set as default
		$this->defaultAdapter = $handle;
	}

	/**
	 * Sets a fallback for specified adapter
	 * @param                        string $linkID
	 * @param                        string $fallback
	 * @throws                        SystemException
	 * @return                        void
	 * @api
	 */
	public function setFallback ($linkID, $fallback) {
		// validate linkIDs
		if (!array_key_exists ($linkID, $this->connections)) throw new SystemException("Cannot create fallback: The specified linkID does not name a cache connection", $linkID);

		// save fallback
		$this->fallbacks[$fallback] = $linkID;
	}

	/**
	 * Closes all cache connections
	 * @return                        void
	 * @internal                        This will be called by Ikarus during it's shutdown period.
	 */
	public function shutdown () {
		foreach ($this->connections as $connection) {
			$connection->shutdown ();
		}
	}

	/**
	 * Starts all cache connections
	 * @return                        void
	 * @internal                        This will be called by Ikarus during it's init period.
	 */
	protected function startAdapters () {
		$sql = "SELECT
				source.*,
				adapter.adapterClass
			FROM
				ikarus" . IKARUS_N . "_cache_source source
			LEFT JOIN
				ikarus" . IKARUS_N . "_cache_adapter adapter
			ON
				(source.adapterID = adapter.adapterID)
			WHERE
				source.isDisabled = 0
			ORDER BY
				source.fallbackFor DESC, source.connectionID ASC";
		$stmt = Ikarus::getDatabaseManager ()->getDefaultAdapter ()->prepareStatement ($sql);
		$resultList = $stmt->fetchList ();

		foreach ($resultList as $result) {
			$adapter = $this->createConnection ($result->adapterClass, (!empty($result->adapterParameters) ? unserialize ($result->adapterParameters) : array()), $result->connectionID);
			if ($result->isDefaultConnection) $this->setDefaultAdapter ($adapter);
			if ($result->fallbackFor) $this->setFallback ($result->connectionID, $result->fallbackFor);
		}
	}

	/**
	 * Starts the default adapter
	 * @return                        void
	 * @internal                        This will be called by Ikarus during it's init period.
	 */
	public function startDefaultAdapter () {
		$this->loadAdapters ();
		$this->startAdapters ();
	}
}

?>