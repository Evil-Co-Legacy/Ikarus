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
namespace ikarus\system;

use ikarus\pattern\NonInstantiableClass;
use ikarus\system\application\ApplicationManager;
use ikarus\system\cache\CacheManager;
use ikarus\system\configuration\Configuration;
use ikarus\system\database\DatabaseManager;
use ikarus\system\event\EventManager;
use ikarus\system\exception\MissingDependencyException;
use ikarus\system\exception\StrictStandardException;
use ikarus\system\exception\SystemException;
use ikarus\system\extension\ExtensionManager;
use ikarus\system\io\FilesystemManager;
use ikarus\util\ClassUtil;
use ikarus\util\EncryptionManager;
use ikarus\util\FileUtil;

// includes
require_once (IKARUS_DIR . 'lib/core.defines.php');
require_once (IKARUS_DIR . 'lib/pattern/NonInstantiableClass.class.php');

/**
 * Manages all core instances
 * @author                    Johannes Donath
 * @copyright                 2011 Evil-Co.de
 * @package                   de.ikarus-framework.core
 * @subpackage                system
 * @category                  Ikarus Framework
 * @license                   GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version                   2.0.0-0001 (Codename: Raw Emerald)
 */
class Ikarus extends NonInstantiableClass {

	/**
	 * Contains the name of the file wich contains the core configuration
	 * @var                        string
	 */
	const CONFIGURATION_FILE = 'options.inc.php';

	/**
	 * Contains an instance of ApplicationManager
	 * @var                        ApplicationManager
	 */
	protected static $applicationManagerObj = null;

	/**
	 * Contains an instance of CacheManager
	 * @var                        CacheManager
	 */
	protected static $cacheManagerObj = null;

	/**
	 * Contains all requested appliation components
	 * @var                        array
	 */
	protected static $componentList = array();

	/**
	 * Contains an instance of Configuration
	 * @var                        Configuration
	 */
	protected static $configurationObj = null;

	/**
	 * Contains the time of init sequence
	 * @var                        integer
	 */
	protected static $currentTime = -1;

	/**
	 * Contains an instance of DatabaseManager
	 * @var                        DatabaseManager
	 */
	protected static $databaseManagerObj = null;

	/**
	 * Contains an instance of EventManager
	 * @var                        EventManager
	 */
	protected static $eventManagerObj = null;

	/**
	 * Contains an instance of ExtensionManager
	 * @var                        ExtensionManager
	 */
	protected static $extensionManagerObj = null;

	/**
	 * Contains an instance of FilesystemManager
	 * @var                        FilesystemManager
	 */
	protected static $filesystemManagerObj = null;

	/**
	 * Starts all core instances
	 * @return                        void
	 * @internal                        This method gets called by our index script.
	 */
	public static final function init () {
		// save current time
		static::$currentTime = time ();

		// set default locale to en-US
		setLocale (LC_ALL, 'en_US');

		// start core components
		static::initDatabaseManager ();
		static::initConfiguration ();
		static::initEncryptionManager ();
		static::initCacheManager ();
		static::initEventManager ();
		static::initApplicationManager ();
		static::initExtensionManager ();

		// boot applications
		static::$applicationManagerObj->boot ();
	}

	/**
	 * Shuts the whole framework down
	 * @return                        void
	 * @internal                        This method gets called by PHP.
	 */
	public static final function shutdown () {
		if (static::getDatabaseManager () !== null) echo static::getDatabaseManager ()->getDefaultAdapter ()->getQueryCount (); // FIXME: Remove this

		// shut down components
		if (static::getExtensionManager () !== null) static::getExtensionManager ()->shutdown ();
		if (static::getApplicationManager () !== null) static::getApplicationManager ()->shutdown ();
		if (static::getCacheManager () !== null) static::getCacheManager ()->shutdown ();
		if (static::$filesystemManagerObj !== null) static::getFilesystemManager ()->shutdown ();
		if (static::getDatabaseManager () !== null) static::getDatabaseManager ()->shutdown ();

		// stop output buffer (if any)
		if (ob_get_level () > 0) ob_end_flush ();
	}

	/**
	 * Checks wheater a component abbreviation exists
	 * @param                        string $abbreviation
	 * @return                        boolean
	 * @api
	 */
	public static function componentAbbreviationExists ($abbreviation) {
		return array_key_exists ($abbreviation, static::$componentList);
	}

	/**
	 * Checks wheater a component with the same abbreviation does already exist
	 * @param                        string $componentName
	 * @param                        string $abbreviation
	 * @return                        boolean
	 * @api
	 */
	public static function componentLoaded ($componentName, $abbreviation = null) {
		// check abbreviation
		if ($abbreviation != null) {
			if (static::componentAbbreviationExists ($abbreviation) and ($componentName == null or ($componentName != null and get_class (static::getComponent ($abbreviation)) == $componentName))) return true;

			return false;
		}

		// fallback check
		foreach (static::$componentList as $component) {
			if (get_class ($component) == $componentName) return true;
		}

		// nothing found
		return false;
	}

