<?php

namespace app\contracts;

use app\models\User;

interface UserServiceContract
{
    public function createUser(string $username, string $email, string $password, int $role): User;
    public function getAllUsers(): array;
    public function updateUser(User $user, array $data): bool;
    public function deleteUser(User $user): bool;
    public function findUserById(int $id): ?User;
}