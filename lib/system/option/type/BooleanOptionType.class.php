<?php
namespace ikarus\system\option\type;

/**
 * Option type for boolean values (1 or 0)
 * @author		Johannes Donath
 * @copyright		2011 DEVel Fusion
 * @package		com.develfusion.ikarus
 * @subpackage		system
 * @category		Ikarus Framework
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version		1.0.0-0001
 */
class BooleanOptionType implements OptionType {

	/**
	 * @see OptionType::formatOptionValue()
	 */
	public static function formatOptionValue($value) {
		return (intval($value) ? 'true' : 'false');
	}
}
?>