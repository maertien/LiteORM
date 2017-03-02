# LiteORM
Lightweight, really simple and easy to use PHP ORM for SQLite3 and MySQL

## License
GNU GPL version 2

## Author
Martin Kumst - http://kumst.net

## Howto use LiteORM
```php
<?php

// In the file src/LiteORMConnector.php you can choose SQLite3 or MySQL connector by extending the class you want (LiteORMSQLiteConnector or LiteORMMySQLConnector) 

// For SQLite3 connector you should write this:
class LiteORMConnector extends LiteORMSQLiteConnector {
};

```
