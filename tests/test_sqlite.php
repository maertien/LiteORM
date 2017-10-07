<?php

require_once "../src/LiteORM.php";

define("LITEORM_DB_FILE", "./test.sqlite");

class A {
	private $a;
	private $b;
}


$r = new LiteORMTableGenerator();
$r->generate('A');
die();



$r = new LiteORMReflector(new A());
var_dump($r->getAllVariables());


$em = new LiteORMEntityManager();

var_dump($em->getAll('A'));

die("KONEC\n");


// Delete test db file if it exists
if (file_exists(LITEORM_DB_FILE)) {
	unlink(LITEORM_DB_FILE);
}

// The first class
class A extends LiteORMDataObject {
}

// The second class
class B extends LiteORMDataObject {
}

// Create tables
echo "Creating tables\n";
$a = new A(array("x" => 1, "y" => "abc"));
$b = new B(array("y" => 2, "z" => "cde"));
$a->createTable();
$b->createTable();


// Insert data into tables
echo "Inserting data\n";
for ($i = 0; $i < 100; $i++) {
	$c = new A(array("x" => $i, "y" => "test"));
	$c->save();
	$d = new B(array("y" => $i, "z" => "testB"));
	$d->save();
}

// Select by selector
echo "Selecting data\n";
$result = A::getBySelector(function ($o) {if ($o->get("x") > 90) return true;});
foreach ($result as $res) {echo $res->get("x") . " ";};
echo "\n";

// Select by selector and compare by comparator
echo "Selecting and comparing data\n";
$result = A::getBySelector(function ($o) { if ($o->get("x") > 90) return true;}, function ($a, $b) {
	$aX = $a->get("x"); $bX = $b->get("x");
	if ($aX === $bX) return 0;
	return ($aX > $bX) ? -1 : 1;
});
foreach ($result as $res) {echo $res->get("x") . " ";};
echo "\n";
