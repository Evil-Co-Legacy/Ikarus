<?php
namespace ikarus\system\exception;

/**
 * The base class for all printable exceptions.
 * @author		Johannes Donath
 * @copyright		2011 Evil-Co.de
 * @package		de.ikarus-framework.core
 * @subpackage		system
 * @category		Ikarus Framework
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version		2.0.0-0001
 */
interface IPrintableException {

	/**
	 * Displays the exception
	 */
	public function show();
	
	/**
	 * Shows a minimal error message
	 */
	public function showMinimal();
}
?>