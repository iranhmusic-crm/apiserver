<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\models;

use yii\base\Model;
use app\modules\aaa\models\ApprovalRequestModel;

class ApproveCodeForm extends Model
{
  public $input;
  public $code;

  public function rules()
  {
    return [
      ['input', 'required'],
      ['code', 'required'],
    ];
  }

  public function approve()
  {
    if ($this->validate() == false)
      return false;

    // list ($normalizedInput, $inputType) = AuthHelper::checkLoginPhrase($this->input, false);

		return ApprovalRequestModel::acceptCode($this->input, $this->code);
  }

}
