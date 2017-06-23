# LiteORM
Lightweight, really simple and easy to use PHP ORM for SQLite3 

## License
GNU GPL version 2

## Author
Martin Kumst - http://kumst.net

## Howto use LiteORM
```php
<?php

// Load LiteORM classes as simple as 
require_once "../src/LiteORM.php";

// Specify database filename
define("LITEORM_DB_FILE", "./test.sqlite");

// Create database structure as simple as
class Man extends LiteORMDataObject {
}
$man = new Man(array("age" => 20, "name" => "Martin"));
$man->createTable();

// Insert object
$man->save();

// Get some property value
echo $man->get("age");

// Modify and save object
$man->set("age", 22);
$man->save();

// For more examples please take a look at tests/test_sqlite.php file
```
