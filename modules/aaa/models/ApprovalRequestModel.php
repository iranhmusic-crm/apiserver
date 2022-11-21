<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use app\classes\helpers\AuthHelper;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use app\modules\aaa\models\UserModel;
use app\modules\aaa\models\AlertModel;

class ApprovalRequestModel extends ActiveRecord
{
  const KEYTYPE_EMAIL = 'E';
  const KEYTYPE_MOBILE = 'M';

  const STATUS_NEW = 'N';
  const STATUS_SENT = 'S';
  const STATUS_APPLIED = 'A';
  const STATUS_EXPIRED = 'E';

  public $ElapsedSeconds;
  public $IsExpired;

	public static function tableName()
	{
		return '{{%AAA_ApprovalRequest}}';
	}

  public function rules()
  {
    return [
      ['aprID', 'integer'],
      ['aprUserID', 'integer'],

      ['aprKeyType', 'string', 'max' => 1],

      ['aprKey', 'string', 'max' => 128],
      ['aprCode', 'string', 'max' => 48],

      ['aprLastRequestAt', 'safe'],
      ['aprExpireAt', 'safe'],
      ['aprSentAt', 'safe'],
      ['aprApplyAt', 'safe'],

      ['aprStatus', 'string', 'max' => 1],
      ['aprStatus', 'default', 'value' => static::STATUS_NEW],

      ['aprCreatedAt', 'safe'],
      // ['aprCreatedBy', 'integer'],
      // ['aprUpdatedAt', 'safe'],
      // ['aprUpdatedBy', 'integer'],

      [[
        'aprUserID',
        'aprKeyType',
        'aprKey',
        'aprCode',
        'aprLastRequestAt',
        'aprExpireAt',
        'aprStatus',
      ], 'required'],

    ];
  }

  public function behaviors()
	{
		return [
			[
				'class' => \app\classes\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'aprCreatedAt',
				// 'createdByAttribute' => 'aprCreatedBy',
				// 'updatedAtAttribute' => 'aprUpdatedAt',
				// 'updatedByAttribute' => 'aprUpdatedBy',
			],
		];
	}

  public function getUser()
  {
    return $this->hasOne(UserModel::class, ['usrID' => 'aprUserID']);
  }

  static function formatTimeFromSeconds($seconds)
  {
    $days = intval($seconds / (24 * 60 * 60));
    $seconds -= $days * (24 * 60 * 60);

    $hours = intval($seconds / (60 * 60));
    $seconds -= $hours * (60 * 60);

    $minutes = intval($seconds / 60);
    $seconds -= $minutes * 60;

    $parts = [];

    if ($days > 0)
      $parts[] = $days;

    if (($days > 0) || ($hours > 0))
      $parts[] = $hours;

    if (($days > 0) || ($hours > 0) || ($minutes > 0))
      $parts[] = $minutes;

    $parts[] = $seconds;

    $result = implode(':', $parts);

    if (count($parts) == 1)
      $result = '0:' . $result;

    return $result;
  }

