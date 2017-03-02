<?php

require_once "../src/LiteORM.php";

define("LITEORM_DB_FILE", "./test.sqlite");

class A extends LiteORMDataObject {
}

// Create table
//$a = new A(array("x" => 1, "y" => "abc"));
//var_dump($a->createTable());

// Insert data into table
/*
for ($i = 0; $i < 1000; $i++) {
	$b = new A(array("x" => $i, "y" => "test"));
	$b->save();
}
*/

// Select by selector
$result = A::getBySelector(function ($o) {if ($o->get("x") > 670) return true;});
foreach ($result as $res) {echo $res->get("x") . " ";};
echo "\n";

// Select by selector and compare by comparator
$result = A::getBySelector(function ($o) { if ($o->get("x") > 670) return true;}, function ($a, $b) {
	$aX = $a->get("x"); $bX = $b->get("x");
	if ($aX === $bX) return 0;
	return ($aX > $bX) ? -1 : 1;
});
foreach ($result as $res) {echo $res->get("x") . " ";};
echo "\n";
