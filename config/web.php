<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
	'id' => 'basic',
	'basePath' => dirname(__DIR__),
	'bootstrap' => [
		'log',
		'aaa',
		'fin',
		'mha',
	],
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
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
		'request' => [
			// !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
			'cookieValidationKey' => 'Fsr4FMUPthoCEWKtAV7Jo0BLiXoiP3Dn',
			'parsers' => [
				'application/json' => 'yii\web\JsonParser',
			],
		],
		'response' => [
			'format' => yii\web\Response::FORMAT_JSON,
			'charset' => 'UTF-8',
		],
		'cache' => [
			'class' => \yii\caching\FileCache::class,
		],
		'user' => [
			'class' => \app\modules\aaa\components\User::class,
			'enableAutoLogin' => true,
			'enableSession' => false,
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
		'mailer' => [
			'class' => \yii\symfonymailer\Mailer::class,
			'viewPath' => '@app/mail',
			// send all mails to a file by default.
			'useFileTransport' => true,
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'db' => $db,
		'urlManager' => [
			'enablePrettyUrl' => true,
			'enableStrictParsing' => true,
			'showScriptName' => false,
		],
		'jwt' => [
			'class' => \app\classes\auth\Jwt::class,
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
		//'allowedIPs' => ['127.0.0.1', '::1'],
	];

	$config['bootstrap'][] = 'gii';
	$config['modules']['gii'] = [
		'class' => 'yii\gii\Module',
		// uncomment the following to add your IP if you are not connecting from localhost.
		//'allowedIPs' => ['127.0.0.1', '::1'],
	];
}

return $config;
