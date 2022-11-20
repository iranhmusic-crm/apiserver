<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\classes\behaviors;

use yii\base\Exception;
use yii\db\BaseActiveRecord;
use app\classes\behaviors\MultiAttributeBehavior;

class ReplaceInStringBehavior extends MultiAttributeBehavior
{
	public $stringAttributes = null;
	public $language = null; //fa, fa_IR, ...
	//public $replaceMap = [];
	public $replaceMap = [
		'{}' => '‌', //ZWNJ
		'>>' => '»',
		'<<' => '«',
		// '۰' => '0',
		// '۱' => '1',
		// '۲' => '2',
		// '۳' => '3',
		// '۴' => '4',
		// '۵' => '5',
		// '۶' => '6',
		// '۷' => '7',
		// '۸' => '8',
		// '۹' => '9',
		'ى'	=> 'ی',
		// 'ئ'	=> 'ی',
		'ي' => 'ی',
		'ك' => 'ک',
	];

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if (empty($this->stringAttributes))
			throw new Exception('stringAttributes must be set.');
		if (empty($this->replaceMap))
			throw new Exception('replaceMap must be set.');

		parent::init();

		if (empty($this->attributes))
		{
			$this->attributes = [
				BaseActiveRecord::EVENT_BEFORE_INSERT => $this->stringAttributes,
				BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->stringAttributes,
			];
		}
	}

	protected function getAttributeValue($event, $attribute)
	{
		if ($this->owner->$attribute === null)
			return parent::getAttributeValue($event, $attribute);

		if (is_array($this->owner->$attribute))
		{
			$val = $this->owner->$attribute;
			foreach ($val as $k => &$v)
			{
				if (is_string($v))
				{
					foreach ($this->replaceMap as $_k => $_v)
						$val[$k] = str_replace($_k, $_v, $val[$k]);
					$val[$k] = trim($val[$k]);
				}
			}
		}
		else if (is_string($this->owner->$attribute))
		{
			$val = $this->owner->$attribute;
			foreach ($this->replaceMap as $k => $v)
				$val = str_replace($k, $v, $val);
		}
		else
			$val = parent::getAttributeValue($event, $attribute);

		if ($val === null)
			return $val;

		if (is_string($val))
			return trim($val);

		return $val;
	}

}
