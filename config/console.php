<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
	'id' => 'basic-console',
	'basePath' => dirname(__DIR__),
	'bootstrap' => [
		'log',
		'aaa',
		'fin',
		'mha',
	],
	'controllerNamespace' => 'app\commands',
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
		'@tests' => '@app/tests',
	],
	'modules' => [
		'aaa' => [
			'class' => 'app\modules\aaa\Module',
		],
		'fin' => [
			'class' => 'app\modules\fin\Module',
		],
		'mha' => [
			'class' => 'app\modules\mha\Module',
		],
	],
	'components' => [
		'cache' => [
			'class' => 'yii\caching\FileCache',
		],
		'log' => [
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'db' => $db,
	],
	'params' => $params,
	'controllerMap' => [
		// 'fixture' => [ // Fixture generation command line.
		// 	'class' => 'yii\faker\FixtureController',
		// ],
		// 'migrationNamespaces' => [
		// 	'yii\queue\db\migrations',
		// ],
		'migrate' => [
			'class' => 'yii\console\controllers\MigrateController',
			'migrationPath' => [
				// '@yii/rbac/migrations',
				// '@yii/web/migrations',
				'@app/migrations',
				'@app/modules/aaa/migrations',
				'@app/modules/fin/migrations',
				'@app/modules/mha/migrations',
				// '@yii/../yii2-queue/src/drivers/db/migrations',
			],
		],
		//'migrationPath' => null, // allows to disable not namespaced migration completely
	],
];

if (YII_ENV_DEV) {
	// configuration adjustments for 'dev' environment
	$config['bootstrap'][] = 'gii';
	$config['modules']['gii'] = [
		'class' => 'yii\gii\Module',
	];
}

return $config;
