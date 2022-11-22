<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\models;

use yii\db\ActiveRecord;
use app\classes\validators\JsonValidator;

class AlertModel extends ActiveRecord
{
  const STATUS_NEW 				= 'N';
  const STATUS_PROCESSING = 'P';
  const STATUS_SENT 			= 'S';
  const STATUS_ERROR 			= 'E';
  const STATUS_REMOVED 		= 'R';

	public static function tableName()
	{
		return '{{%AAA_Alert}}';
	}

  public function rules()
  {
    return [
      ['alrID', 'integer'],
			['alrUserID', 'integer'],

			['alrApprovalRequestID', 'integer'],
			['alrForgotPasswordRequestID', 'integer'],

			['alrTypeKey', 'string', 'max' => 64],
			['alrTypeKey', 'required'],

			['alrTarget', 'string', 'max' => 255],

			['alrInfo', JsonValidator::class],
			['alrInfo', 'required'],

			['alrLockedAt', 'safe'], //datetime
			['alrLockedBy', 'string', 'max' => 64],
			['alrLastTryAt', 'safe'], //datetime'],
			['alrSentAt', 'safe'], //datetime
			['alrResult', JsonValidator::class],

			['alrStatus', 'string', 'max' => 1],
      ['alrStatus', 'default', 'value' => static::STATUS_NEW],
			['alrStatus', 'required'],

			['alrCreatedAt', 'safe'],
			['alrCreatedBy', 'integer'],
      // ['alrUpdatedAt', 'safe'],
      // ['alrUpdatedBy', 'integer'],
    ];
  }

  public function behaviors()
	{
		return [
			[
				'class' => \app\classes\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'alrCreatedAt',
				'createdByAttribute' => 'alrCreatedBy',
				// 'updatedAtAttribute' => 'alrUpdatedAt',
				// 'updatedByAttribute' => 'alrUpdatedBy',
			],
		];
	}

}
