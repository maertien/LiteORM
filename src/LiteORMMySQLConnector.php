<?php

/**
 * LiteORMMySQLConnector
 */
class LiteORMMySQLConnector extends LiteORMBaseConnector {

	/**
	 * Returns the singleton instance 
	 */
	public static function getInstance() {

		if (is_null(self::$instance)) {

			self::$instance = new LiteORMMySQLConnector(array(
				"host" => LITEORM_MYSQL_HOST,
				"port" => LITEORM_MYSQL_PORT,
				"user" => LITEORM_MYSQL_USER,
				"password" => LITEORM_MYSQL_PASSWORD,
				"dbname" => LITEORM_MYSQL_DBNAME
			));
		}

		return self::$instance;
	}

	/**
	 * Constructs the connection to the supplied database
	 * @param array $params Array of parameters
	 */
	public function __construct($params) {

		try {

			$this->pdo = new PDO("mysql:host=" . $params["host"] . ";port=" . $params["port"]  . ";dbname=" . $params["dbname"], $params["user"], $params["password"]);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (Exception $e) {
			
			throw new LiteORMException("Unable to connect to database", 1, $e);
		}
	}
}

