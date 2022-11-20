<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\behaviors;

use yii\behaviors\AttributeBehavior;
use yii\base\Exception;
use yii\db\BaseActiveRecord;

class NullEmptyStringBehavior extends AttributeBehavior
{
	public $stringAttribute = null;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if (empty($this->stringAttribute))
			throw new Exception('stringAttribute must be set.');

		parent::init();

		if (empty($this->attributes)) {
			$this->attributes = [
				BaseActiveRecord::EVENT_BEFORE_INSERT => $this->stringAttribute,
				BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->stringAttribute,
			];
		}
	}

	/**
	 * @inheritdoc
	 *
	 * In case, when the [[value]] is `null`, the result of the PHP function [time()](http://php.net/manual/en/function.time.php)
	 * will be used as value.
	 */
	protected function getValue($event)
	{
		if (($event->sender[$this->stringAttribute] === null) || ($event->sender[$this->stringAttribute] == ''))
			return null;
		return $event->sender[$this->stringAttribute];
	}

}
