<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$modules = require __DIR__ . '/modules.php';

$config = [
	'id' => 'apiserver',
	'basePath' => dirname(__DIR__),
	'bootstrap' => array_merge([
		'log',
	], $modules['bootstrap']),
	'controllerNamespace' => 'app\commands',
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
		'@tests' => '@app/tests',
	],
	'modules' => $modules['modules'],
	'components' => [
		'cache' => [
			'class' => 'yii\caching\FileCache',
		],
		'fileManager' => [
			'class' => \shopack\aaa\backend\components\FileManager::class,
		],
		'mailer' => [
			'class' => \yii\symfonymailer\Mailer::class,
			'viewPath' => '@app/mail',
			// send all mails to a file by default.
			'useFileTransport' => true,
		],
		'sms' => [
			'class' => \shopack\aaa\backend\components\Sms::class,
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 999 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'db' => $db,
		'i18n' => [
			'class' => \shopack\base\common\components\I18N::class,
		],
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
				// '@app/migrations',
				'@aaa/migrations',
				'@mha/migrations',
				// '@app/modules/mha/migrations',
				// '@yii/../yii2-queue/src/drivers/db/migrations',
			],
		],
		'migrate-aaa' => [
			'class' => 'yii\console\controllers\MigrateController',
			'migrationPath' => '@aaa/migrations',
		],
		'migrate-mha' => [
			'class' => 'yii\console\controllers\MigrateController',
			'migrationPath' => '@mha/migrations',
		],

		//'migrationPath' => null, // allows to disable not namespaced migration completely
	],
];

if (YII_ENV_DEV) {
	// configuration adjustments for 'dev' environment

	$config['bootstrap'][] = 'debug';
	$config['modules']['debug'] = [
		'class' => 'yii\debug\Module',
		// uncomment the following to add your IP if you are not connecting from localhost.
		'allowedIPs' => ['*'],
	];

	/*
	$config['bootstrap'][] = 'gii';
	$config['modules']['gii'] = [
		'class' => 'yii\gii\Module',
	];
	*/
}

return $config;
