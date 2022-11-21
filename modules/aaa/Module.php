<?php
namespace app\modules\aaa;

use Yii;
use yii\db\Query;
use yii\base\BootstrapInterface;
use shopack\base\helpers\Url;
use shopack\base\helpers\Html;
use shopack\base\helpers\ArrayHelper;
use shopack\base\helpers\FileHelper;
use shopack\base\db\ActiveRecord; //shopack\multilanguage\db\ActiveRecord;
use shopack\base\interfaces\ShopackModuleInterface;
use shopack\app\models\AppConfigModel;
use shopack\app\models\UserModel;
use shopack\app\classes\jobs\ProcessSmsJob;
use shopack\app\classes\jobs\DeleteOldUserHistoryJob;

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
						'GET,POST whoAmI' => 'whoAmI',

						'POST resend-approval-code' => 'resend-approval-code',
						'POST accept-approval' => 'accept-approval',

						'POST requestForgotPassword' => 'requestForgotPassword',
						'POST changePassword' => 'changePassword',
					],
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
