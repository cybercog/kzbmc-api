<?php

namespace app\models;

use yii\web\ForbiddenHttpException;
use Yii;
use JWT;

class User extends \yii\base\Object implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;

    private static $users = [
        '100' => [
            'id' => '100',
            'username' => 'admin',
            'password' => 'admin',
            'authKey' => 'test100key',
            'accessToken' => '100-token',
        ],
        '101' => [
            'id' => '101',
            'username' => 'demo',
            'password' => 'demo',
            'authKey' => 'test101key',
            'accessToken' => '101-token',
        ],
    ];

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
    	if(!Yii::$app->request->getHeaders()->has('Authorization')) {
    		throw new ForbiddenHttpException('Not allowed', 403);
    	}
    	
    	$token = Yii::$app->request->getHeaders()->get('Authorization');
    	$token = str_replace('Bearer ', '', $token);
    	
    	try {
    		$tokenData = JWT::decode($token, Yii::$app->params['jwt_key']);
    	}
    	catch(\Exception $e) {
    		throw new ForbiddenHttpException('Not allowed', 403);
    	}
    	
    	// validate the token data
    	//TODO implement token data validation, browser, IP, expiration time...
    	if($tokenData->iat == '1356999524' && $tokenData->iss == 'http://example.org') {
    		return new static(self::$users[100]);
    	}
    	
        /*foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }*/

        return null;
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
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
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
