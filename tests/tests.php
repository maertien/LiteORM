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

	public function getId() {
		return $this->id;
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
	$id = $ee->getId();	
	$em = new LiteORMEntityManager();
	$em->delete($ee);
	echo "Entity should be deleted\n";	
	$eee = $em->find("E", $id);
	if ($eee === null) {
		echo "Entity does not exists - OK\n";
	}
	else {
		echo "Entity exists - BAD\n";
	}
}
catch (Exception $e) {
	echo "ERROR: " . $e->getMessage() . "\n";
}

// Benchmark
try {
	$start = microtime(true);
	$em = new LiteORMEntityManager();
	for($i = 0; $i < 100; $i++) {
		$e = new E();
		$e->setA("element " . $i);
		$em->save($e);
	}
	$stop = microtime(true);
	$elapsed = sprintf("%.2f", $stop - $start);
	echo "Benchmark: " . $elapsed . " s\n";
}
catch (Exception $e) {
	echo "ERROR: " . $e->getMessage() . "\n";
}
