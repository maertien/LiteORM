<?php

/**
 * LiteORMSQLiteConnector
 */
class LiteORMSQLiteConnector extends LiteORMBaseConnector {

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
}
