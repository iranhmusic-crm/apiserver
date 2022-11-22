<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use app\classes\controller\BaseRestController;
use app\classes\helpers\AuthHelper;
use app\classes\helpers\PrivHelper;
use app\modules\aaa\models\UserModel;
use app\modules\aaa\models\SignupForm;
use app\modules\aaa\models\LoginForm;
use app\modules\aaa\models\LoginByMobileForm;
use app\modules\aaa\models\ApproveCodeForm;
use app\modules\aaa\models\ApprovalRequestModel;
use app\modules\aaa\models\ForgotPasswordRequestModel;
use app\modules\aaa\models\PasswordResetByForgotCodeForm;
use app\modules\aaa\models\PasswordResetForm;
use app\modules\aaa\models\PasswordChangeForm;

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
			'login-by-mobile',
			'request-approval-code',
			'accept-approval',
			'request-forgot-password',
			'password-reset-by-forgot-code',
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

		throw new NotFoundHttpException('The requested item not exist.');
	}

	public function actionIndex()
	{
		$filter = [];
		if (PrivHelper::hasPriv('aaa/user/crud', '0100') == false)
			$filter = ['usrID' => Yii::$app->user->identity->usrID];

		$query = UserModel::find();
		if (empty($filter) == false)
			$query->where($filter);

		return $query->all();
	}

	public function actionView($id)
	{
		if (PrivHelper::hasPriv('aaa/user/crud', '0100') == false) {
			if (Yii::$app->user->identity->usrID != $id)
				throw new ForbiddenHttpException('access denied');
		}

		return $this->findModel($id);
	}

	public function actionCreate()
	{
		PrivHelper::checkPriv('aaa/user/crud', '1000');

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
		if (PrivHelper::hasPriv('aaa/user/crud', '0010') == false) {
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
		if (PrivHelper::hasPriv('aaa/user/crud', '0001') == false) {
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
		list ($token, $mustApprove) = AuthHelper::doLogin($model->user);

		return [
			'token' => $token,
			'mustApprove' => $mustApprove,
		];
	}

	public function actionLogin()
	{
		$model = new LoginForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("Username and Password not provided");

		list ($token, $mustApprove) = $model->login();

		return [
			'token' => $token,
			'mustApprove' => $mustApprove,
		];
	}

	public function actionLogout()
	{
		AuthHelper::logout();

		return [
			'result' => true,
		];
	}

	/**
	 * input
	 */
	public function actionRequestApprovalCode()
	{
		$bodyParams = Yii::$app->request->getBodyParams();

		if (empty($bodyParams['input']))
			throw new NotFoundHttpException("parameters not provided");

		return [
			'result' => ApprovalRequestModel::requestCode($bodyParams['input']),
		];
	}

	/**
	 * input
	 * code
	 */
	public function actionAcceptApproval()
	{
		$model = new ApproveCodeForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->approve() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => true,
		];
	}

	/**
	 * input
	 */
	public function actionRequestForgotPassword()
	{
		$bodyParams = Yii::$app->request->getBodyParams();

		if (empty($bodyParams['input']))
			throw new NotFoundHttpException("parameters not provided");

		return [
			'result' => ForgotPasswordRequestModel::requestCode($bodyParams['input']),
		];
	}

	/**
	 * input
	 * code
	 * newPassword
	 */
	public function actionPasswordResetByForgotCode()
	{
		$model = new PasswordResetByForgotCodeForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => true,
		];
	}

	/**
	 * userID
	 * newPassword
	 */
	public function actionPasswordReset()
	{
		PrivHelper::checkPriv('aaa/user/passwordReset');

		$model = new PasswordResetForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => true,
		];
	}

	/**
	 * oldPassword
	 * newPassword
	 */
	public function actionPasswordChange()
	{
		$model = new PasswordChangeForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => true,
		];
	}

	/**
	 * mobile
	 * code ?
	 */
	public function actionLoginByMobile()
	{
		$model = new LoginByMobileForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("Username and Password not provided");

		return $model->process();
	}

}
