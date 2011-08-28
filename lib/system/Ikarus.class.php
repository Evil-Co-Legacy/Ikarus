<?php
namespace ikarus\system;
use ikarus\pattern\Singleton;
use ikarus\system\configuration\Configuration;
use ikarus\system\database\DatabaseManager;
use ikarus\system\exception\SystemException;

// includes
require_once(IKARUS_DIR.'lib/core.defines.php');
require_once(IKARUS_DIR.'lib/pattern/Singleton.class.php');

/**
 * Manages all core instances
 * @author		Johannes Donath
 * @copyright		2011 DEVel Fusion
 * @package		com.develfusion.ikarus
 * @subpackage		system
 * @category		Ikarus Framework
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version		2.0.0-0001
 */
class Ikarus extends Singleton {
	
	/**
	 * Contains the name of the file wich contains the core configuration
	 * @var		string
	 */
	const CONFIGURATION_FILE = 'options.inc.php';
	
	/**
	 * Contains an instance of ApplicationManager
	 * @var		ApplicationManager
	 */
	protected static $applicationManagerObj = null;
	
	/**
	 * Contains an instance of Configuration
	 * @var		Configuration
	 */
	protected static $configurationObj = null;
	
	/**
	 * Contains an instance of CacheManager
	 * @var		CacheManager
	 */
	protected static $cacheManagerObj = null;
	
	/**
	 * Contains an instance of DatabaseManager
	 * @var		DatabaseManager
	 */
	protected static $databaseManagerObj = null;
	
	/**
	 * Contains an instance of EventManager
	 * @var		EventManager
	 */
	protected static $eventManagerObj = null;
	
	/**
	 * Contains an instance of ExtensionManager
	 * @var		ExtensionManager
	 */
	protected static $extensionManagerObj = null;
	
	/**
	 * Starts all core instances
	 * @return		void
	 */
	public final function init() {
		static::initDatabaseManager();
		static::initCacheManager();
		static::initEventManager();
		static::initApplicationManager();
		static::initConfiguration();
		static::initExtensionManager();
		
		static::$applicationManagerObj->boot();
	}
	
	/**
	 * Returns the current ApplicationManager instance
	 * @return		ApplicationManager
	 */
	public static final function getApplicationManager() {
		return static::$applicationManagerObj;
	}
	
	/**
	 * Returns the current Configuration instance
	 * @return		Configuration
	 */
	public static final function getConfiguration() {
		return static::$configurationObj;
	}
	
	/**
	 * Returns the current CacheManager instance
	 * @return		CacheManager
	 */
	public static final function getCacheManagger() {
		return static::$cacheManagerObj;
	}
	
	/**
	 * Returns the current DatabaseManager instance
	 * @return		DatabaseManager
	 */
	public static final function getDatabaseManager() {
		return static::$databaseManagerObj;
	}
	
	/**
	 * Returns the current EventManager instance
	 * @return		ExtensionManager
	 */
	public static final function getEventManager() {
		return static::$eventManagerObj;
	}
	
	/**
	 * Returns the current ExtensionManager instance
	 * @return		ExtensionManager
	 */
	public static final function getExtensionManager() {
		return static::$extensionManagerObj;
	}
	
	/**
	 * Starts the application manager instance
	 */
	protected final function initApplicationManager() {
		static::$applicationManagerObj = new ApplicationManager();
	}
	
	/**
	 * Starts the cache manager instance
	 * @return		void
	 */
	protected final function initCacheManager() {
		static::$cacheManagerObj = new CacheManager();
		
		static::$cacheManagerObj->startDefaultAdapter();
	}
	
	/**
	 * Starts the configuration instance
	 * @return		void
	 */
	protected final function initConfiguration() {
		static::$configurationObj = new Configuration(IKARUS_DIR.static::CONFIGURATION_FILE);
	}
	
	/**
	 * Starts the database manager instance
	 * @return		void
	 */
	protected final function initDatabaseManager() {
		static::$databaseManagerObj = new DatabaseManager();
		
		static::$databaseManagerObj->startDefaultAdapter();
	}
	
	/**
	 * Starts the event manager instance
	 * @return		void
	 */
	protected final function initEventManager() {
		static::$eventManagerObj = new EventManager();
	}
	
	/**
	 * Starts the extension manager instance
	 * @return		void
	 */
	protected final function initExtensionManager() {
		static::$extensionManagerObj = new ExtensionManager();
	}
	
	/**
	 * Autoloads missing classes
	 * @param		string			$className
	 * @return		void
	 */
	public static function autoload($className) {
		// split namespaces
		$namespaces = explode('\\', $className);
		
		// autoloading inside of our application requires namespaces
		if (count($namespaces) > 1) {
			// get application prefix from namespace
			$applicationPrefix = array_shift($namespaces);
			
			// check for registered applications
			if ($applicationPrefix == 'ikarus') { // FIXME: This should not be hardcoded
				// generate class path
				$classPath = IKARUS_DIR.'lib/'.implode('/', $namespaces).'.class.php';
				
				// include needed file
				if (file_exists($classPath)) {
					require_once($classPath);
					return;
				}
			} elseif (static::$applicationManagerObj->applicationPrefixExists($applicationPrefix)) {
				// generate class path
				$classPath = static::$applicationManagerObj->getApplication($applicationPrefix)->getLibraryPath().implode('/', $namespaces) . '.class.php';
				
				// include needed file
				if (file_exists($classPath)) require_once($classPath);
			}
		}
	}
	
	/**
	 * Handles application errors
	 * @param		integer			$errorNo
	 * @param		string			$message
	 * @param		string			$filename
	 * @param		integer			$lineNo
	 * @throws		SystemException
	 * @return		void
	 */
	public static final function handleError($errorNo, $message, $filename, $lineNo) {
		if (error_reporting() != 0) {
			$type = 'error';
			switch ($errorNo) {
				case 2: $type = 'warning'; break;
				case 8: $type = 'notice'; break;
			}
			
			throw new SystemException('PHP '.$type.' in file %s (%s): %s', $filename, $lineNo, $message);
		}
	}
	
	/**
	 * Handles exceptions
	 * @param		\Exception			$ex
	 * @return		void
	 */
	public static final function handleException(\Exception $ex) {
		if ($ex instanceof exception\IPrintableException) {
			if (static::$applicationManagerObj !== null)
				$ex->show();
			else
				$ex->showMinimal();
				
			exit;
		}
		
		print $ex;
		exit;
	}
	
	/**
	 * Forwardes normal method calls to our static methods (Needed for template engine)
	 * @param		string			$methodName
	 * @param		array			$arguments
	 * @throws		SystemException
	 */
	public function __call($methodName, $arguments) {
		// call static method if exists
		if (method_exists(__CLASS__, $methodName)) call_user_func_array(array(__CLASS__, $methodName), $arguments);
		
		// failed
		throw new SystemException("Method %s does not exist in class %s", $methodName, __CLASS__);
	}
}

// post includes
require_once(IKARUS_DIR.'lib/core.functions.php');
?>