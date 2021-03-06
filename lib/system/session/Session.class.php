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
namespace ikarus\system\session;

use ikarus\data\DatabaseObject;
use ikarus\system\event\session\InitEvent;
use ikarus\system\event\session\InitFinishedEvent;
use ikarus\system\event\session\SessionEventArguments;
use ikarus\system\event\session\UpdateEvent;
use ikarus\system\event\session\UpdateFinishedEvent;
use ikarus\system\Ikarus;
use ikarus\util\StringUtil;

/**
 * @author                    Johannes Donath
 * @copyright                 2011 Evil-Co.de
 * @package                   de.ikarus-framework.core
 * @subpackage                system
 * @category                  Ikarus Framework
 * @license                   GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version                   2.0.0-0001
 */
class Session extends DatabaseObject implements ISession {

	/**
	 * Stores a list of built-in variables which are read-only from external sources.
	 * @var                        string[]
	 */
	protected $builtInVariables = array();

	/**
	 * @see ikarus\system\session.ISession::__construct()
	 */
	public function __construct ($data) {
		parent::__construct ($data);

		$this->init ();
	}

	/**
	 * Reads default information of session
	 * @return                        void
	 */
	protected function init () {
		// fire event
		$event = new InitEvent(new SessionEventArguments($this));
		Ikarus::getEventManager ()->fire ($event);

		// cancellable event
		if ($event->isCancelled ()) return;

		// detect default information
		$this->data['userAgent'] = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null);
		$this->data['acceptLanguage'] = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null);
		$this->data['requestMethod'] = (isset($_SERVER['REQUEST_METHOD']) ? StringUtil::toUpperCase ($_SERVER['REQUEST_METHOD']) : 'GET');
		$this->data['requestURI'] = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null);
		$this->data['ipAddress'] = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
		$this->data['hostname'] = (isset($_SERVER['REMOTE_ADDR']) ? gethostbyaddr ($_SERVER['REMOTE_ADDR']) : null);
		$this->data['user'] = null;

		// register built-in variables
		$this->registerBuiltInVariable (array('userAgent', 'acceptLanguage', 'requestMethod', 'requestURI', 'ipAddress', 'hostname'));

		// fire event
		Ikarus::getEventManager ()->fire (new InitFinishedEvent(new SessionEventArguments($this)));
	}

	/**
	 * @see \ikarus\system\session\ISession::isReadOnly()
	 */
	public function isReadOnly ($variableName) {
		return (in_array ($variableName, $this->builtInVariables));
	}

	/**
	 * Registers a built-in variable which is read-only to external calls.
	 * @param                        string $variableName
	 * @return                        void
	 */
	protected function registerBuiltInVariable ($variableName) {
		if (is_array ($variableName)) {
			foreach ($variableName as $variable) $this->registerBuiltInVariable ($variable);

			return;
		}

		$this->builtInVariables[] = $variableName;
	}

	/**
	 * @see \ikarus\system\session\ISession::setVariable()
	 */
	public function setVariable ($variableName, $variableValue) {
		// read only variables
		if ($this->isReadOnly ($variableName)) throw new StrictStandardException('Cannot set value of read-only variable "%s"', $variableName);

		$this->data[$variableName] = $variableValue;
	}

	/**
	 * @see ikarus\system\session.ISession::update()
	 */
	public function update () {
		// fire event
		$event = new UpdateEvent(new SessionEventArguments($this));
		Ikarus::getEventManager ()->fire ($event);

		// cancellable event
		if ($event->isCancelled ()) return;

		// update native stored information
		$sql = "UPDATE
				ikarus" . IKARUS_N . "_session
			SET
				userID = ?,
				ipAddress = ?,
				userAgent = ?,
				packageID = ?,
				environment = ?,
				abbreviation = ?
			WHERE
				sessionID = ?";
		$stmt = Ikarus::getDatabaseManager ()->getDefaultAdapter ()->prepareStatement ($sql);
		$stmt->bind (($this->user !== null ? $this->user->getUserID () : null));
		$stmt->bind ($this->ipAddress);
		$stmt->bind ($this->userAgent);
		$stmt->bind ($this->packageID);
		$stmt->bind ($this->environment);
		$stmt->bind ($this->abbreviation);
		$stmt->bind ($this->sessionID);
		$stmt->execute ();

		// update row
		$sql = "UPDATE
				ikarus" . IKARUS_N . "_session
			SET
				sessionData = ?
			WHERE
				sessionID = ?
			AND
				packageID = ?";
		$stmt = Ikarus::getDatabaseManager ()->getDefaultAdapter ()->prepareStatement ($sql);
		$stmt->bind (serialize ($this));
		$stmt->bind ($this->sessionID);
		$stmt->bind ($this->packageID);
		$stmt->execute ();

		// fire event
		Ikarus::getEventManager ()->fire (new UpdateFinishedEvent(new SessionEventArguments($this)));
	}
}

?>