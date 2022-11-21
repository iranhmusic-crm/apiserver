<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\components;

use Yii;
use yii\web\User as BaseUser;

class User extends BaseUser
{
	public $identityClass = \app\modules\aaa\models\UserModel::class;

	//current sessions jwt token dataset
	public ?\Lcobucci\JWT\Token\Plain $accessToken = null;

	public function loginByAccessToken($token, $type = null)
	{
		$identity = parent::loginByAccessToken($token, $type);

		if ($identity)
			$this->accessToken = Yii::$app->jwt->parse($token);

		return $identity;
	}

}
