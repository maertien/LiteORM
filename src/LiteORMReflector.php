<?php

/**
 * LiteORMReflector
 */
class LiteORMReflector {

	private $src;
	private $reflector;

	/**
	 * Constructs LiteORMReflector
	 * @param object $srcObject Source object for reflection
	 */
	public function __construct($srcObject) {

		if (is_object($srcObject) === true) {

			$this->src = $srcObject;
			$this->reflector = new ReflectionClass($this->src);
		}
		else {

			throw new LiteORMException("Source object not found");
		}
	}

	/**
	 * Get all source object variables
	 */
	public function getAllVariables() {

		$result = $this->reflector->getProperties();
		return $result;
	}

	public function setVariable($name, $value) {

	}

	public function getVariable($name) {

	}
}
