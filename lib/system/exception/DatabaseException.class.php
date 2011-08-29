<?php
namespace ikarus\system\exception;
use ikarus\util\StringUtil;

/**
 * This exception will thrown if a database problem occoures
 * @author		Johannes Donath
 * @copyright		2011 DEVel Fusion
 * @package		com.develfusion.ikarus
 * @subpackage		system
 * @category		Ikarus Framework
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version		2.0.0-0001
 */
class DatabaseException extends SystemException {

	/**
	 * Contains the database driver where the error has occoured
	 * @var DatabaseDriver
	 */
	protected $databaseDriver = null;

	/**
	 * Contains the version of database server
	 * @var string
	 */
	protected $sqlVersion = null;

	/**
	 * Creates a new instance of DatabaseException
	 */
	public function __construct() {
		// validate arguments
		if (!func_num_args()) die("<strong>FATAL:</strong> Cannot display SystemException: Invalid arguments passed to system exception!");

		// get arguments (sorry for this shit but i would like a c like system exception ;-D)
		$arguments = func_get_args();

		// remove argument1 (DatabaseDriver)
		$this->databaseDriver = $arguments[0];
		unset($arguments[0]);

		// resort
		$arguments = array_merge(array(), $arguments);

		// call parent
		call_user_func_array(array('parent', '__construct'), $arguments);
		
		// modify information
		$this->modifyInformation();
	}

	/**
	 * Returns the sql type of the active database.
	 * @return	string
	 */
	public function getDatabaseType() {
		return get_class($this->databaseDriver);
	}

	/**
	 * Returns the error description of this exception.
	 * @return	string
	 */
	public function getErrorDesc() {
		return $this->databaseDriver->getErrorDescription();
	}

	/**
	 * Returns the error number of this exception.
	 * @return	integer
	 */
	public function getErrorNumber() {
		return $this->databaseDriver->getErrorNumber();
	}

	/**
	 * Returns the current sql version of the database.
	 * @return	string
	 */
	public function getSQLVersion() {
		// get version if not defined
		if (!$this->sqlVersion) $this->sqlVersion = $this->databaseDriver->getVersion();

		return $this->sqlVersion;
	}

	/**
	 * Modifies error information
	 * @return			void
	 */
	public function modifyInformation() {
		$this->information .= '<b>database driver:</b> ' . StringUtil::encodeHTML($this->getDatabaseType()) . '<br />';
		$this->information .= '<b>sql error:</b> ' . StringUtil::encodeHTML($this->getErrorDesc()) . '<br />';
		$this->information .= '<b>sql error number:</b> ' . StringUtil::encodeHTML($this->getErrorNumber()) . '<br />';
		$this->information .= '<b>sql version:</b> ' . StringUtil::encodeHTML($this->getSQLVersion()) . '<br />';
	}
}
?>