  static function requestCode(
    $emailOrMobile,
    $userID = null,
    $gender = null,
    $firstName = null,
    $lastName = null
  ) {
    list ($normalizedInput, $inputType) = AuthHelper::checkLoginPhrase($emailOrMobile, false);

    // if ($inputType != $type)
    //   throw new UnauthorizedHttpException('input type is not correct');

    //flag expired
    //-----------------------------------
    $approvalRequestTableName = static::tableName();
    $alertTableName = AlertModel::tableName();

    $qry =<<<SQLSTR
          UPDATE {$alertTableName} alr
      INNER JOIN {$approvalRequestTableName} apr
              ON apr.aprID = alr.alrApprovalRequestID
             SET alrStatus = 'R'
           WHERE aprKey = '{$normalizedInput}'
             AND aprExpireAt <= NOW()
SQLSTR;
    Yii::$app->getDb()->createCommand($qry)->execute();

    $qry =<<<SQLSTR
          UPDATE {$approvalRequestTableName}
             SET aprStatus = 'E'
           WHERE aprKey = '{$normalizedInput}'
             AND aprExpireAt <= NOW()
SQLSTR;
    Yii::$app->getDb()->createCommand($qry)->execute();

    //find current
    //-----------------------------------
    $models = ApprovalRequestModel::find()
      ->addSelect([
        '*',
        'TIME_TO_SEC(TIMEDIFF(NOW(), COALESCE(aprSentAt, aprLastRequestAt))) AS ElapsedSeconds',
        'aprExpireAt <= NOW() AS IsExpired'
      ])
      ->joinWith('user', "INNER JOIN")
      ->where(['aprKey' => $normalizedInput])
      // ->andWhere(['aprCode' => $code])
      ->andWhere(['in', 'aprStatus', [
        static::STATUS_NEW, static::STATUS_SENT] ])
      ->limit(2)
      // ->asArray()
      ->all();

    if (empty($models) == false && count($models) > 1) {
      $qry =<<<SQLSTR
          UPDATE {$alertTableName} alr
      INNER JOIN {$approvalRequestTableName} apr
              ON apr.aprID = alr.alrApprovalRequestID
             SET alrStatus = 'R'
           WHERE aprKey = '{$normalizedInput}'
             AND aprStatus IN ('N', 'S')
SQLSTR;
      Yii::$app->getDb()->createCommand($qry)->execute();

      $qry =<<<SQLSTR
          UPDATE {$approvalRequestTableName}
             SET aprStatus = 'E'
           WHERE aprKey = '{$normalizedInput}'
             AND aprStatus IN ('N', 'S')
SQLSTR;
      Yii::$app->getDb()->createCommand($qry)->execute();
    }

    $settings = Yii::$app->params['settings'];
    $code = null;

    if (empty($models) == false) {
      $approvalRequestModel = $models[0];

      if (empty($userID)) {
        $userID    = $approvalRequestModel->user->usrID;
        $gender    = $approvalRequestModel->user->usrGender;
        $firstName = $approvalRequestModel->user->usrFirstName;
        $lastName  = $approvalRequestModel->user->usrLastName;
      }

      if ($approvalRequestModel->IsExpired) {
        $approvalRequestModel->aprStatus = static::STATUS_EXPIRED;
        $approvalRequestModel->save();

        $approvalRequestModel = null;
      } else {
        $resendTTL = ArrayHelper::getValue($settings,
          'AAA.approvalRequest.' . ($inputType == 'E' ? 'email' : 'mobile') . 'resend-ttl', 120);

        if ($approvalRequestModel->ElapsedSeconds < $resendTTL) {
          $seconds = $resendTTL - $approvalRequestModel->ElapsedSeconds;

          throw new UnauthorizedHttpException('the waiting time has not elapsed. ('
            . static::formatTimeFromSeconds($seconds) . ' remained)');
        }

        $code = $approvalRequestModel->aprCode;
      }
    }

    if (empty($userID)) {
      $userModel = UserModel::find()
        ->where(['usr' . ($inputType == 'E' ? 'Email' : 'Mobile') => $normalizedInput])
        ->andWhere("usrStatus != 'R'")
        ->one();

      if (!$userModel)
        throw new UnauthorizedHttpException('user not found');

      $userID    = $userModel->usrID;
      $gender    = $userModel->usrGender;
      $firstName = $userModel->usrFirstName;
      $lastName  = $userModel->usrLastName;
    }

    if (empty($code)) {
      if ($inputType == static::KEYTYPE_EMAIL)
        $code = Yii::$app->security->generateRandomString() . '_' . time();
      else if ($inputType == static::KEYTYPE_MOBILE)
        $code = strval(rand(123456, 987654));
      else
        throw new UnauthorizedHttpException("invalid input type {$inputType}");

      $expireTTL = ArrayHelper::getValue($settings,
      'AAA.approvalRequest.' . ($inputType == 'E' ? 'email' : 'mobile') . 'expire-ttl', 15 * 60);

      $approvalRequestModel = new static();
      $approvalRequestModel->aprUserID        = $userID;
      $approvalRequestModel->aprKeyType       = $inputType;
      $approvalRequestModel->aprKey           = $normalizedInput;
      $approvalRequestModel->aprCode          = $code;
      $approvalRequestModel->aprLastRequestAt = new Expression('NOW()');
      $approvalRequestModel->aprExpireAt      = new Expression("DATE_ADD(NOW(), INTERVAL {$expireTTL} SECOND)");
      if ($approvalRequestModel->save() == false)
        throw new UnprocessableEntityHttpException("error in creating approval request\n" . implode("\n", $approvalRequestModel->getFirstErrors()));
    } else {
      $approvalRequestModel->aprLastRequestAt = new Expression('NOW()');
      if ($approvalRequestModel->save() == false)
        throw new UnprocessableEntityHttpException("error in updating approval request\n" . implode("\n", $approvalRequestModel->getFirstErrors()));

      $qry =<<<SQLSTR
          UPDATE {$alertTableName}
             SET alrStatus = 'R'
           WHERE alrApprovalRequestID = '{$approvalRequestModel->aprID}'
SQLSTR;
      Yii::$app->getDb()->createCommand($qry)->execute();
    }

    $alertModel = new AlertModel();
    $alertModel->alrUserID  = $userID;
    $alertModel->alrApprovalRequestID = $approvalRequestModel->aprID;
    $alertModel->alrTarget  = $normalizedInput;

    $alrInfo = [
      'gender' => $gender,
      'firstName' => $firstName,
      'lastName' => $lastName,
      'code' => $code,
    ];

    if ($inputType == static::KEYTYPE_EMAIL) {
      $alertModel->alrTypeKey = AlertModel::TYPE_EMAIL_APPROVAL;
      $alrInfo['email'] = $normalizedInput;
    } else {
      $alertModel->alrTypeKey = AlertModel::TYPE_MOBILE_APPROVAL;
      $alrInfo['mobile'] = $normalizedInput;
    }
    $alertModel->alrInfo = $alrInfo;

    if ($alertModel->save() == false)
      throw new UnprocessableEntityHttpException("could not save alert\n" . implode("\n", $alertModel->getFirstErrors()));
  }

