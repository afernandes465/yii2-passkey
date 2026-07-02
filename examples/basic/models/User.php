<?php

namespace app\models;

use Afernandes\Yii2Passkey\Interfaces\PasskeyIdentityInterface;

class User extends \yii\base\BaseObject implements PasskeyIdentityInterface
{
    public $id;
    public $email;
    public $name;
    public $password;
    public $authKey;
    public $accessToken;

    private static $users = [
        '100' => [
            'id'          => '100',
            'email'       => 'admin@mail.void',
            'name'        => 'Admin',
            'password'    => 'admin',
            'authKey'     => 'test100key',
            'accessToken' => '100-token',
        ],
        '101' => [
            'id'          => '101',
            'email'       => 'staff@mail.void',
            'name'        => 'Staff',
            'password'    => 'staff',
            'authKey'     => 'test101key',
            'accessToken' => '101-token',
        ],
    ];


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

    /**
     * Finds user by username
     *
     * @param string $email
     * @return static|null
     */
    public static function findByUsername($email)
    {
        foreach (self::$users as $user) {
            if (strcasecmp($user['email'], $email) === 0) {
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

    /**
     * @inheritDoc
     */
    public function getPasskeyDisplayName(): string
    {
        //Nome a mostrar
        return (string) $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getPasskeyId(): string
    {
        return (string) $this->getId();
    }

    /**
     * @inheritDoc
     */
    public function getPasskeyName(): string
    {
        //Email
        return (string) $this->email;
    }
}
