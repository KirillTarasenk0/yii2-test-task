<?php

namespace app\services;

use app\models\User;
use yii\db\Exception;
use app\contracts\UserServiceContract;

final class UserService implements UserServiceContract
{
    public function createUser(string $username, string $email, string $password, int $role): User
    {
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->setPassword($password);
        $user->role = $role;

        if (!$user->save()) {
            throw new Exception('Failed to create user: ' . implode(', ', $user->getFirstErrors()));
        }

        return $user;
    }

    public function getAllUsers(): array
    {
        return User::find()->all();
    }

    public function updateUser(User $user, array $data): bool
    {
        $user->attributes = $data;

        return $user->save();
    }

    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }

    public function findUserById(int $id): ?User
    {
        return User::findOne($id);
    }
}