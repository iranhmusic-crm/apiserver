<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

// class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface
class User extends ActiveRecord implements IdentityInterface
{
  public static function tableName()
  {
    return '{{%User}}';
  }

  public function rules()
  {
    return [
      ['usrEmail', 'string', 'max' => 255],
      ['usrEmail', 'email'],

      ['usrMobile', 'string', 'max' => 32],
      ['usrMobile', 'required'],

    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function findIdentity($id)
  {
    return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
  }

  /**
   * {@inheritdoc}
   */
  public static function findIdentityByAccessToken($token, $type = null)
  {
    foreach (self::$users as $user) {
      if ($user['accessToken'] === $token) {
        return new static($user);
      }
    }

    return null;
  }

  public static function findByLoginPhrase($loginPhrase)
  {
    this->find()
      ->where()

      $loginPhrase

    return null;
  }

  /**
   * Finds user by username
   *
   * @param string $username
   * @return static|null
   */
  public static function findByUsername($username)
  {
    foreach (self::$users as $user) {
      if (strcasecmp($user['username'], $username) === 0) {
        return new static($user);
      }
    }

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthKey()
  {
    return $this->authKey;
  }

  /**
   * {@inheritdoc}
   */
  public function validateAuthKey($authKey)
  {
    return $this->authKey === $authKey;
  }

  /**
   * Validates password
   *
   * @param string $password password to validate
   * @return bool if password provided is valid for current user
   */
  public function validatePassword($password)
  {
    return $this->password === $password;
  }
}
