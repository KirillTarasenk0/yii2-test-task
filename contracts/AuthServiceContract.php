<?php

namespace app\contracts;

use app\models\User;

interface AuthServiceContract
{
    public function authenticate(string $email, string $password): string;
    public function getAuthenticatedUser(): ?User;
}