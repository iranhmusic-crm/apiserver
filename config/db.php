<?php

return [
	'class' => 'yii\db\Connection',

	// 'schemaMap' => [
	// 	'mysql' => SamIT\Yii2\MariaDb\Schema::class
	// ],

	'dsn' => 'mysql:host=localhost;port=3306;dbname=db',
	'username' => 'must be define in local file',
	'password' => 'must be define in local file',

	'enableSchemaCache' => true,
	'schemaCacheDuration' => 3600, //seconds
	'schemaCache' => 'cache',
	'charset' => 'utf8mb4',
	'tablePrefix' => 'tbl_',
];
