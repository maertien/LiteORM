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
	$newID = $em->save($e);
	echo "New E entity should be saved - new id is " . $newID . "\n";
}
catch (Exception $e) {
	echo "ERROR: " . $e->getMessage() . "\n";
}

// Load entity from database
try {
	$em = new LiteORMEntityManager();
	$e = $em->find("E", $newID);
	echo "Entity with id " . $newID . " should be loaded\n";
	$aVal = $e->getA();
	if ($aVal === "AAA") {
		echo "A value is ok\n";
	}
	else {
		echo "A value is NOT ok\n";
	}
}
catch (Exception $e) {
	echo "ERROR: " . $e->getMessage() . "\n";
}

// Update entity
try {
	$em = new LiteORMEntityManager();
	$e->setA("BBB");
	$em->save($e);
	echo "Entity should be updated and will be checked A value\n";

	$ee = $em->find("E", $newID);
	$nVal = $ee->getA();
	if ($nVal === "BBB") {
		echo "New A value is ok\n";
	}
	else {
		echo "New A value is NOT ok\n";
	}	
}
catch (Exception $e) {
	echo "ERROR: " . $e->getMessage() . "\n";
}

// Delete entity
try {
	$em = new LiteORMEntityManager();
	$em->delete($ee);
	echo "Entity should be deleted\n";	
}
catch (Exception $e) {
	echo "ERROR: " . $e->getMessage() . "\n";
}
