<?php

/**
 * LiteORMTableGenerator
 */
class LiteORMTableGenerator {

	private $connector;

	/**
	 * Constructs LiteORMTableGenerator
	 */
	public function __construct() {

		$this->connector = LiteORMConnector::getInstance();
	}
	
	/**
	 * Generate DB structure for entity type
	 * @param string $entityType Entity type
	 */
	public function generate($entityType) {

		LiteORMValidationHelper::isEntityTypeValid($entityType);

		$reflector = new LiteORMReflector(new $entityType());
		$variables = $reflector->getAllVariables();

		$sql = "CREATE TABLE IF NOT EXISTS " . $entityType . " (id integer primary key autoincrement";

		foreach ($variables as $variable) {

			$sql .= ", " . $variable . " " . "text";
		}

		$sql .= ");";

		$this->connector->prepare($sql);
		$this->connector->execute();
	}
}
