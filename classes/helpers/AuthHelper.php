<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\classes\helpers;

use Yii;
use app\classes\helpers\PrivHelper;
use yii\web\UnauthorizedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use app\models\AAA\SessionModel;
use app\models\AAA\RoleModel;

class AuthHelper
{
	static function recognizeAndNormalizeEmailOrMobileOrSSID($input)
	{
			$input = strtolower(trim($input));

			if (empty($input))
					return [$input, 'N']; //none

			//email
			if (strpos($input, '@') !== false) {
					if (filter_var($input, FILTER_VALIDATE_EMAIL) !== false)
							return [$input, 'E'];

					throw new UnprocessableEntityHttpException('Invalid email address');
			}

			//mobile
			try {
					$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
					$phoneNumber = $phoneUtil->parse($input, 'IR');
					if ($phoneUtil->isValidNumber($phoneNumber)) {
							$input = $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);
							return [$input, 'M'];
					}
			} catch(\Exception $exp) {
					$message = $exp->getMessage();
			}

			//ssid
			$sidMatched = preg_match('/^[0-9]{8,10}$/', $input);
			if ($sidMatched === 1)
					return [$input, 'S'];

			//
			return [$input, 'N']; //none
	}

	static function doLogin($user)
	{
		//create session
		//-----------------------
		$sessionModel = new SessionModel();
		$sessionModel->ssnUserID = $user->usrID;
		if ($sessionModel->save() == false)
			throw new UnauthorizedHttpException(implode("\n", $sessionModel->getFirstErrors()));

		//privs
		//-----------------------
		$privs = [];

		if ((empty($user->usrEmail) == false && empty($user->usrEmailApprovedAt))
			|| (empty($user->usrMobile) == false && empty($user->usrMobileApprovedAt))
		) {
			//set to user role until signup email or mobile approved
			$role = RoleModel::findOne(['rolID' => RoleModel::ROLE_USER]);
			if (empty($role->rolPrivs) == false)
				$privs = $role->rolPrivs;
		} else {
			if (empty($user->usrRoleID) == false) {
				$role = $user->role;
				if (empty($role->rolPrivs) == false)
					$privs = array_replace_recursive($privs, $role->rolPrivs);
			}

			if (empty($user->usrPrivs) == false)
				$privs = array_replace_recursive($privs, $user->usrPrivs);
		}

		PrivHelper::digestPrivs($privs);

		//token
		//-----------------------
		$ttl = Yii::$app->jwt->ttl;

		$now = new \DateTimeImmutable();
		$expire = $now->modify("+{$ttl} second");

		$token = Yii::$app->jwt->getBuilder()
			->identifiedBy($sessionModel->ssnID) //Yii::$app->session->id)	// Configures the id (jti claim)
			->issuedAt($now)
			->expiresAt($expire)
			->withClaim('privs', $privs)
			->withClaim('uid', $user->usrID)
			->withClaim('email', $user->usrEmail)
			->withClaim('mobile', $user->usrMobile)
			// ->withClaim('firstName', $model->user->usrFirstName)
			// ->withClaim('lastName', $model->user->usrLastName)
			->getToken(
				Yii::$app->jwt->getConfiguration()->signer(),
				Yii::$app->jwt->getConfiguration()->signingKey()
			)
			->toString();

		//update session
		//-----------------------
		$sessionModel->ssnJWT = $token;
		$sessionModel->ssnStatus = SessionModel::STATUS_ACTIVE;
		$sessionModel->ssnExpireAt = $expire->format('Y-m-d H:i:s');
		$sessionModel->save();

		//-----------------------
		return $token;
	}

	static function logout()
	{
		if (!Yii::$app->user->accessToken)
			return;

		$sessionID = Yii::$app->user->accessToken->claims()->get(\Lcobucci\JWT\Token\RegisteredClaims::ID);
		if ($sessionID == null)
			throw new NotFoundHttpException("Session not found");

		$rowsAffected = SessionModel::deleteAll([
			'ssnID' => $sessionID,
		]);

		if ($rowsAffected != 1)
			throw new NotFoundHttpException("Could not log out");

		Yii::$app->user->accessToken = null;
	}

}
