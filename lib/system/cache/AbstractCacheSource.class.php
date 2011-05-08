<?php
namespace ikarus\system\cache;
use ikarus\system\cache\CacheSource;
use ikarus\system\event\EventHandler;

/**
 * @author		Johannes Donath
 * @copyright		2010 DEVel Fusion
 * @package		com.develfusion.ikarus
 * @subpackage		system
 * @category		Ikarus Framework
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version		1.0.0-0001
 */
class AbstractCacheSource implements CacheSource {

	/**
	 * @see CacheSource::close()
	 */
	public function close() { }

	/**
	 * @see lib/system/cache/CacheSource::enable()
	 */
	public function enable() {
		// fire event
		// EventHandler::fire($this, 'enable');
	}

	/**
	 * Fires the enabled event
	 */
	public function enabled() {
		// fire event
		// EventHandler::fire($this, 'enabled');
	}

	/**
	 * @see lib/system/cache/CacheSource::get()
	 */
	function get($cacheFile, $cacheBuilderPath, $minLifetime, $maxLifetime) {
		return null;
	}

	/**
	 * @see lib/system/cache/CacheSource::isSupported()
	 */
	public function isSupported() {
		return true;
	}
}
?>