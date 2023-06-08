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
    $iRaw = new Raw($query, ...$bindings);

    return $iRaw;
}

function query(?string $tableName=null)
{
    global $iClient;
    $iQueryBuilder = new QueryBuilder($iClient, 'my_schema');
    if($tableName !== null) $iQueryBuilder->table($tableName);

    return $iQueryBuilder;
}

$languageQuery = query()->select('languages_id')->from('languages_to_stores')->whereColumn('stores_id', '=', 'cda.storeID');

$q1 = query()
    ->select(['cda.*', 'name'=>raw('pd.products_name COLLATE utf8mb4_unicode_ci')])
    ->from(['cda'=>'ContentDrugApprovals'])
    ->join(raw('products_to_stores AS pts'), function($q)
    {
        $q->on('cda.entityID', '=', 'pts.products_id')
          ->andOn('cda.storeID', '=', 'pts.stores_id');
        })
    ->join(raw('products_description AS pd'), function($q) use($languageQuery)
    {
        $q->on('cda.entityID', '=', 'pd.products_id')
          ->andOn('pd.language_id', '=', $languageQuery);
    })
    ->where('cda.entityType', '=', 'product');


$q2 = query()
    ->select(['cda.*', 'name'=>'al.name'])
    ->from(['cda'=>'ContentDrugApprovals'])
    ->join(raw('ArticleLocalizations AS al'), function($q) use($languageQuery)
    {
        $q->on('cda.entityID', '=', 'al.articleID')
          ->andOn('al.languageID', '=', $languageQuery);
    })
    ->join(raw('Articles AS a'), 'a.articleID', '=', 'al.articleID')
    ->join(raw('ContentBlocks AS cb'), function($q)
    {
        $q->on('cda.entityID', '=', 'cb.entityID')
          ->andOn('cb.contentBlockTypeID', '=', query()->select('contentBlockTypeID')->from('ContentBlockTypes')->where('name', '=', 'article'));
    })
    ->join(raw('ContentBlockSources AS cbs'), function($q)
    {
        $q->on('cb.contentBlockID', '=', 'cbs.contentBlockID')
          ->andOn('cbs.languageID', '=', 'al.languageID');
        })
    ->where('cda.entityType', '=', 'article')
    ->andWhere('cbs.sourceLongtext', '!=', '')
    ->andWhere('cbs.sourceLongtext', '!=', '[]')
    ->andWhere('al.enabled', '=', 1)
    ->andWhere('a.deleted', '=', 0);


$query = query()
    ->select('*')
    ->from(function($q) use($q1, $q2)
    {
        $q->union($q1->as('q1'), $q2->as('q2'))
          ->as('T');
    })
    ->where('approved', '=', 1);


$iQueryCompiler = new QueryCompiler($iClient, $query, []);

$query = $iQueryCompiler->toSQL();

print $query->getSQL().PHP_EOL;
print_r($query->getBindings());
