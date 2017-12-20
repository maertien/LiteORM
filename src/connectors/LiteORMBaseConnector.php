<?php

/**
 * LiteORMBaseConnector
 */
abstract class LiteORMBaseConnector {
	
	protected $pdo;
	protected $statement;
	protected static $instance = null;
	protected $transactionInProgress = false;

	public static abstract function getInstance();
	public abstract function __construct($params);

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

		if ($this->pdo->inTransaction() !== true) {

			return $this->pdo->beginTransaction();
		}
	}
	
	/**
	 * Commit DB transaction
	 */
	public function commit() {

		if ($this->pdo->inTransaction() === true) {

			return $this->pdo->commit();
		}
	}

	/**
	 * Rollback DB transaction
	 */
	public function rollback() {

		if ($this->pdo->inTransaction() === true) {
	
			return $this->pdo->rollBack();
		}
	}
	
	/**
	 * Get last inserted ID
	 * @return int Last ID
	 */
	public function getLastID() {
		return $this->pdo->lastInsertId();
	}
}

