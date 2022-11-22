<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\modules\aaa\models;

use yii\base\Model;
use app\classes\helpers\AuthHelper;
use app\modules\aaa\models\UserModel;
use yii\web\UnprocessableEntityHttpException;

class SignupForm extends Model
{
  public $input;
  public $password;

	private $_inputName = '';
  private $_user = false;

  public function rules()
  {
    return [
      ['input', 'string'],
      ['input', 'required'],

			['password', 'string', 'min' => 4],
      ['password', 'required'],
    ];
  }

  public function signup()
  {
		list ($normalizedInput, $type) = AuthHelper::recognizeLoginPhrase($this->input);

		$model = new UserModel();

		if ($type == AuthHelper::PHRASETYPE_EMAIL) {
			$this->_inputName = 'email';
			$model->usrEmail = $normalizedInput;
		} else if ($type == AuthHelper::PHRASETYPE_MOBILE) {
			$this->_inputName = 'mobile';
			$model->usrMobile = $normalizedInput;
		// } else if ($type == AuthHelper::PHRASETYPE_SSID) {
		// 	$this->_inputName = 'ssid';
		// 	$model->usrSSID = $normalizedInput;
		} else
				throw new UnprocessableEntityHttpException('Invalid input');

		$model->usrPassword = $this->password;

		if ($this->validate()) {
			if ($model->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

      $this->_user = $model;
      return true;
    }

    return false;
  }

	public function getInputName()
  {
    return $this->_inputName;
  }

	public function getUser()
  {
    return $this->_user;
  }

}
