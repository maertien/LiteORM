<?php

require_once "../src/LiteORM.php";

define("LITEORM_DB_FILE", "./test.sqlite");

class A extends LiteORMDataObject {
}

// Create table
$a = new A(array("x" => 1, "y" => "abc"));
var_dump($a->createTable());
