<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa;

use yii\base\BootstrapInterface;

class Module extends \yii\base\Module implements BootstrapInterface
{
	public function init()
	{
		parent::init();
		// $this->params['foo'] = 'bar';
	}

	public function bootstrap($app)
	{
		if ($app instanceof \yii\web\Application)
		{
			$rules = [
				[
					'class' => \yii\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/user'],
					'pluralize' => false,
					'extraPatterns' => [
						'POST signup' => 'signup',
						'POST login' => 'login',
						'GET,POST logout' => 'logout',
						'GET,POST whoami' => 'who-am-i',

						'POST login-by-mobile' => 'login-by-mobile',

						'POST request-approval-code' => 'request-approval-code',
						'POST accept-approval' => 'accept-approval',

						'POST request-forgot-password' => 'request-forgot-password',
						'POST password-reset-by-forgot-code' => 'password-reset-by-forgot-code',
						'POST password-reset' => 'password-reset',
						'POST password-change' => 'password-change',
					],
				],
				[
					'class' => \yii\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/role'],
					'pluralize' => false,
				],
			];

			$app->urlManager->addRules($rules, false);
		}
		elseif ($app instanceof \yii\console\Application)
		{
			//http://www.yiiframework.com/wiki/820/yii2-create-console-commands-inside-a-module-or-extension/
			$this->controllerNamespace = 'app\modules\aaa\commands';
			// $app->controllerMap['aaa'] = [
				// 'class' => 'shopack\app\commands\SmsController',
				// 'generators' => array_merge($this->coreGenerators(), $this->generators),
				// 'module' => $this,
			// ];
		}
	}

}
