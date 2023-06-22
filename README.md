# QueryBuilder
A query builder for PHP

### Usage
```php
<?php

use \Sharksmedia\QueryBuilder\Config;
use \Sharksmedia\QueryBuilder\Client;

use \Sharksmedia\QueryBuilder\QueryBuilder;
use \Sharksmedia\QueryBuilder\QueryCompiler;

$iConfig = new Config('mysql');
$iClient = Client::createFromConfig($iConfig);

$iQueryBuilder = new QueryBuilder($iClient, 'my_schema');
$iQueryCompiler = new QueryCompiler($iClient, $iQueryBuilder);

$iQueryBuilder
    ->select('name')
    ->from('users')
    ->where('id', '=', 1);

$iQuery = $iQueryCompiler->toSQL();

$sql = $iQuery->getSQL(); // SELECT `name` FROM `users` WHERE `id` = ?
$bindings = $iQuery->getBindings(); // [1]

print $sql.PHP_EOL;
print_r($bindings);
```

### Installation
Add Sharksmedia repository
```bash
composer config repositories.sharksmedia/query-builder vcs git@github.com:SharkMagnus/QueryBuilder.git
```

Require the QueryBuilder
```bash
composer require sharksmedia/query-builder:master
```

### Documentation
Create the documentation with phpDocumenter

```bash
composer run-script phpdoc
```

You can now open the documentation pages in docs/
