<?php

namespace app\controllers;

use Yii;
use app\services\AuthService;
use app\traits\ResponseHelperTrait;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

class AuthController extends Controller
{
    use ResponseHelperTrait;

    public function __construct($id, $module, private readonly AuthService $authService, $config = [],)
    {
        parent::__construct($id, $module, $config);
    }

    public function actionLogin(): array
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            throw new BadRequestHttpException('Only POST requests are allowed.');
        }

        $bodyParams = $request->getBodyParams();
        $email = $bodyParams['email'] ?? null;
        $password = $bodyParams['password'] ?? null;

        if (!$email || !$password) {
            throw new BadRequestHttpException('Email and password are required.');
        }

        try {
            $token = $this->authService->authenticate($email, $password);
            return $this->createResponse(['token' => $token]);
        } catch (UnauthorizedHttpException $e) {
            Yii::error("Login failed: {$e->getMessage()}");
            Yii::$app->response->statusCode = 401;
            return $this->createErrorResponse('Invalid credentials.', 401);
        } catch (\Exception $e) {
            Yii::error("Authentication error: {$e->getMessage()}");
            Yii::$app->response->statusCode = 500;
            return $this->createErrorResponse('An error occurred during authentication.', 500);
        }
    }

    public function actionMe(): array
    {
        $user = $this->authService->getAuthenticatedUser();

        if ($user === null) {
            return $this->createErrorResponse('Unauthorized', 401);
        }

        return $this->createResponse($user->toArray());
    }
}
