<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\models\AAA;

use yii\db\ActiveRecord;

class ApprovalRequestModel extends ActiveRecord
{
  const KEYTYPE_EMAIL = 'E';
  const KEYTYPE_MOBILE = 'M';

  const STATUS_NEW = 'N';
  const STATUS_SENT = 'S';
  const STATUS_APPLIED = 'A';

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

}
