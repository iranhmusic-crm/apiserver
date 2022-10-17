<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class UserController extends Controller
{
	/**
	 * {@inheritdoc}
	 */
	public function behaviors()
	{
		return [
			// 'access' => [
			//   'class' => AccessControl::class,
			//   'only' => ['logout'],
			//   'rules' => [
			//     [
			//       'actions' => ['logout'],
			//       'allow' => true,
			//       'roles' => ['@'],
			//     ],
			//   ],
			// ],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'login' => ['post'],
					'logout' => ['get', 'post'],
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
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

	public function actionLogin()
	{
		$model = new LoginForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("Username and Password not provided");

		if ($model->login() == false)
			throw new NotFoundHttpException(implode("\n", $model->getFirstErrors()));

		//-- create token -----
		$token = Yii::$app->jwt->getBuilderWithConf()
			->identifiedBy(Yii::$app->session->id)	// Configures the id (jti claim)
			->withClaim(Yii::$app->jwt->claim_UserID, $model->user->usrID)
			->withClaim(Yii::$app->jwt->claim_UserFirstName, $model->user->usrFirstName)
			->withClaim(Yii::$app->jwt->claim_UserLastName, $model->user->usrLastName)
			->getToken(
				Yii::$app->jwt->getConfiguration()->signer(),
				Yii::$app->jwt->getConfiguration()->signingKey()
			)
			->toString();

		return [
			'token' => $token,
		];
	}

}