  static function acceptCode($emailOrMobile, $code)
  {
    list ($normalizedInput, $inputType) = AuthHelper::checkLoginPhrase($emailOrMobile, false);

    //find current
    //------------------------------
    $models = ApprovalRequestModel::find()
      ->addSelect([
        '*',
        'aprExpireAt <= NOW() AS IsExpired'
      ])
      ->joinWith('user', "INNER JOIN")
      ->where(['aprKey' => $normalizedInput])
      ->andWhere(['aprCode' => $code])
      ->andWhere(['in', 'aprStatus', [
        static::STATUS_NEW, static::STATUS_SENT, static::STATUS_APPLIED] ])
      ->limit(2)
      // ->asArray()
      ->all();

    if (empty($models))
      throw new UnauthorizedHttpException('request not found');

    if (count($models) > 1)
      throw new UnauthorizedHttpException('more than one request found');

    $approvalRequestModel = $models[0];

    // new ApprovalRequestModel(); //$approvalRequestModelRaw);
    // $approvalRequestModelRaw = $rows[0];
    // ApprovalRequestModel::populateRecord($approvalRequestModel, $approvalRequestModelRaw);

    //validate
    //------------------------------
    if ($approvalRequestModel->aprKeyType != $inputType) {
      $approvalRequestModel->aprStatus = static::STATUS_EXPIRED;
      $approvalRequestModel->save();

      throw new UnauthorizedHttpException('incorrect key type');
    }

    if ($approvalRequestModel->aprStatus == static::STATUS_APPLIED)
      throw new UnauthorizedHttpException('this code applied before');

    if ($approvalRequestModel->aprStatus != static::STATUS_SENT)
      throw new UnauthorizedHttpException('code not sent to the client');

    if ($approvalRequestModel->IsExpired) {
      $approvalRequestModel->aprStatus = static::STATUS_EXPIRED;
      $approvalRequestModel->save();

      throw new UnauthorizedHttpException('code expired');
    }

    //accept
    //------------------------------
    $transaction = static::getDb()->beginTransaction();
    try {
      //1: apr
      $approvalRequestModel->aprStatus = static::STATUS_APPLIED;
      $approvalRequestModel->aprApplyAt = new Expression('NOW()');
      if ($approvalRequestModel->save() == false)
        throw new UnprocessableEntityHttpException("could not save approval request\n" . implode("\n", $approvalRequestModel->getFirstErrors()));

      //2: user
      if ($approvalRequestModel->aprKeyType == static::KEYTYPE_EMAIL) {
        $approvalRequestModel->user->usrEmailApprovedAt = new Expression('NOW()');
        if (empty($approvalRequestModel->user->usrEmail)
              || ($approvalRequestModel->user->usrEmail != $approvalRequestModel->aprKey))
          $approvalRequestModel->user->usrEmail = $approvalRequestModel->aprKey;
      }
      else if ($approvalRequestModel->aprKeyType == static::KEYTYPE_MOBILE) {
        $approvalRequestModel->user->usrMobileApprovedAt = new Expression('NOW()');
        if (empty($approvalRequestModel->user->usrMobile)
              || ($approvalRequestModel->user->usrMobile != $approvalRequestModel->aprKey))
          $approvalRequestModel->user->usrMobile = $approvalRequestModel->aprKey;
      }

      if ($approvalRequestModel->user->save() == false)
        throw new UnprocessableEntityHttpException("could not save user\n" . implode("\n", $approvalRequestModel->user->getFirstErrors()));

      //3: send alert '[email|mobile]Approved'
      $alertModel = new AlertModel();
			$alertModel->alrUserID  = $approvalRequestModel->user->usrID;
      // $alertModel->alrApprovalRequestID = null;
			$alertModel->alrTarget  = $approvalRequestModel->aprKey;

      $alrInfo = [
        'gender' => $approvalRequestModel->user->usrGender,
        'firstName' => $approvalRequestModel->user->usrFirstName,
        'lastName' => $approvalRequestModel->user->usrLastName,
      ];

      if ($approvalRequestModel->aprKeyType == static::KEYTYPE_EMAIL) {
        $alertModel->alrTypeKey = AlertModel::TYPE_EMAIL_APPROVED;
        $alrInfo['email'] = $approvalRequestModel->aprKey;
      } else {
        $alertModel->alrTypeKey = AlertModel::TYPE_MOBILE_APPROVED;
        $alrInfo['mobile'] = $approvalRequestModel->aprKey;
      }
			$alertModel->alrInfo = $alrInfo;

      if ($alertModel->save() == false)
        throw new UnprocessableEntityHttpException("could not save alert\n" . implode("\n", $alertModel->getFirstErrors()));

      //
      $transaction->commit();

    } catch (\Exception $e) {
        $transaction->rollBack();
        throw $e;
    } catch (\Throwable $e) {
        $transaction->rollBack();
        throw $e;
    }

    return true;
  }

}
