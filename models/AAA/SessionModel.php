<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\models\AAA;

use yii\db\ActiveRecord;

class SessionModel extends ActiveRecord
{
  const STATUS_PENDING = 'P';
  const STATUS_ACTIVE = 'A';
  const STATUS_REMOVED = 'R';

	public static function tableName()
	{
		return '{{%AAA_Session}}';
	}

  public function rules()
  {
    return [
      ['ssnID', 'integer'],
      ['ssnUserID', 'integer'],
      ['ssnJWT', 'string', 'max' => 2048],

      ['ssnStatus', 'string', 'max' => 1],
      ['ssnStatus', 'default', 'value' => static::STATUS_PENDING],

      ['ssnExpireAt', 'safe'],

      ['ssnCreatedAt', 'safe'],
      // ['ssnCreatedBy', 'integer'],
      ['ssnUpdatedAt', 'safe'],
      ['ssnUpdatedBy', 'integer'],
    ];
  }

  public function behaviors()
	{
		return [
			[
				'class' => \app\classes\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'ssnCreatedAt',
				// 'createdByAttribute' => 'ssnCreatedBy',
				'updatedAtAttribute' => 'ssnUpdatedAt',
				'updatedByAttribute' => 'ssnUpdatedBy',
			],
		];
	}

}
