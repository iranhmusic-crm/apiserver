<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\models;

use yii\base\Model;
use app\classes\helpers\AuthHelper;
use app\modules\aaa\models\ApprovalRequestModel;

class ApproveCodeForm extends Model
{
  public $input;
  public $code;

	private $_inputName = '';
  private $_user = false;

  public function rules()
  {
    return [
      ['input', 'required'],
      ['code', 'required'],
    ];
  }

  public function getInputName()
  {
    return $this->_inputName;
  }

  public function approve()
  {
    if ($this->validate() == false)
      return false;

    list ($normalizedInput, $inputType) = AuthHelper::checkLoginPhrase($this->input, false);

		return ApprovalRequestModel::acceptCode($normalizedInput, $this->code);
  }

}
