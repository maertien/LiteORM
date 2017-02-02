<?php

/**
 * LiteORM - Written by Martin Kumst
 * NO WARRANTY !!!
 * License: GNU GPL version 2
 */

define("LITEORM_VERSION", "0.0.3");

/**
 * LiteORMException
 */
class LiteORMException extends Exception {
	
}

/**
 * LiteORMConnector
 */
class LiteORMConnector {
	
	private $pdo;
	private $statement;
	private static $instance = null;

	/**
	 * Returns singleton instance of LiteORMConnector
	 * @return object LiteORMConnector instance
	 */
	public static function getInstance() {

		if (is_null(self::$instance)) {

			self::$instance = new LiteORMConnector(LITEORM_DB_FILE);
		}

		return self::$instance;
	}


	/**
	 * Constructs a connection to the specified database file
	 * @param string $file Filename containing DB
	 */
	public function __construct($file) {
		
		try {
			
			$this->pdo = new PDO("sqlite:" . $file);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (Exception $e) {
			
			throw new LiteORMException("Unable to connect to or create database file", 1, $e);
		}
	}

	/**
	 * Prepare statement
	 * @param string $sql SQL command
	 */
	public function prepare($sql) {
		$this->statement = $this->pdo->prepare($sql);
	}

	/**
	 * Bind value into prepared statement
	 * @param string $name Name of the placeholder
	 * @param mixed $value Value of the placeholder
	 */
	public function bindVal($name, $value) {

		$type = gettype($value);
		$typeDB = PDO::PARAM_STR;
		switch ($type) {
			case "integer":
				$typeDB = PDO::PARAM_INT;
				break;
			default:
				$typeDB = PDO::PARAM_STR;	
		}

		$this->statement->bindValue($name, $value, $typeDB);
	}

	/**
	 * Execute prepared statement
	 */
	public function execute() {
		$this->statement->execute();
	}

	/**
	 * Fetch all data from the statement
	 * @return mixed Data
	 */
	public function fetchAll() {
		return $this->statement->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Begin DB transaction
	 */
	public function begin() {
		return $this->pdo->beginTransaction();
	}
	
	/**
	 * Commit DB transaction
	 */
	public function commit() {
		return $this->pdo->commit();
	}
	
	/**
	 * Get last inserted ID
	 * @return int Last ID
	 */
	public function getLastID() {
		return $this->pdo->lastInsertId();
	}
}

/**
 * LiteORMDataObject
 */
abstract class LiteORMDataObject {
	
	private $vals;
	private $connector;
	
	/**
	 * Constructs new data object
	 * @param array|int|bool $vals Array with column names and their values or integer ID or boolean (false means do nothing)
	 */
	public function __construct($vals) {

		$this->connector = LiteORMConnector::getInstance();
		
		if (is_array($vals)) {
			$this->vals = $vals;
		}
		elseif (is_numeric($vals)) {
			$this->vals["id"] = $vals;
			$this->load();
		}
		elseif (is_bool($vals) && $vals === false) {
			;
		}
	}
	
	/**
	 * Get all instances
	 */
	public static function getAll() {

		$connector = LiteORMConnector::getInstance();
		$result = array();
		
		$tableName = get_called_class();
		$sql = "select id from " . $tableName;
		
		$connector->prepare($sql);
		$connector->execute();
		$ids = $connector->fetchAll(); 
		foreach ($ids as $id) {
			$result[] = new $tableName($id["id"], $connector);
		}
		
		return $result;
	}
	
	/**
	 * Find all objects by supplied conditions
	 * @param array $conds DB conditions
	 * @return array Objects
	 */
	public static function find($conds) {

		$connector = LiteORMConnector::getInstance();
		$result = array();
		$tableName = get_called_class();
		
		$sql = "select id from " . $tableName . " where ";
		
		$whereConds = array();
		foreach ($conds as $key => $val) {
		
			$whereConds[] = $key . " = :" . $key;	
		}
		
		$sql .= implode(", ", $whereConds);
		
		$connector->prepare($sql);
		foreach ($conds as $key => $val) {
			$connector->bindVal(":" . $key, $val);
		}
		$connector->execute();
		
		$rows = $connector->fetchAll();
		
		foreach ($rows as $row) {
			
			$result[] = new $tableName($row["id"], $connector);
		}
		
		return $result;
	}

	/**
	 * Load object data from database
	 */
	private function load() {
		$tableName = get_class($this);
		$sql = "select * from " . $tableName . " where id = :id";
		$this->connector->prepare($sql);
		$this->connector->bindVal(":id", $this->vals["id"]);
		$this->connector->execute();
		
		$result = $this->connector->fetchAll();
		
		foreach ($result[0] as $columnName => $value) {
			if (! isset($this->vals[$columnName])) {
				$this->vals[$columnName] = $value;
			}
		}
	}
	
	/**
	 * Delete object
	 */
	public function delete() {
		$tableName = get_class($this);
		$sql = "delete from " . $tableName . " where id = :id";
		$this->connector->prepare($sql);
		$this->connector->bindVal(":id", $this->vals["id"]);
		$this->connector->execute();
	}
	
	/**
	 * Delete all objects
	 */
	public function deleteAll() {
		$tableName = get_class($this);
		$this->connector->prepare("delete from " . $tableName);
		$this->connector->execute();
	}

	/**
	 * Save object into database
	 */
	public function save() {
		$tableName = get_class($this);
		
		if (! isset($this->vals["id"])) {
			
			$this->insert();
		}
		else {
		
			$sql = "update " . $tableName . " set ";	

			$updateParams = array();
			foreach ($this->vals as $key => $value) {
				$updateParams[] = $key . " = :" . $key;
			}

			$sql .= implode(", ", $updateParams);
			$sql .= " where id = :id";

			$this->connector->prepare($sql);

			foreach ($this->vals as $key=> $value) {
				$this->connector->bindVal(":" . $key, $value);
			}

			$this->connector->execute();
		}
	}

	/**
	 * Insert object into database
	 */
	private function insert() {

		$valsDB = array();
		foreach ($this->vals as $key => $value) {
			$valsDB[] = ":" . $key;
		}

		$tableName = get_class($this);
		$sql = "insert into " . $tableName . " (id, ";
		$sql .= implode(", " , array_keys($this->vals));
		$sql .= ") values(null, ";
		$sql .= implode(", ", $valsDB);
		$sql .= ");";
		
		$this->connector->begin();
		$this->connector->prepare($sql);
		
		foreach ($this->vals as $key => $value) {
			$this->connector->bindVal($key, $value);
		}

		$this->connector->execute();
		$newID = $this->connector->getLastID();
		$this->connector->commit();
		
		$this->vals["id"] = $newID;
		
		return $newID;
	}

	/**
	 * Create table with the structure based on object values
	 */
	public function createTable() {
		$tableName = get_class($this);
		$sql = "CREATE TABLE " . $tableName . " (id integer primary key autoincrement";

		foreach ($this->vals as $key => $value) {
			
			$type = "text";
			switch (gettype($value)) {
				case "integer":
					$type = "integer";
					break;
				default:
					$type = "text";
			}
			$sql .= ", " . $key . " " . $type;
		}

		$sql .= ");";

		$this->connector->prepare($sql);
		$this->connector->execute();
	}
	
	/**
	 * Get value
	 * @param string $name Name of value
	 * @return mixed Value
	 */
	public function get($name) {
		
		if (isset($this->vals[$name])) {
			return $this->vals[$name];
		}
		else {
		
			throw new LiteORMException("There is no such value name - " . $name, 2);
		}
	}
	
	/**
	 * Set value
	 * @param string $name Name of value
	 * @param mixed $value Value
	 */
	public function set($name, $value) {		
		$this->vals[$name] = $value;
	}
}
