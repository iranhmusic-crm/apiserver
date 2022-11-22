<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use app\classes\controller\BaseRestController;
use app\classes\helpers\PrivHelper;
use app\modules\aaa\models\RoleModel;

class RoleController extends BaseRestController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		return $behaviors;
	}

	protected function findModel($id)
	{
		if (($model = RoleModel::findOne($id)) !== null)
			return $model;

		throw new NotFoundHttpException('The requested item not exist.');
	}

	public function actionIndex()
	{
		PrivHelper::checkPriv('aaa/role/crud', '0100');

		$query = RoleModel::find();

		return $query->all();
	}

	public function actionView($id)
	{
		PrivHelper::checkPriv('aaa/role/crud', '0100');

		return $this->findModel($id);
	}

	public function actionCreate()
	{
		PrivHelper::checkPriv('aaa/role/crud', '1000');

		$model = new RoleModel();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => [
				'message' => 'created',
				'rolID' => $model->rolID,
			],
		];
	}

	public function actionUpdate($id)
	{
		PrivHelper::checkPriv('aaa/role/crud', '0010');

		$model = $this->findModel($id);
		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => [
				'message' => 'updated',
				'rolID' => $model->rolID,
			],
		];
	}

	public function actionDelete($id)
	{
		PrivHelper::checkPriv('aaa/role/crud', '0001');

		$model = $this->findModel($id);

		if ($model->delete() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => [
				'message' => 'deleted',
				'rolID' => $model->rolID,
			],
		];
	}

	public function actionOptions()
	{
		return 'options';
	}

}
