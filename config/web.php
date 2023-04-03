<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$modules = require __DIR__ . '/modules.php';

use \yii\web\Request;
$baseUrl = str_replace('/web', '', (new Request)->getBaseUrl());
$baseUrl = rtrim($baseUrl, '/') . '/';

$config = [
	'id' => 'apiserver',
	'basePath' => dirname(__DIR__),
	'homeUrl' => $baseUrl,
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
	],
	'bootstrap' => array_merge([
		'log',
	], $modules['bootstrap']),
	'modules' => $modules['modules'],
	'components' => [
		'request' => [
			'class' => \shopack\base\common\web\Request::class,
			// !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
			'cookieValidationKey' => 'Fsr4FMUPthoCEWKtAV7Jo0BLiXoiP3Dn',
			'parsers' => [
				'application/json' => 'yii\web\JsonParser',
			],
			'baseUrl' => $baseUrl,
		],
		'response' => [
			'format' => yii\web\Response::FORMAT_JSON,
			'charset' => 'UTF-8',
		],
		'cache' => [
			'class' => \yii\caching\FileCache::class,
		],
		'user' => [
			'class' => \shopack\aaa\backend\components\User::class,
			'enableAutoLogin' => true,
			'enableSession' => false,
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
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
		'urlManager' => [
			'cache' => (YII_DEBUG ? false : 'cache'),
			'enablePrettyUrl' => true,
			'enableStrictParsing' => true,
			'showScriptName' => false,
			'baseUrl' => $baseUrl,
		],
		'jwt' => [
			'class' => \shopack\base\backend\auth\Jwt::class,
			'signer' => \bizley\jwt\Jwt::HS512,
			'signingKey' => 'fDcXlBvkO9ND9UvhszmW4elXl2EehtpM',
			// 'ttl' => 24 * 3600, //24 hours
		],
	],
	'params' => $params,
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
		// uncomment the following to add your IP if you are not connecting from localhost.
		//'allowedIPs' => ['127.0.0.1', '::1'],
	];
	*/
}

return $config;
