<?php

return [
	'bootstrap' => [
		'aaa',
		'mha',
	],
	'modules' => [
		'aaa' => [
			'class' => \shopack\aaa\backend\Module::class,
		],
		'mha' => [
			'class' => \iranhmusic\shopack\mha\backend\Module::class,
			// 'components' => [
			// 	'db' => [
			// 		'class' => \yii\db\Connection::class,
			// 		'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=dbiranhmusic_fin',
			// 		'username' => 'root',
			// 		'password' => '111',

			// 		'enableSchemaCache' => YII_ENV_PROD,
			// 		'schemaCacheDuration' => 60, //3600; //seconds
			// 		'schemaCache' => 'cache',
			// 		'charset' => 'utf8mb4',
			// 		'tablePrefix' => 'tbl_',
			// 	],
			// ],
		],
	],
];
