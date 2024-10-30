<?php

namespace app\commands;

use app\services\UserService;
use app\services\AuthService;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\User;

class UserController extends Controller
{
    public function __construct($id, $module, private readonly UserService $userService, private readonly AuthService $authService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function actionRegister(string $username, string $email, string $password, string $role): int
    {
        $role = ($role === 'admin') ? User::ROLE_ADMIN : User::ROLE_USER;

        try {
            $user = $this->userService->createUser($username, $email, $password, $role);

            $token = $this->authService->generateToken($user);

            echo "User created successfully. Token: $token\n";
            return ExitCode::OK;
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}