	/**
	 * Configures all loaded components.
	 * @param \ikarus\system\application\IApplication|\ikarus\system\IApplication $application
	 * @return                        void
	 * @api
	 */
	public static function configureComponents (application\IApplication $application) {
		foreach (static::$componentList as $component) {
			if (ClassUtil::isInstanceOf ($component, 'ikarus\\system\\application\\IConfigurableComponent')) $component->configure ($application);
		}
	}

	/**
	 * Returns the current ApplicationManager instance
	 * @return                ikarus\system\application\ApplicationManager
	 * @api
	 */
	public static final function getApplicationManager () {
		return static::$applicationManagerObj;
	}

	/**
	 * Returns the current CacheManager instance
	 * @return                ikarus\system\cache\CacheManager
	 * @api
	 */
	public static final function getCacheManager () {
		return static::$cacheManagerObj;
	}

	/**
	 * Returns an active component
	 * @param                        string $abbreviation
	 * @throws                        StrictStandardException
	 * @return                        void
	 * @api
	 */
	public static final function getComponent ($abbreviation) {
		if (!static::componentAbbreviationExists ($abbreviation)) throw new StrictStandardException("The component with the abbreviation '%s' does not exist", $abbreviation);

		return static::$componentList[$abbreviation];
	}

	/**
	 * Returns the current Configuration instance
	 * @return                ikarus\system\configuration\Configuration
	 * @api
	 */
	public static final function getConfiguration () {
		return static::$configurationObj;
	}

	/**
	 * Returns the current DatabaseManager instance
	 * @return                ikarus\system\database\DatabaseManager
	 * @api
	 */
	public static final function getDatabaseManager () {
		return static::$databaseManagerObj;
	}

	/**
	 * Returns the current EventManager instance
	 * @return                ikarus\system\event\ExtensionManager
	 * @api
	 */
	public static final function getEventManager () {
		return static::$eventManagerObj;
	}

	/**
	 * Returns the current ExtensionManager instance
	 * @return                ikarus\system\extension\ExtensionManager
	 * @api
	 */
	public static final function getExtensionManager () {
		return static::$extensionManagerObj;
	}

	/**
	 * Returns the current FilesystemManager instance
	 * @return                ikarus\system\io\FilesystemManager
	 * @api
	 */
	public static final function getFilesystemManager () {
		if (static::$filesystemManagerObj === null) static::initFilesystemManager ();

		return static::$filesystemManagerObj;
	}

	/**
	 * Returns Ikarus' package ID
	 * @todo                This is currently hardcoded.
	 * @return                integer
	 * @api
	 */
	public static final function getPackageID () {
		return 1;
	}

	/**
	 * Returns Ikarus' path with a trailing slash.
	 * @return                string
	 * @api
	 */
	public static final function getPath () {
		if (!class_exists ('FileUtil', true)) return dirname (__FILE__) . '/../../';

		return FileUtil::getRealPath (dirname (__FILE__) . '/../../');
	}

	/**
	 * Returns the init time.
	 * @return                integer
	 * @api
	 */
	public static final function getTime () {
		return static::$currentTime;
	}

	/**
	 * Starts the application manager instance
	 * @return                        void
	 */
	protected static final function initApplicationManager () {
		static::$applicationManagerObj = new ApplicationManager();
	}

	/**
	 * Starts the cache manager instance
	 * @return                void
	 */
	protected static final function initCacheManager () {
		static::$cacheManagerObj = new CacheManager();

		static::$cacheManagerObj->startDefaultAdapter ();
	}

	/**
	 * Starts the configuration instance
	 * @return                void
	 */
	protected static final function initConfiguration () {
		static::$configurationObj = new Configuration(static::getPath () . static::CONFIGURATION_FILE);
		static::$configurationObj->loadOptions ();

		// disable or enable assertions
		assert_options (ASSERT_ACTIVE, static::$configurationObj->get ('global.advanced.debug'));
	}

	/**
	 * Starts the database manager instance
	 * @return                void
	 */
	protected static final function initDatabaseManager () {
		static::$databaseManagerObj = new DatabaseManager();

		static::$databaseManagerObj->startDefaultAdapter ();
	}

	/**
	 * Loads up all needed information for the global encryption system.
	 * Note: There are some dummy values in our database which will be used instead if there's no special configuration available.
	 * @return                void
	 */
	protected static final function initEncryptionManager () {
		EncryptionManager::initGlobalEncryption ();
	}

	/**
	 * Starts the event manager instance
	 * @return                void
	 */
	protected static final function initEventManager () {
		static::$eventManagerObj = new EventManager();
	}

	/**
	 * Starts the extension manager instance
	 * @return                void
	 */
	protected static final function initExtensionManager () {
		static::$extensionManagerObj = new ExtensionManager();
	}

	/**
	 * Starts the filesystem manager instance
	 * @return                void
	 */
	protected static final function initFilesystemManager () {
		;
		static::$filesystemManagerObj = new FilesystemManager();
		static::$filesystemManagerObj->startDefaultAdapter ();
	}

