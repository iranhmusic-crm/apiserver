<?php

namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $loginPhrase;
    public $password;
    public $salt;
    public $rememberMe = true;

    private $_user = false;

    public function rules()
    {
        return [
            ['loginPhrase', 'required'],

            ['password', 'required'],
            ['password', 'validatePassword'],

            ['salt', 'required'],

            ['rememberMe', 'boolean'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    public function getUser()
    {
        if ($this->_user === false)
            $this->_user = User::findByLoginPhrase($this->loginPhrase);

        return $this->_user;
    }

    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

}
