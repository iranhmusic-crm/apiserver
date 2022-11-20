<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\classes\controller;

use yii\rest\Controller;

class BaseController extends Controller
{
	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
			'captcha' => [
				'class' => 'yii\captcha\CaptchaAction',
				'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
			],
		];
	}

}
