<?php

spl_autoload_register(function($class)
{
    $class = str_replace('Sharksmedia\\QueryBuilder\\', '', $class);
	$fileName = str_replace('\\', '/', $class).'.php';

    if(!is_file($fileName)) throw new \Exception('File not found: '.$fileName);
	
	if(is_file($fileName)) require($fileName);
});

use Sharksmedia\QueryBuilder\Config;
use Sharksmedia\QueryBuilder\QueryBuilder;
use Sharksmedia\QueryBuilder\QueryCompiler;
use Sharksmedia\QueryBuilder\Client\MySQL;
use Sharksmedia\QueryBuilder\Statement\Raw;

$iConfig = new Config('mysql');

$iClient = MySQL::create($iConfig);

function raw(string $query, ...$bindings)
{
    global $iClient;

    $iRaw = new Raw($iClient);
    $iRaw->set($query, $bindings);

    return $iRaw;
}

function query(?string $tableName=null)
{
    global $iClient;
    $iQueryBuilder = new QueryBuilder($iClient, 'my_schema');
    if($tableName !== null) $iQueryBuilder->table($tableName);

    return $iQueryBuilder;
}

$iQB = query('customers')
    ->select('id', 'name');

$iQueryBuilder = query()
    ->column('id', 'customer_id')
    ->column('name')->as('name_a')
    ->column(raw('SUM(price)'))->as('name_b')
    // ->select()
    ->from('users')
    ->where('id', '=', 1)
    ->andWhere(['id'=>2, 'name'=>'bob'])
    ->orWhere(function($q)
    {
        $q->where('id', '=', 3);
        $q->andWhere('id', '=', 4);
        // $q->andWhere('id', '=', 4);
    })
    ->andWhereNotIn('id', $iQB);

$iQueryCompiler = new QueryCompiler($iClient, $iQueryBuilder, []);

// print $iQueryCompiler->valuesClause([1, 2]).PHP_EOL;
// print $iQueryCompiler->valuesClause([[1, 2], [3, 4]]).PHP_EOL;
// print $iQueryCompiler->valuesClause($iQueryBuilder).PHP_EOL;
// print $iQueryCompiler->valuesClause(raw('SELECT 1, 2')).PHP_EOL;

$query = $iQueryCompiler->select();

print $query;

//
// foreach($iQueryBuilder->getStatements() as $iStatement)
// {
//     if($iStatement->getClass() === 'Columns')
//     {
//         $bindings = [];
//         $result = $iQueryCompiler->compileColumns($iStatement, $bindings);
//
//         print $result;
//     }
// }
//
// print_r($iQueryBuilder);
// print_r($iQueryCompiler);
