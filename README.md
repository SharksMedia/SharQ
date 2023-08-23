# SharQ
A query builder for PHP

### Usage
```php
<?php
// src/index.php

require_once __DIR__.'/../vendor/autoload.php';

use \Sharksmedia\SharQ\Config;
use \Sharksmedia\SharQ\Client;

use \Sharksmedia\SharQ\SharQ;

function getClient(): Client
{
    $iConfig = (new Config(Config::CLIENT_MYSQL))
        ->host('127.0.0.0')
        ->port(3306)
        ->user('user')
        ->password('password')
        ->database('main_db')
        ->charset('utf8mb4');

    $iClient = Client::create($iConfig);

    return $iClient;
}

function qb(?Client $iClient=null): SharQ
{
    $iClient = $iClient ?? getClient();

    $iSharQ = new SharQ($iClient);

    return $iSharQ;
}

// SELECT `name` FROM `users` WHERE `id` = ?
$usersQB = qb()->select('name')
    ->from('users')
    ->where('id', '=', 1);

// SELECT `name` FROM `users` WHERE `id` = ? OR `id` = ?
$usersQB->orWhere('id', 2);

// use ->run() function to execute the query
// $users = $usersQuery->run();

$iQuery = $usersQB->toQuery();

$sql = $iQuery->getSQL();
$bindings = $iQuery->getBindings();

print $sql.PHP_EOL;
print_r($bindings);
```

### Installation
Add Sharksmedia repository
```bash
composer config repositories.sharksmedia/sharq vcs git@github.com:SharksMedia/SharQ.git
```

Require SharQ
```bash
composer require sharksmedia/sharq:master
```

### Documentation
Create the documentation with phpDocumenter

```bash
composer run-script phpdoc
```

You can now open the documentation pages in docs/
