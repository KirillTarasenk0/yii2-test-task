<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property int $role
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Token[] $tokens
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const int ROLE_ADMIN = 1;
    public const int ROLE_USER = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'user';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'email', 'password_hash', 'role'], 'required'],
            ['email', 'email'],
            ['email', 'unique'],
            ['role', 'in', 'range' => [self::ROLE_ADMIN, self::ROLE_USER]],
            [['username', 'password_hash'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'password_hash' => 'Password Hash',
            'role' => 'Role',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Tokens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTokens(): ActiveQuery
    {
        return $this->hasMany(Token::class, ['user_id' => 'id']);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $userToken = Token::find()
            ->where(['token' => $token])
            ->andWhere(['>', 'expires_at', date('Y-m-d H:i:s')])
            ->one();

        return $userToken ? $userToken->user : null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey): bool
    {
        return false;
    }
}
