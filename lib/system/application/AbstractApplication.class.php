<?php
namespace ikarus\system\application;
use ikarus\system\exception\StrictStandardException;

use ikarus\system\Ikarus;

/**
 * Implements needed default methods for application cores
 * @author		Johannes Donath
 * @copyright		2011 Evil-Co.de
 * @package		de.ikarus-framework.core
 * @subpackage		system
 * @category		Ikarus Framework
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version		2.0.0-0001
 */
abstract class AbstractApplication implements IApplication {
	
	/**
	 * Contains the abbreviation for this application
	 * @var			string
	 */
	protected $abbreviation = '';
	
	/**
	 * Contains all application components
	 * @var			array
	 */
	protected $components = array();
	
	/**
	 * Contains the choosen environment
	 * @var			string
	 */
	protected $environment = '';
	
	/**
	 * Contains an absolute library path for this application
	 * @var			string
	 */
	protected $libraryPath = '';
	
	/**
	 * Contains the packageID of this application
	 * @var			integer
	 */
	protected $packageID = 0;
	
	/**
	 * Contains true if this application is the primary application of this framework instance
	 * @var			boolean
	 */
	protected $primaryApplication = false;
	
	/**
	 * @see ikarus\system\application.IApplication::__construct()
	 */
	public function __construct($abbreviation, $libraryPath, $packageID, $environment, $primaryApplication = false) {
		$this->abbreviation = $abbreviation;
		$this->libraryPath = $libraryPath;
		$this->packageID = $packageID;
		$this->environment = $environment;
		$this->primaryApplication = $primaryApplication;
		
		Ikarus::getEventManager()->fire($this, 'initFinished');
	}
	
	/**
	 * @see ikarus\system\application.IApplication::boot()
	 */
	public function boot() {
		Ikarus::getEventManager()->fire($this, 'boot');
		
		$this->registerDefaultCacheResources();
		$this->registerDefaultComponents();
	}
	
	/**
	 * @see ikarus\system\application.IApplication::getEnvironment()
	 */
	public function getEnvironment() {
		return $this->environment;
	}
	
	/**
	 * @see ikarus\system\application.IApplication::getLibraryPath()
	 */
	public function getLibraryPath() {
		return $this->libraryPath;
	}
	
	/**
	 * @see ikarus\system\application.IApplication::getPackageID()
	 */
	public function getPackageID() {
		return $this->packageID;
	}
	
	/**
	 * @see ikarus\system\application.IApplication::isPrimaryApplication()
	 */
	public function isPrimaryApplication() {
		return $this->primaryApplication;
	}
	
	/**
	 * Registers all default cache resources for this application
	 * @return			void
	 */
	protected function registerDefaultCacheResources() { }
	
	/**
	 * Registers all default components for this application
	 * @return			void
	 */
	protected function registerDefaultComponents() { }
	
	/**
	 * @see ikarus\system\application.IApplication::shutdown()
	 */
	public function shutdown() {
		Ikarus::getEventManager()->fire($this, 'shutdown');
	}
}
?>