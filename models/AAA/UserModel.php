<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\models\AAA;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;
use app\classes\validators\GroupRequiredValidator;
use app\classes\validators\JsonValidator;
use app\models\AAA\RoleModel;
use app\models\AAA\AlertModel;
use app\models\AAA\ApprovalRequestModel;
use yii\web\UnprocessableEntityHttpException;

class UserModel extends ActiveRecord implements IdentityInterface
{
  const STATUS_ACTIVE = 'A';
  const STATUS_INACTIVE = 'D';
  const STATUS_REMOVED = 'R';

  use \app\classes\db\SoftDeleteActiveRecordTrait;
	public function softDelete()
	{
    $this->usrStatus = static::STATUS_REMOVED;
    $this->usrRemovedAt = new Expression('UNIX_TIMESTAMP()');

    if (isset(Yii::$app->user->identity) && (Yii::$app->user->getIsGuest() == false))
      $this->usrRemovedBy = Yii::$app->user->identity->usrID;

    $this->save();
	}

  public static function tableName()
  {
    return '{{%AAA_User}}';
  }

  public function rules()
  {
    return [
      ['usrID', 'integer'],

      ['usrRoleID', 'integer'],
      ['usrRoleID', 'default', 'value' => RoleModel::ROLE_USER],
      ['usrPrivs', JsonValidator::class],

      ['usrEmail', 'string', 'max' => 128],
      ['usrEmail', 'email'],
			['usrEmailApprovedAt', 'safe'],
      // ['usrConfirmEmailToken', 'string', 'max' => 255],

      ['usrMobile', 'string', 'max' => 32],
      // ['usrMobileConfirmToken', 'integer'],
      ['usrMobileApprovedAt', 'safe'],

      [[
        'usrEmail',
        'usrMobile'
      ], GroupRequiredValidator::class,
        'min' => 1,
        'in' => [
          'usrEmail',
          'usrMobile'
        ],
        'message' => 'one of email or mobile is required',
      ],

      ['usrSSID', 'string', 'max' => 16],

      // ['usrAuthKey', 'string', 'max' => 32],
      // ['usrAuthKey', 'required'],

      ['usrPasswordHash', 'string', 'max' => 255],
      ['usrPasswordCreatedAt', 'safe'],

      // ['usrPasswordResetToken', 'string', 'max' => 255],

      ['usrStatus', 'string', 'max' => 1],

      ['usrCreatedAt', 'safe'],
      ['usrCreatedBy', 'integer'],
      ['usrUpdatedAt', 'safe'],
      ['usrUpdatedBy', 'integer'],
      ['usrRemovedAt', 'integer'], //UNIX_TIMESTAMP()
      ['usrRemovedBy', 'integer'],
    ];
  }

  public function behaviors()
	{
		return [
			[
				'class' => \app\classes\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'usrCreatedAt',
				'createdByAttribute' => 'usrCreatedBy',
				'updatedAtAttribute' => 'usrUpdatedAt',
				'updatedByAttribute' => 'usrUpdatedBy',
			],
		];
	}

  public function init()
	{
    parent::init();

    $this->on(ActiveRecord::EVENT_AFTER_INSERT, [$this, 'slotAfterInsert']);
	}

  public function transactions()
  {
    return [
      self::SCENARIO_DEFAULT => self::OP_INSERT,
    ];
  }

  public function save($runValidation = true, $attributeNames = null)
  {
    $clearPassword = false;
    if (empty($this->getDirtyAttributes(['usrPasswordHash'])) == false) {
      $clearPassword = $this->usrPasswordHash;
      $this->usrPasswordHash = Yii::$app->security->generatePasswordHash($this->usrPasswordHash);
      $this->usrPasswordCreatedAt = new Expression('NOW()');
    }

    try {
      $result = parent::save($runValidation, $attributeNames);

      if ($result == false && $clearPassword != false)
        $this->usrPasswordHash = $clearPassword;

      return $result;

    } catch (\Throwable $th) {
      if ($clearPassword != false)
        $this->usrPasswordHash = $clearPassword;
      throw($th);
    }
  }

