<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;
use app\classes\validators\GroupRequiredValidator;
use app\classes\validators\JsonValidator;
use app\modules\aaa\models\RoleModel;
use app\modules\aaa\models\AlertModel;
use app\modules\aaa\models\ApprovalRequestModel;
use yii\web\UnprocessableEntityHttpException;

class UserModel extends ActiveRecord implements IdentityInterface
{
  const STATUS_ACTIVE = 'A';
  const STATUS_INACTIVE = 'D';
  const STATUS_REMOVED = 'R';

  public $usrPassword;
  public $bypassRequestApprovalCode = false;

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

      ['usrPassword', 'string'], //, 'min' => 4],

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
    // $clearPassword = false;
    // if (empty($this->getDirtyAttributes(['usrPassword'])) == false) {
    if (empty($this->usrPassword) == false) {
      $this->usrPasswordHash = Yii::$app->security->generatePasswordHash($this->usrPassword);
      $this->usrPasswordCreatedAt = new Expression('NOW()');
    }

    try {
      $result = parent::save($runValidation, $attributeNames);

      // if ($result == false && $clearPassword != false)
      //   $this->usrPasswordHash = $clearPassword;

      return $result;

    } catch (\Throwable $th) {
      // if ($clearPassword != false)
      //   $this->usrPasswordHash = $clearPassword;
      throw($th);
    }
  }

  public function slotAfterInsert()
	{
    $settings = Yii::$app->params['settings'];

    if ($this->bypassRequestApprovalCode == false) {
      if (empty($this->usrEmail) == false) {
        ApprovalRequestModel::requestCode(
          $this->usrEmail,
          $this->usrID,
          $this->usrGender,
          $this->usrFirstName,
          $this->usrLastName
        );
      }

      if (empty($this->usrMobile) == false) {
        ApprovalRequestModel::requestCode(
          $this->usrMobile,
          $this->usrID,
          $this->usrGender,
          $this->usrFirstName,
          $this->usrLastName
        );
      }
    } //bypassRequestApprovalCode
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
  public function validatePassword($password) //, $salt)
  {
		return Yii::$app->security->validatePassword($password, $this->usrPasswordHash);
    // return md5($salt . $this->usrPasswordHash) == $password;
  }

}