	/**
	 * Loads a requested application component
	 * @param                        string $componentName
	 * @param                        string $abbreviation
	 * @throws                        StrictStandardException
	 * @return                        boolean
	 * @api
	 */
	public static function requestComponent ($componentName, $abbreviation = null) {
		// get abbreviation
		if ($abbreviation === null) $abbreviation = basename ($componentName);

		// check for already existing components
		if (static::componentLoaded ($componentName, $abbreviation)) return true;
		if (static::componentAbbreviationExists ($abbreviation)) throw new StrictStandardException("Cannot load requested component: '%s': The requested component abbreviation does already exist", $componentName);

		// load component
		if (!class_exists ($componentName, true)) throw new SystemException("Cannot load requested component '%s': The requested component was not found", $componentName);

		// create component instance
		static::$componentList[$abbreviation] = new $componentName();

		return true;
	}

	/**
	 * Autoloads missing classes
	 * @param                string $className
	 * @throws exception\SystemException
	 * @throws exception\MissingDependencyException
	 * @return                void
	 * @api
	 */
	public static function autoload ($className) {
		// split namespaces
		$namespaces = explode ('\\', $className);

		// autoloading inside of our application requires namespaces
		if (count ($namespaces) > 1) {
			// get application prefix from namespace
			$applicationPrefix = array_shift ($namespaces);

			// check for registered applications
			if ($applicationPrefix == 'ikarus') { // FIXME: This should not be hardcoded
				// generate class path
				$classPath = static::getPath () . 'lib/' . implode ('/', $namespaces) . '.class.php';

				// include needed file
				if (file_exists ($classPath)) {
					require_once ($classPath);

					// check for missing classes
					if (!ClassUtil::classExists ($className, false)) throw new MissingDependencyException("Cannot find class '%s' in path '%s'", $className, $classPath);

					// check for NotImplemented patern
					if ($className != 'ikarus\\pattern\\NotImplemented' and ClassUtil::isInstanceOf ($className, 'ikarus\\pattern\\NotImplemented')) throw new SystemException("Cannot load class '%s': %s", $className, 'The class isn\'t implemented');

					// check dependencies
					ClassUtil::loadDependencies ($className);

					// stop here
					return;
				}
			} elseif (static::$applicationManagerObj->applicationPrefixExists ($applicationPrefix)) {
				// generate class path
				$classPath = static::$applicationManagerObj->getApplication ($applicationPrefix)->getLibraryPath () . implode ('/', $namespaces) . '.class.php';

				// include needed file
				if (file_exists ($classPath)) {
					require_once ($classPath);

					// check for missing classes
					if (!ClassUtil::classExists ($className, false)) throw new MissingDependencyException("Cannot find class '%s' in path '%s'", $className, $classPath);

					// check for NotImplemented patern
					if ($className != 'ikarus\\pattern\\NotImplemented' and ClassUtil::isInstanceOf ($className, 'ikarus\\pattern\\NotImplemented')) throw new SystemException("Cannot load class '%s': %s", $className, 'The class isn\'t implemented');

					// check dependencies
					ClassUtil::loadDependencies ($className);

					// stop here
					return;
				}
			}
		}

		if (static::getExtensionManager () !== null) static::getExtensionManager ()->autoload ($className);
	}

	/**
	 * Handles failed assertions
	 * @param                        string  $file
	 * @param                        integer $line
	 * @param                        integer $code
	 * @throws exception\SystemException
	 * @internal param int $message
	 * @return                        void
	 * @api
	 */
	public static function handleAssertion ($file, $line, $code) {
		// get the relative version of file parameter
		$file = FileUtil::removeTrailingSlash (FileUtil::getRelativePath (static::getPath (), $file));

		// print error message
		throw new SystemException("Assertion failed in file %s on line %u", $file, $line);
	}

	/**
	 * Handles application errors
	 * @param                integer $errorNo
	 * @param                string  $message
	 * @param                string  $filename
	 * @param                integer $lineNo
	 * @throws                SystemException
	 * @return                void
	 * @api
	 */
	public static final function handleError ($errorNo, $message, $filename, $lineNo) {
		$type = 'error';
		switch ($errorNo) {
			case 2:
				$type = 'warning';
				break;
			case 8:
				$type = 'notice';
				break;
		}

		throw new SystemException('PHP ' . $type . ' in file %s (%s): %s', $filename, $lineNo, $message);
	}

	/**
	 * Handles exceptions
	 * @param                \Exception $ex
	 * @return                void
	 * @api
	 */
	public static final function handleException (\Exception $ex) {
		if ($ex instanceof exception\IPrintableException) {
			if (static::$applicationManagerObj !== null) $ex->show (); else
				$ex->showMinimal ();

			exit;
		}

		print $ex;
		exit;
	}

	/**
	 * Forwardes normal method calls to component system
	 * @param                        string $methodName
	 * @param                        array  $arguments
	 * @throws                        SystemException
	 * @return                        mixed
	 * @api
	 */
	public static function __callStatic ($methodName, $arguments) {
		// support for components
		if (substr ($methodName, 0, 3) == 'get') return static::getComponent (substr ($methodName, 3));

		// failed
		throw new SystemException("Method %s does not exist in class %s", $methodName, __CLASS__);
	}
}

// post includes
require_once (Ikarus::getPath () . 'lib/core.functions.php');
?>