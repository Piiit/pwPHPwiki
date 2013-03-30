<?php

class pwPattern {
	private $name;
	private $entryPattern;
	private $exitPattern;
	private $type;
	private $allowedModes = array(); //The token can be part of this modes.
	private $childModes = array(); //This list of modes can be inside this token.
	private $recursive = false;
	private $connectTo;
	private $restore;
	private $level;
	private $flags;
	private $cachedRegexp;
	private $cachedRegexpRenew = true;

	public function __construct($name, $type, $entryPattern = null,	$exitPattern = null) {
		$this->setName($name);
		$this->setEntryPattern($entryPattern);
		$this->setExitPattern($exitPattern);
		$this->setType($type);
	}



	public function __toString() {
		return "TOKEN: name=" . $this->getName() . "; entry="
				. $this->getEntryPattern() . "; exit="
						. $this->getExitPattern() . "; type=" . $this->getType();
	}

	public function getName() {
		return $this->name;
	}

	public function getAllowedModes() {
		return $this->allowedModes;
	}

	public function setAllowedModes($modes, $initself = false) {
		if (!is_array($modes)) {
			throw new InvalidArgumentException(
					"Tried to set modes for '" . $this->getName()
					. "', but 'modes' must be an array of nodeNames (string)");
		}

		foreach ($modes as $mode) {
			if (!is_string($mode)) {
				throw new InvalidArgumentException(
						"Tried to set modes for '" . $this->getName()
						. "', but 'modes' must be an array of nodeNames (string). This 'mode' is of type "
						. gettype($mode));
			}

			// Possible Deadlock! Don't add yourself, except it is explicitly said!
			if ($mode != $this->name or $initself) {
				if (empty($this->allowedModes)
						or !in_array($mode, $this->allowedModes)) {
					array_push($this->allowedModes, $mode);
				}
			}
		}
	}

	public function getRecursive() {
		return $this->recursive;
	}

	public function setRecursive($recursive) {
		$this->recursive = $recursive;
	}

	public function getConnectTo() {
		return $this->connectTo;
	}

	public function setConnectTo($connectTo) {
		$this->connectTo = $connectTo;
	}

	public function getType() {
		return $this->type;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getEntryPattern() {
		return $this->entryPattern;
	}

	public function setEntryPattern($entryPattern) {
		$this->entryPattern = $entryPattern;
	}

	public function getExitPattern() {
		return $this->exitPattern;
	}

	public function setExitPattern($exitPattern) {
		$this->exitPattern = $exitPattern;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function getRestore() {
		return $this->restore;
	}

	public function setRestore($restore) {
		$this->restore = $restore;
	}

	public function getLevel() {
		return $this->level;
	}

	public function setLevel($level) {
		$this->level = $level;
	}

	public function getFlags() {
		return $this->flags;
	}

	public function setFlags($flags) {
		$this->flags = $flags;
	}

	public function addMode($mode, $order = -1) {
		if (!is_string($mode)) {
			throw new InvalidArgumentException(
					"Tried to set modes for '" . $this->getName()
					. "', but 'mode' is not a string! (type = "
					. gettype($mode) . ")");
		}
		if ($order == -1)
			array_push($this->childModes, $mode);
		else
			$this->childModes[$order] = $mode;
	}

	public function setModes($modes) {
		$this->childModes = $modes;
	}

	public function getModes() {
		return $this->childModes;
	}

	public function getCachedRegexp() {
		return $this->cachedRegexp;
	}

	public function setCachedRegexp($cachedRegexp) {
		$this->cachedRegexp = $cachedRegexp;
	}

	public function getCachedRegexpRenew() {
		return $this->cachedRegexpRenew;
	}

	public function setCachedRegexpRenew($cachedRegexpRenew) {
		$this->cachedRegexpRenew = $cachedRegexpRenew;
	}

}

?>