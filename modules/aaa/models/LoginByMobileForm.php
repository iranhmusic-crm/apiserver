<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\models;

use yii\base\Model;
use app\classes\helpers\AuthHelper;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UnauthorizedHttpException;
use app\classes\helpers\PhoneHelper;

class LoginByMobileForm extends Model
{
  public $mobile;
  public $code;

  public function rules()
  {
    return [
      ['mobile', 'required'],
      ['code', 'string'],
    ];
  }

	public function process()
	{
    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

		$normalizedMobile = PhoneHelper::normalizePhoneNumber($this->mobile);
		if (!$normalizedMobile)
			throw new UnprocessableEntityHttpException('invalid mobile number');

		//send code
		//------------------------
		if (empty($this->code)) {
			$userID = null;
			$gender = null;
			$firstName = null;
			$lastName = null;

			$user = UserModel::find()
				->where('usrStatus != \'' . UserModel::STATUS_REMOVED . '\'')
				->andWhere(['usrMobile' => $normalizedMobile])
				->one();

			if ($user) {
				$userID    = $user->usrID;
				$gender    = $user->usrGender;
				$firstName = $user->usrFirstName;
				$lastName  = $user->usrLastName;
			}

			return ApprovalRequestModel::requestCode(
				$normalizedMobile,
				$userID,
				$gender,
				$firstName,
				$lastName,
				true
			);
		}

		//login
		//------------------------
		$userModel = ApprovalRequestModel::acceptCode($normalizedMobile, $this->code);
		if ($userModel) {
			list ($token, $mustApprove) = AuthHelper::doLogin($userModel);

			return [
				'token' => $token,
				'mustApprove' => $mustApprove,
			];
		}

		throw new UnauthorizedHttpException("could not login. \n" . implode("\n", $this->getFirstErrors()));
	}

}
