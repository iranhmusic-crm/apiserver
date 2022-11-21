<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\models;

use yii\db\ActiveRecord;
use app\classes\validators\JsonValidator;

class RoleModel extends ActiveRecord
{
	CONST ROLE_FULL_ACCESS = 1;
	CONST ROLE_USER = 10;

	public static function tableName()
	{
		return '{{%AAA_Role}}';
	}

  public function rules()
  {
    return [
      ['rolID', 'integer'],
			['rolName', 'string', 'max' => 64],
			['rolParentID', 'integer'],
			['rolPrivs', JsonValidator::class],

      ['rolCreatedAt', 'safe'],
      ['rolCreatedBy', 'integer'],
      ['rolUpdatedAt', 'safe'],
      ['rolUpdatedBy', 'integer'],
    ];
  }

	public function behaviors()
	{
		return [
			[
				'class' => \app\classes\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'rolCreatedAt',
				'createdByAttribute' => 'rolCreatedBy',
				'updatedAtAttribute' => 'rolUpdatedAt',
				'updatedByAttribute' => 'rolUpdatedBy',
			],
			[
				'class' => \app\classes\behaviors\ReplaceInStringBehavior::class,
				'stringAttributes' => [
					'rolName',
				],
			],
		];
	}

}
