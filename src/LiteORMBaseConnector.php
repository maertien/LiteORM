<?php

/**
 * LiteORMBaseConnector
 */
abstract class LiteORMBaseConnector {
	
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