  public function slotAfterInsert()
	{
    $settings = Yii::$app->params['settings'];

    if (empty($this->usrEmail) == false) {
      //generate code
      $code = Yii::$app->security->generateRandomString() . '_' . time();

      //save to approvals
      $expireTTL = $settings['AAA']['approvalRequest']['email']['expire-ttl'];

      $approvalRequestModel = new ApprovalRequestModel();
      $approvalRequestModel->aprUserID   = $this->usrID;
      $approvalRequestModel->aprKeyType  = ApprovalRequestModel::KEYTYPE_EMAIL;
      $approvalRequestModel->aprKey      = $this->usrEmail;
      $approvalRequestModel->aprCode     = $code;
      $approvalRequestModel->aprExpireAt = new Expression("DATE_ADD(NOW(), INTERVAL {$expireTTL} SECOND)");
      if ($approvalRequestModel->save() == false)
        throw new UnprocessableEntityHttpException("error in creating email approval request\n" . implode("\n", $approvalRequestModel->getFirstErrors()));

      //send alert 'emailApproval'
      $alertModel = new AlertModel();
			$alertModel->alrUserID  = $this->usrID;
			$alertModel->alrTypeKey = 'emailApproval';
			$alertModel->alrTarget  = $this->usrEmail;
			$alertModel->alrInfo    = [
        'gender' => $this->usrGender,
        'firstName' => $this->usrFirstName,
        'lastName' => $this->usrLastName,
        'email' => $this->usrEmail,
        'code' => $code,
      ];
      if ($alertModel->save() == false)
        throw new UnprocessableEntityHttpException("error in creating mobile alert\n" . implode("\n", $alertModel->getFirstErrors()));
    }

    if (empty($this->usrMobile) == false) {
      //generate code
      $code = strval(rand(123456, 987654));

      //save to approvals
      $expireTTL = $settings['AAA']['approvalRequest']['mobile']['expire-ttl'];

      $approvalRequestModel = new ApprovalRequestModel();
      $approvalRequestModel->aprUserID   = $this->usrID;
      $approvalRequestModel->aprKeyType  = ApprovalRequestModel::KEYTYPE_MOBILE;
      $approvalRequestModel->aprKey      = $this->usrMobile;
      $approvalRequestModel->aprCode     = $code;
      $approvalRequestModel->aprExpireAt = new Expression("DATE_ADD(NOW(), INTERVAL {$expireTTL} SECOND)");
      if ($approvalRequestModel->save() == false)
        throw new UnprocessableEntityHttpException("error in creating mobile approval request\n" . implode("\n", $approvalRequestModel->getFirstErrors()));

      //send alert 'mobileApproval'
      $alertModel = new AlertModel();
			$alertModel->alrUserID  = $this->usrID;
			$alertModel->alrTypeKey = 'mobileApproval';
			$alertModel->alrTarget  = $this->usrEmail;
			$alertModel->alrInfo    = [
        'gender' => $this->usrGender,
        'firstName' => $this->usrFirstName,
        'lastName' => $this->usrLastName,
        'mobile' => $this->usrMobile,
        'code' => $code,
      ];
      if ($alertModel->save() == false)
        throw new UnprocessableEntityHttpException("error in creating mobile alert\n" . implode("\n", $alertModel->getFirstErrors()));
    }
  }

  public function getRole()
  {
    return $this->hasOne(RoleModel::class, ['rolID' => 'usrRoleID']);
  }

  /**
   * {@inheritdoc}
   */
  public static function findIdentity($id)
  {
    return UserModel::find()
      ->where(['usrID' => $id])
      ->andWhere(['!=', 'usrStatus', static::STATUS_REMOVED])
      ->one();
  }

  public static function findIdentityByAccessToken($token, $type = null)
  {
    $user = UserModel::find()
      ->joinWith('role')
      ->innerJoin(SessionModel::tableName(),
        SessionModel::tableName() . '.ssnUserID = ' . UserModel::tableName() . '.usrID'
      )
      ->where(['ssnJWT' => $token])
      ->one();

    // if ($user == null)
    //   throw new \yii\web\ForbiddenHttpException(Yii::t('yii', 'token not found'));

    return $user;
  }

  // public static function findByUsername($username)
  // {
  //   return static::find()
  //     ->joinWith('role')
  //     ->where(['usrStatus' => self::STATUS_ACTIVE])
  //     ->andWhere(['or',
  //                   ['usrEmail' => $username],
  //                   ['usrMobile' => $username]
  //               ])
  //     ->one()
  //   ;
  // }

  /**
   * {@inheritdoc}
   */
  public function getId()
  {
    return $this->usrID;
  }

  public function getAuthKey()
  {
    return null; //$this->authKey;
  }

  public function validateAuthKey($authKey)
  {
    return false;
  //   return $this->authKey === $authKey;
  }

  /**
   * Validates password
   *
   * @param string $password password to validate
   * @return bool if password provided is valid for current user
   */
  public function validatePassword($password, $salt)
  {
    return md5($salt . $this->usrPasswordHash) == $password;
  }
}
