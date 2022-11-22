<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\fin;

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
					'controller' => [$this->id . '/payment-gateway'],
					'pluralize' => false,
					'extraPatterns' => [
						'POST callback' => 'callback',
					],
				],
				[
					'class' => \yii\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/online-payment'],
					'pluralize' => false,
				],
				[
					'class' => \yii\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/voucher'],
					'pluralize' => false,
				],
			];

			$app->urlManager->addRules($rules, false);
		}
		elseif ($app instanceof \yii\console\Application)
		{
			//http://www.yiiframework.com/wiki/820/yii2-create-console-commands-inside-a-module-or-extension/
			$this->controllerNamespace = 'app\modules\fin\commands';
			// $app->controllerMap['fin'] = [
				// 'class' => 'shopack\app\commands\SmsController',
				// 'generators' => array_merge($this->coreGenerators(), $this->generators),
				// 'module' => $this,
			// ];
		}
	}

}
