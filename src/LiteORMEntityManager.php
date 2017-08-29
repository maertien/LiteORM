<?php

/**
 * LiteORMEntityManager
 */
class LiteORMEntityManager {

	private $connector;

	/**
	 * Construct LiteORMEntityManager
	 */
	public function __construct() {
		
		$this->connector = LiteORMConnector::getInstance();
	}

	/**
	 * Find entities by conditions
	 * @param string $entityType Entity type
	 * @param mixed $conditions Conditions
	 */
	public function find($entityType, $conditions) {

		$this->validateEntityType();

		if (is_numeric($conditions) == true) {

			return $this->getInstanceById($entityType, $conditions);
		}
	}

	public function getBySelector($entityType, $selector, $comparator = null) {

	}

	/**
	 * Get all entities
	 * @param string $entityType Entity type
	 * @return array Array of entities
	 */
	public function getAll($entityType) {

		$this->validateEntityType($entityType);
		
		$result = array();
		
		$sql = "select id from " . $entityType;
		
		$this->connector->prepare($sql);
		$this->connector->execute();
		$ids = $this->connector->fetchAll(); 
		foreach ($ids as $id) {

			$result[] = $id["id"];
		}
		
		return $result;
	}

	public function delete($entity) {

	}

	public function save($entity) {

	}

	/**
	 * Validate entity type
	 * @param string $entityType Entity type
	 * @return bool True if entity type is valid
	 * @throws Exception
	 */
	private function validateEntityType($entityType) {

		if (class_exists($entityType) !== true) {

			throw new LiteORMException("Entity type " . $entityType . " is invalid");
		}

		return true;
	}

	/**
	 * Get entity by id 
	 * @param string $entityType Entity type
	 * @param int $id Entity id
	 */
	private function getInstanceById($entityType, $id) {

	}
}

class LiteORM extends LiteORMEntityManager {
}
