<?php

namespace app\services;

use app\models\User;
use app\models\Token;
use Yii;
use yii\web\UnauthorizedHttpException;
use yii\db\Exception;
use app\contracts\AuthServiceContract;

final class AuthService implements AuthServiceContract
{
    public function authenticate(string $email, string $password): string
    {
        $user = $this->findUserByEmail($email);

        if (!$user || !$this->validateUserPassword($user, $password)) {
            throw new UnauthorizedHttpException('Invalid credentials');
        }

        return $this->generateToken($user);
    }

    private function findUserByEmail(string $email): ?User
    {
        return User::findOne(['email' => $email]);
    }

    private function validateUserPassword(User $user, string $password): bool
    {
        return $user->validatePassword($password);
    }

    private function generateToken(User $user): string
    {
        $token = Yii::$app->security->generateRandomString();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $tokenModel = new Token();
        $tokenModel->user_id = $user->id;
        $tokenModel->token = $token;
        $tokenModel->expires_at = $expiresAt;

        if (!$tokenModel->save()) {
            throw new Exception('Failed to generate token');
        }

        return $token;
    }

    public function getAuthenticatedUser(): ?User
    {
        $tokenString = $this->extractBearerToken();

        if ($tokenString) {
            $token = Token::findOne(['token' => $tokenString]);
            if ($token && !$token->isExpired()) {
                return User::findOne($token->user_id);
            }
        }

        return null;
    }

    private function extractBearerToken(): ?string
    {
        $authHeader = Yii::$app->request->getHeaders()->get('Authorization');
        return $authHeader ? str_replace('Bearer ', '', $authHeader) : null;
    }
}
