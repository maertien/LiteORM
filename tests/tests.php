<?php

require_once "../src/LiteORM.php";
define("LITEORM_DB_FILE", "./test.sqlite");

// Remove test file if exists
if (file_exists(LITEORM_DB_FILE) === true) {

	unlink(LITEORM_DB_FILE);
}

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
	echo "Benchmark started\n";
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

// Transactions
try {
	echo "Testing insert with transaction ... ";
	$em = new LiteORMEntityManager();
	$e = new E();
	$e->setA("Testing entity");
	$em->beginTransaction();
	$em->save($e);
	$em->commitTransaction();
	echo "OK\n";
}
catch (Exception $e) {
	$em->rollbackTransaction();
	echo "ERROR: " . $e->getMessage() . "\n";
}

// Benchmark with transaction
try {
	echo "Benchmark using just one transaction\n";
	$start = microtime(true);
	$em = new LiteORMEntityManager();
	$em->beginTransaction();
	for($i = 0; $i < 100; $i++) {
		$e = new E();
		$e->setA("element " . $i);
		$em->save($e);
	}
	$em->commitTransaction();
	$stop = microtime(true);
	$elapsed = sprintf("%.2f", $stop - $start);
	echo "Benchmark: " . $elapsed . " s\n";
}
catch (Exception $e) {
	$em->rollbackTransaction();
	echo "ERROR: " . $e->getMessage() . "\n";
}

// Find by conditons
try {
	echo "Testing selection with conditions\n";
	$em = new LiteORMEntityManager();

	$ids = array();
	$es = array();
	for ($i = 0; $i < 5; $i++) {
		$e = new E();
		$e->setA($i);
		$ids[] = $em->save($e);
		$es[] = $e;
	}

	$idBinds = array(":id1" => $ids[0], ":id2" => $ids[1], ":id3" => $ids[2]);
	$recs = $em->find("E", array("id IN(:id1, :id2, :id3)"), $idBinds);
	if ($es[0] == $recs[0] && $es[1] == $recs[1] && $es[2] == $recs[2]) {
		echo "OK\n";
	}
	else {
		throw new Exception("Resultset contains invalid data");
	}
}
catch (Exception $e) {
	echo "ERROR: " . $e->getMessage() . "\n";
}
