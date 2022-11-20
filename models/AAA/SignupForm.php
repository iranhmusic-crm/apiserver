<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace app\models\AAA;

use yii\base\Model;
use app\classes\helpers\AuthHelper;
use app\models\AAA\UserModel;
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

			['password', 'string'],
      ['password', 'required'],
    ];
  }

  public function signup()
  {
		list ($nomalizedInput, $type) = AuthHelper::recognizeAndNormalizeEmailOrMobileOrSSID($this->input);

		$model = new UserModel();

		if ($type == 'E') {
			$this->_inputName = 'email';
			$model->usrEmail = $nomalizedInput;
		} else if ($type == 'M') {
			$this->_inputName = 'mobile';
			$model->usrMobile = $nomalizedInput;
		// } else if ($type == 'S') {
		// 	$this->_inputName = 'ssid';
		// 	$model->usrSSID = $nomalizedInput;
		} else
				throw new UnprocessableEntityHttpException('Invalid input');

		$model->usrPasswordHash = $this->password;

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
