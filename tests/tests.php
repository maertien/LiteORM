<?php

require_once "../src/LiteORM.php";
define("LITEORM_DB_FILE", "./test.sqlite");

// Test LiteORM

// Entity class
class E {
	private $id;
	private $a;

	public function getA() {
		return $this->a;
	}

	public function setA($a) {
		$this->a = $a;
	}
}

// Generate table structure
try {
	$gen = new LiteORMTableGenerator();
	$gen->generate("E");
	echo "E table should be generated\n";
}
catch (Exception $e) {
	echo "ERROR: " . $e->getMessage() . "\n";
}

// Create new entity
try {
	$em = new LiteORMEntityManager();
	$e = new E();
	$e->setA("AAA");
	$em->save($e);
	echo "New E entity should be saved\n";
}
catch (Exception $e) {
	echo "ERROR: " . $e->getMessage() . "\n";
}


// Insert into database

// Load entity from database

// Update entity

// Delete entity
