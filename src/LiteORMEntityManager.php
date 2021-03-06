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
	 * @param mixed $conditions Conditions (array) or id (numeric)
	 * @param array $binds Binded values
	 */
	public function find($entityType, $conditions, $binds = null) {

		LiteORMValidationHelper::isEntityTypeValid($entityType);

		// Numeric conditions means get instance by id
		if (is_numeric($conditions) === true) {

			return $this->getInstanceById($entityType, $conditions);
		}
		elseif (is_array($conditions) === true) { // Array with conditions

			return $this->getInstancesByConditions($entityType, $conditions, $binds);
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

		$reflector = new LiteOrmReflector($entity);
		$id = $reflector->getVariable("id");
		$tableName = get_class($entity);
		
		if (is_numeric($id) === false) {
			// No id set, insert
			return $this->insert($entity);
		}
		else {
			// Id is set, update
			return $this->update($entity);
		}
	}

	/**
	 * Begin transaction
	 */
	public function beginTransaction() {

		$this->connector->begin();
	}

	/**
	 * Commit transaction
	 */
	public function commitTransaction() {

		$this->connector->commit();
	}

	/**
	 * Rollback transaction
	 */
	public function rollbackTransaction() {

		$this->connector->rollback();
	}

	/**
	 * Insert new entity into database
	 * @param object $entity Entity
	 */
	private function insert($entity) {

		$tableName = get_class($entity);
		$reflector = new LiteORMReflector($entity);
		$variables = $reflector->getAllVariables();

		foreach ($variables as $key => $var) {

			if ($var === "id") {

				unset($variables[$key]);
			}
		}		


		$valsDB = array();
		foreach ($variables as $varName) {

			if ($varName === "id") {

				continue;
			}

			$valsDB[] = ":" . $varName;
		}

		$sql = "insert into " . $tableName . " (id, ";
		$sql .= implode(", ", $variables);
		$sql .= ") values(null, ";
		$sql .= implode(", ", $valsDB);
		$sql .= ");";

		// $this->connector->begin();
		$this->connector->prepare($sql);
		foreach ($variables as $varName) {
			$this->connector->bindVal(":" . $varName, $reflector->getVariable($varName));
		}
		$this->connector->execute();
		$newID = $this->connector->getLastID();
		// $this->connector->commit();

		$reflector->setVariable("id", $newID);

		return $newID;
	}

	/**
	 * Update existing entity in database
	 * @param object $entity Entity
	 */
	private function update($entity) {

		$tableName = get_class($entity);
		$reflector = new LiteORMReflector($entity);		
		$variables = $reflector->getAllVariables();

		$sql = "update " . $tableName . " set ";

		$updateParams = array();
		foreach ($variables as $varName) {

			if ($varName === "id") {

				continue;
			}

			$updateParams[] = $varName . " = :" . $varName;
		}

		$sql .= implode(", ", $updateParams);
		$sql .= " where id = :id";

		$this->connector->prepare($sql);

		foreach ($variables as $varName) {

			$this->connector->bindVal(":" . $varName, $reflector->getVariable($varName));
		}
		
		$this->connector->execute();
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

			// throw new LiteORMException("There is no object with id " . $id);
			return null;
		}

		foreach ($result[0] as $columnName => $value) {

			$reflector->setVariable($columnName, $value);
		}

		return $entity;
	}

	/**
	 * Select instances by conditions
	 * @param string $entityType Entity type
	 * @param array $conditions Conditions
	 * @param array $binds Binded values
	 */
	private function getInstancesByConditions($entityType, $conditions, $binds) {

		LiteORMValidationHelper::isEntityTypeValid($entityType);

		if (is_array($conditions) !== true) {

			throw new LiteORMException("Invalid conditions");
		}

		if (is_array($binds) !== true) {

			throw new LiteORMException("Invalid binds");
		}

		if (count($conditions) === 0) {
			
			throw new LiteORMException("There is no condition");
		}

		$sql = "select * from " . $entityType . " where " . implode(" and ", $conditions);
		$this->connector->prepare($sql);
	
		foreach ($binds as $bindName => $bindVal) {

			$this->connector->bindVal($bindName, $bindVal);
		}

		$this->connector->execute();
		$result = $this->connector->fetchAll();

		if (! isset($result[0])) {

			return null;
		}

		$resultItems = array();

		foreach ($result as $resItem) {

			$entity = new $entityType();
			$reflector = new LiteORMReflector($entity);

			foreach ($resItem as $columnName => $value) {

				$reflector->setVariable($columnName, $value);
			}

			$resultItems[] = $entity;
		}

		return $resultItems;
	}
}

