<?php

return [
	'enablePrettyUrl' => true,
	'enableStrictParsing' => true,
	'showScriptName' => false,
	'rules' => [
		[
			'class' => \yii\rest\UrlRule::class,
			'controller' => ['user'],
			'pluralize' => false,
			'extraPatterns' => [
				'POST signup' => 'signup',
				'POST login' => 'login',
				'GET,POST logout' => 'logout',
				'GET,POST whoAmI' => 'whoAmI',
				'POST requestForgotPassword' => 'requestForgotPassword',
				'POST changePassword' => 'changePassword',
			],
		],
	],
];
