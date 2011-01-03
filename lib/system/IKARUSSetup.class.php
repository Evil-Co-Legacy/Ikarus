<?php
// ikarus imports
require_once(IKARUS_DIR.'lib/system/IKARUS.class.php');

/**
 * @author		Johannes Donath
 * @copyright		2010 DEVel Fusion
 * @package		com.develfusion.ikarus
 * @subpackage		system
 * @category		Ikarus Framework
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version		1.0.0-0001
 */
class IKARUSSetup extends IKARUS {

	/**
	 * @see IKARUS::ENVIRONMENT
	 */
	const ENVIRONMENT = 'setup';

	/**
	 * @see IKARUS::TEMPLATE_DIR
	 */
	const TEMPLATE_DIR = 'setup/templates/';
}
?>