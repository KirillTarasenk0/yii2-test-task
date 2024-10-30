<?php

namespace app\controllers;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use app\services\UserService;
use app\models\User;
use yii\rest\Controller;
use app\traits\ResponseHelperTrait;

class UserController extends Controller
{
    use ResponseHelperTrait;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function ($rule, $action) {
                        return Yii::$app->user->identity->role === User::ROLE_ADMIN;
                    }
                ],
            ],
        ];

        return $behaviors;
    }

    public function __construct($id, $module, private readonly UserService $userService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): array
    {
        $users = $this->userService->getAllUsers();
        return $this->createResponse($users);
    }

    public function actionView(int $id): array
    {
        $user = $this->userService->findUserById($id);
        return $user ? $this->createResponse($user->toArray()) : $this->createErrorResponse('User not found', 404);
    }

    public function actionCreate(): array
    {
        $request = Yii::$app->request;
        $username = $request->post('username');
        $email = $request->post('email');
        $password = $request->post('password');
        $role = $request->post('role');

        try {
            $user = $this->userService->createUser($username, $email, $password, $role);
            return $this->createResponse($user->toArray());
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage(), 400);
        }
    }

    public function actionUpdate(int $id): array
    {
        $request = Yii::$app->request;
        $user = $this->userService->findUserById($id);

        if ($user) {
            $success = $this->userService->updateUser($user, $request->bodyParams);
            return $success ? $this->createResponse($user->toArray()) : $this->createErrorResponse('Failed to update user', 400);
        }

        return $this->createErrorResponse('User not found', 404);
    }

    public function actionDelete(int $id): array
    {
        $user = $this->userService->findUserById($id);

        if ($user && $this->userService->deleteUser($user)) {
            return $this->createResponse(['message' => 'User deleted successfully']);
        }

        return $this->createErrorResponse('User not found', 404);
    }
}