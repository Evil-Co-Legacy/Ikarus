<?php
/**
 * This file is part of the Ikarus Framework.
 *
 * The Ikarus Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Ikarus Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with the Ikarus Framework. If not, see <http://www.gnu.org/licenses/>.
 */
namespace ikarus\system\io\archive\tar;
use ikarus\system\io\archive\ArchiveContent;

/**
 * Represents a file stored in a tar archive.
 * @author		Johannes Donath
 * @copyright		© Copyright 2012 Evil-Co.de <http://www.evil-co.com>
 * @package		de.ikarus-framework.core
 * @subpackage		system
 * @category		Ikarus Framework
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version		2.0.0-0001
 */
class TarArchiveContent implements ArchiveContent {
	
	/**
	 * Stores an archive instance.
	 * @var			Tar
	 */
	protected $archive = null;
	
	/**
	 * Stores a conntent's checksum.
	 * @var			integer
	 */
	protected $checksum = 0;
	
	/**
	 * Stores a content's filename.
	 * @var			string
	 */
	protected $filename = '';
	
	/**
	 * Stores a content's original group ID.
	 * @var			string
	 */
	protected $gid = '';
	
	/**
	 * Stores a content's target *NIX filemode.
	 * @var			integer
	 */
	protected $mode = 0755;
	
	/**
	 * Stores a content's modification time.
	 * @var			integer
	 */
	protected $modificationTime = 0;
	
	/**
	 * Stores the offset of this content in it's archive file.
	 * @var			integer
	 */
	protected $offset = 0;
	
	/**
	 * Stores a content's prefix (it's path).
	 * @var			string
	 */
	protected $prefix = '/';
	
	/**
	 * Stores a content's size (in bytes).
	 * @var			integer
	 */
	protected $size = 0;
	
	/**
	 * Stores a content's type (directory or file).
	 * @var			integer
	 */
	protected $type = 0;
	
	/**
	 * Stores a content's original user ID.
	 * @var			string
	 */
	protected $uid = '';
	
	/**
	 * Constructs the object.
	 * @param			Tar			$archive
	 */
	public function __construct(Tar $archive) {
		$this->archive = $archive;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::extract()
	 */
	public function extract($filePath) {
		$this->archive->extractFile($this, $filePath);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::extractContent()
	 */
	public function extractContent() {
		return $this->archive->extractContent($this);
	}
	
	/**
	 * Returns the parent archive instance.
	 * @return \ikarus\system\io\archive\tar\Tar
	 */
	public function getArchive() {
		return $this->archive;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::getChecksum()
	 * @return			integer
	 */
	public function getChecksum() {
		return $this->checksum;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::getFilename()
	 */
	public function getFilename() {
		return $this->filename;
	}
	
	/**
	 * Alias for \ikarus\system\io\archive\tar\Tar::getGroupID()
	 * @see \ikarus\system\io\archive\tar\Tar::getGroupID()
	 */
	public function getGID() {
		return $this->getGroupID();
	}
	
	/**
	 * Returns a content's original group ID.
	 * @return			string
	 */
	public function getGroupID() {
		return $this->gid;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::getMode()
	 */
	public function getMode() {
		return $this->mode;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::getPrefix()
	 */
	public function getPrefix() {
		return $this->prefix;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::getSize()
	 */
	public function getSize() {
		return $this->size;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::getType()
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Alias for \ikarus\system\io\archive\tar\Tar::getUserID()
	 * @see \ikarus\system\io\archive\tar\Tar::getUserID()
	 */
	public function getUID() {
		return $this->getUserID();
	}
	
	/**
	 * Returns a content's original user ID.
	 * @return			string
	 */
	public function getUserID() {
		return $this->uid;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::setChecksum()
	 */
	public function setChecksum($checksum) {
		$this->checksum = $checksum;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::setFilename()
	 */
	public function setFilename($filename) {
		$this->filename = $filename;
	}
	
	/**
	 * Alias for \ikarus\system\io\archive\tar\Tar::setGroupID()
	 * @see \ikarus\system\io\archive\tar\Tar::setGroupID()
	 */
	public function setGID($gid) {
		$this->setGroupID($gid);
	}
	
	/**
	 * Sets a content's original group ID.
	 * @param			string			$groupID
	 * @return			void
	 */
	public function setGroupID($groupID) {
		$this->guid = $groupID;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::setMode()
	 */
	public function setMode($mode = 0755) {
		$this->mode = $mode;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::setPrefix()
	 */
	public function setPrefix($prefix = '/') {
		$this->prefix = $prefix;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::setSize()
	 */
	public function setSize($size = 0) {
		$this->size = $size;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ikarus\system\io\archive\ArchiveContent::setType()
	 */
	public function setType($type) {
		$this->type = $type;
	}
	
	/**
	 * Alias for \ikarus\system\io\archive\tar\Tar::setUserID()
	 * @see \ikarus\system\io\archive\tar\Tar::setUserID()
	 */
	public function setUID($uid) {
		$this->setUserID($uid);
	}
	
	/**
	 * Sets a content's original user ID.
	 * @param			string			$userID
	 * @return			void
	 */
	public function setUserID($userID) {
		$this->userID = $userID;
	}
}
?>