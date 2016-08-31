<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;

/**
 * This is the model class for table "User".
 *
 * @property integer $userId
 * @property string $username
 * @property string $passwordHash
 * @property string $passwordResetToken
 * @property string $status
 * @property string $type
 *
 * @property string $id User.userId (read-only)
 * @property string $password Property for setting up the password
 *
 * @author Anatoly Milkov <anatoly.milko@gmail.com>
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    /**
     * Status constats.
     */
    const STATUS_ACTIVE      = 'active';
    const STATUS_UNCONFIRMED = 'unconfirmed';

    /**
     * @var string
     */
    private $_password;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'User';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['username'], 'required',
                'on' => 'edit'
            ],
            [
                'username', 'unique',
                'on' => 'create',
            ],
            [
                ['username'], 'string',
                'max' => 64,
                'on'  => ['create', 'edit'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->userId;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not implemented.');
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        throw new NotSupportedException('"' . __METHOD__ . '" is not implemented.');
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Generates password hash from password and sets it to the model.
     * @param string $password
     */
    public function setPassword($password)
    {
        if (!empty($password)) {
            $this->_password    = $password;
            $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
        }
    }

    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->passwordResetToken = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token.
     */
    public function removePasswordResetToken()
    {
        $this->passwordResetToken = null;
    }

    /**
     * Finds user by password reset token.
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                'passwordResetToken' => $token,
        ]);
    }

    /**
     * Finds out if password reset token is valid.
     * @param string $token Password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire    = 3600 * 24; // 24 hours
        $parts     = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * Sends recovery message.
     * @return boolean
     */
    public function recoveryQuery()
    {
        $this->generatePasswordResetToken();

        if ($this->save(false)) {

            $subject          = $this->scenario === 'create' ? 'Добро пожаловать!' : 'Восстановление доступа';
            $verificationLink = Yii::$app->urlManager->createAbsoluteUrl([
                'site/login',
                'passwordResetToken' => $this->passwordResetToken,
            ]);

            switch ($this->scenario) {
                case 'create':
                    $subject = 'Добро пожаловать!';
                    $text    = 'Для продолжения регистрации пройдите по ссылке '
                        . $verificationLink;
                    break;
                default:
                    $subject = 'Восстановление доступа';
                    $text    = 'Для восстановления доступа и входа в систему пройдите по ссылке '
                        . $verificationLink;
                    break;
            }

            // Send email to $this user
            return Yii::$app->mailer->compose()
                    ->setFrom(Yii::$app->params['adminEmail'])
                    ->setTo($this->username)
                    ->setSubject($subject)
                    ->setTextBody($text)
                    ->send();
        }
        return false;
    }

    /**
     * Creates an user.
     * @param string $username
     * @return \app\models\User|null
     */
    public static function createUser($username)
    {
        $user           = new User;
        $user->scenario = 'create';
        $user->username = $username;
        $user->status   = static::STATUS_UNCONFIRMED;
        if ($user->save()) {
            return $user;
        }
        return null;
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        return Yii::$app->user->login($this);
    }

}
