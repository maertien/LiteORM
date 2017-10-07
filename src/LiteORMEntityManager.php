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

		LiteORMValidationHelper::isEntityTypeValid($entityType);

		if (is_numeric($conditions) === true) {

			return $this->getInstanceById($entityType, $conditions);
		}
		
	}

	/**
	 * Get all entities by selector and compare by comparator
	 * @param string $entityType Entity type
	 * @param mixed $selector Selector function
	 * @param mixed $comparator Comparator function
	 */
	public function getBySelector($entityType, $selector, $comparator = null) {

		LiteORMValidationHelper::isEntityTypeValid($entityType);

		$entities = $this->getAll($entityType);

		// Fire selector method on each entity
		$result = array();
		foreach ($entities as $entity) {

			if ($selector($entity) === true) {

				$result[] = $entity;
			}			
		}

		// Compare the result
		if ($comparator !== null) {

			usort($result, $comparator);
		}

		return $result;
	}

	/**
	 * Get all entities
	 * @param string $entityType Entity type
	 * @return array Array of entities
	 */
	public function getAll($entityType) {

		LiteORMValidationHelper::isEntityTypeValid($entityType);
		
		$result = array();
		
		$sql = "select id from " . $entityType;
		
		$this->connector->prepare($sql);
		$this->connector->execute();
		$ids = $this->connector->fetchAll(); 
		foreach ($ids as $id) {

			$result[] = $this->getInstanceById($entityType, $id["id"]);
		}
		
		return $result;
	}

	/**
	 * Get all entities (alias for getAll method)
	 * @param string $entityType Entity type
	 * @return array Array of entities
	 */
	public function findAll($entityType) {

		return $this->getAll($entityType);
	}

	/**
	 * Delete entity
	 * @param object $entity Entity
	 */
	public function delete($entity) {

		$reflector = new LiteORMReflector($entity);
		$id = $reflector->getVariable("id");
		$tableName = get_class($entity);

		$sql = "delete from " . $tableName . " where id = :id";
		$this->connector->prepare($sql);
		$this->connector->bindVal(":id", $id);
		$this->connector->execute();
	}

	/**
	 * Save entity
	 * @param object $entity Entity
	 */
	public function save($entity) {

	}

	/**
	 * Get entity by id 
	 * @param string $entityType Entity type
	 * @param int $id Entity id
	 */
	private function getInstanceById($entityType, $id) {

		LiteORMValidationHelper::isEntityTypeValid($entityType);

		$entity = new $entityType();
		$reflector = new LiteORMReflector($entity);

		$sql = "select * from " . $entityType . " where id = :id";
		$this->connector->prepare($sql);
		$this->connector->bindVal(":id", $id);
		$this->connector->execute();
		
		$result = $this->connector->fetchAll();

		if (! isset($result[0])) {

			throw new LiteORMException("There is no object with id " . $this->vals["id"]);
		}

		foreach ($result[0] as $columnName => $value) {

			$reflector->setVariable($columnName, $value);
		}

		return $entity;
	}
}

