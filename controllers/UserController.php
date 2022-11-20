<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UnauthorizedHttpException;
use app\classes\controller\BaseRestController;
use app\classes\helpers\AuthHelper;
use app\classes\helpers\PrivHelper;
use app\models\AAA\UserModel;
use app\models\AAA\SignupForm;
use app\models\AAA\LoginForm;

class UserController extends BaseRestController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[BaseRestController::BEHAVIOR_AUTHENTICATOR]['optional'] = [
		  'signup',
		];

		// $behaviors[BaseRestController::BEHAVIOR_AUTHENTICATOR]['only'] = [
		// ];

		$behaviors[BaseRestController::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'login',
		];

		// $behaviors['verbs'] = [
		// 	'class' => VerbFilter::class,
		// 	'actions' => [
		// 		'login' => ['post'],
		// 		'logout' => ['get', 'post'],
		// 	],
		// ];

		return $behaviors;
	}

	// 'GET,HEAD  users'      => 'user/index'   : return a list/overview/options of users
	// 'GET,HEAD  users/<id>' => 'user/view'    : return the details/overview/options of a user
	// 'POST      users'      => 'user/create'  : create a new user
	// 'PUT,PATCH users/<id>' => 'user/update'  : update a user
	// 'DELETE    users/<id>' => 'user/delete'  : delete a user
	// '          users/<id>' => 'user/options' : process all unhandled verbs of a user
	// '          users'      => 'user/options' : process all unhandled verbs of user collection

	protected function findModel($id)
	{
		if (($model = UserModel::findOne($id)) !== null)
			return $model;

		throw new NotFoundHttpException('The requested page item not exist.');
	}

	public function actionIndex()
	{
		$filter = [];
		if (PrivHelper::hasPriv('user/crud', '0100') == false)
			$filter = ['usrID' => Yii::$app->user->identity->usrID];

		$query = UserModel::find();
		if (empty($filter) == false)
			$query->where($filter);

		return $query->all();
	}

	public function actionView($id)
	{
		if (PrivHelper::hasPriv('user/crud', '0100') == false) {
			if (Yii::$app->user->identity->usrID != $id)
				throw new ForbiddenHttpException('access denied');
		}

		return $this->findModel($id);
	}

	public function actionCreate()
	{
		PrivHelper::checkPriv('user/crud', '1000');

		$model = new UserModel();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => [
				'message' => 'created',
				'usrID' => $model->usrID,
			],
		];
	}

	public function actionUpdate($id)
	{
		if (PrivHelper::hasPriv('user/crud', '0010') == false) {
			if (Yii::$app->user->identity->usrID != $id)
				throw new ForbiddenHttpException('access denied');
		}

		$model = $this->findModel($id);
		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => [
				'message' => 'updated',
				'usrID' => $model->usrID,
			],
		];
	}

	public function actionDelete($id)
	{
		if (PrivHelper::hasPriv('user/crud', '0001') == false) {
			if (Yii::$app->user->identity->usrID != $id)
				throw new ForbiddenHttpException('access denied');
		}

		$model = $this->findModel($id);

		if ($model->delete() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => [
				'message' => 'deleted',
				'usrID' => $model->usrID,
			],
		];
	}

	public function actionOptions()
	{
		return 'options';
	}

	public function actionWhoAmI()
	{
		return [
			Yii::$app->user->identity,
			Yii::$app->user->accessToken->claims()->all(),
			Yii::$app->user->accessToken->toString(),
		];
	}

	public function actionSignup() {
		$model = new SignupForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->signup() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		//logout
		//-----------------------
		try {
			AuthHelper::logout();
		} catch (\Throwable $th) { ; }

		//login
		//-----------------------
		$token = AuthHelper::doLogin($model->user);

		return [
			'token' => $token,
		];
	}

	public function actionLogin()
	{
		$model = new LoginForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("Username and Password not provided");

		if ($model->login() == false)
			throw new UnauthorizedHttpException(implode("\n", $model->getFirstErrors()));

		//login
		//-----------------------
		$token = AuthHelper::doLogin($model->user);

		return [
			'token' => $token,
		];
	}

	public function actionLogout()
	{
		AuthHelper::logout();

		return [
			'result' => true,
		];
	}

	public function actionRequestEmailApproval()
	{
		// Yii::$app->user->identity->usrID

	}

	public function actionApproveEmail()
	{
		// Yii::$app->user->identity->usrID

	}

	public function actionRequestForgotPassword()
	{
		// Yii::$app->user->identity->usrID

	}

	public function actionChangePassword()
	{

	}

}
