<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\models\AAA;

use yii\db\ActiveRecord;

class AlertTypeModel extends ActiveRecord
{
	const TYPE_EMAIL = 'E';
	const TYPE_MOBILE = 'M';

	public static function tableName()
	{
		return '{{%AAA_AlertType}}';
	}

  public function rules()
  {
    return [
      ['altID', 'integer'],

			['altKey', 'string', 'max' => 64],
			['altKey', 'required'],

			['altType', 'string', 'max' => 1],
			['altType', 'required'],

			['altBody', 'string'],
			['altBody', 'required'],
    ];
  }

  public function behaviors()
	{
		return [
			[
				// 'class' => \app\classes\behaviors\RowDatesAttributesBehavior::class,
				// 'createdAtAttribute' => 'altCreatedAt',
				// 'createdByAttribute' => 'altCreatedBy',
				// 'updatedAtAttribute' => 'altUpdatedAt',
				// 'updatedByAttribute' => 'altUpdatedBy',
			],
		];
	}

}
