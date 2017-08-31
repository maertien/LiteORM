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
	 * @return array Variable names
	 */
	public function getAllVariables() {

		$variables = $this->reflector->getProperties();
		$result = array();

		foreach ($variables as $variable) {

			$result[] = $variable->getName();
		}

		return $result;
	}

	/**
	 * Set variable 
	 * @param string $name Name of variable
	 * @param mixed $value Value of variable
	 */
	public function setVariable($name, $value) {

		$property = $this->reflector->getProperty($name);
		$property->setAccessible(true);
		$property->setValue($this->src, $value);
	}

	/**
	 * Get variable value
	 * @param string $name Name of variable
	 * @return mixed Variable value
	 */
	public function getVariable($name) {

		$property = $this->reflector->getProperty($name);
		$property->setAccessible(true);
		return $property->getValue($this->src);
	}
}
