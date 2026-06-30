<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Controllers;

use Afernandes\Yii2Passkey\Factories\SerializerFactory;
use Afernandes\Yii2Passkey\Interfaces\PasskeyIdentityInterface;
use Afernandes\Yii2Passkey\Services\RegistrationService;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;


class PasskeyController extends Controller
{
    protected function registrationService(): RegistrationService
    {
        return Yii::$container->get(RegistrationService::class);
    }

    protected function serializerFactory(): SerializerFactory
    {
        return Yii::$container->get(SerializerFactory::class);
    }

    public function behaviors(): array
    {

        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => [
                    'registration-options',
                    'registration'
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionRegistrationOptions(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $identity = Yii::$app->user->identity;

        if (!$identity instanceof PasskeyIdentityInterface) {
            throw new InvalidConfigException(
                'User identity must implement PasskeyIdentityInterface.'
            );
        }

        $options = $this->registrationService()->createOptions(
            $identity
        );

        $json = $this->serializerFactory()
            ->create()
            ->serialize($options, 'json');

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }


    public function actionRegistration(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->registrationService()->register(
            Yii::$app->user->identity,
            Yii::$app->request->getRawBody()
        );
    }

    public function actionAuthenticationOptions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // TODO

        return $this->asJson([]);
    }

    public function actionAuthentication()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // TODO

        return $this->asJson([]);
    }
}