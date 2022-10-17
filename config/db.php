<?php

return [
	'class' => 'yii\db\Connection',
	'dsn' => 'mysql:host=localhost;port=3306;dbname=iranhmusic_yii',
	'username' => 'root',
	'password' => '111',

	'enableSchemaCache' => YII_ENV_PROD,
	'schemaCacheDuration' => 60, //3600; //seconds
	'schemaCache' => 'cache',
	'charset' => 'utf8mb4',
	'tablePrefix' => 'tbl_',

	// Schema cache options (for production environment)
	//'enableSchemaCache' => true,
	//'schemaCacheDuration' => 60,
	//'schemaCache' => 'cache',
];
