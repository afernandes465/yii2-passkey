<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Controllers;

use Afernandes\Yii2Passkey\Factories\SerializerFactory;
use Afernandes\Yii2Passkey\Interfaces\PasskeyIdentityInterface;
use Afernandes\Yii2Passkey\Services\AuthenticationService;
use Afernandes\Yii2Passkey\Services\RegistrationService;
use RuntimeException;
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

    protected function authenticationService(): AuthenticationService
    {
        return Yii::$container->get(AuthenticationService::class);
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
                        'actions' => [
                            'registration-options',
                            'registration',
                        ],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => [
                            'authentication-options',
                            'authentication',
                        ],
                        'allow'   => true,
                        'roles'   => ['?'],
                    ],
                ],
            ],
        ];
    }

    public function actionRegistrationOptions(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->serializeResponse(
            $this->registrationService()->createOptions(
                $this->getPasskeyIdentity()
            )
        );
    }

    public function actionRegistration(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->registrationService()->register(
            $this->getPasskeyIdentity(),
            Yii::$app->request->getRawBody()
        );
    }

    public function actionAuthenticationOptions(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->serializeResponse(
            $this->authenticationService()
                ->createOptions()
        );
    }

    public function actionAuthentication(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = $this->authenticationService()->authenticate(
            Yii::$app->request->getRawBody()
        );


        $loader = $this->module->identityLoader ?? null;
        if (!$loader) {
            throw new InvalidConfigException(
                'The "identityLoader" option must be configured.'
            );
        }

        $user = ($loader)($userId);

        if (!$user instanceof \yii\web\IdentityInterface) {
            throw new InvalidConfigException(
                'The "identityLoader" callback must return an instance of yii\web\IdentityInterface.'
            );
        }

        if (!Yii::$app->user->login($user)) {
            throw new RuntimeException(
                'Unable to authenticate the user.'
            );
        }

        return [
            'authenticated' => true,
        ];
    }

    private function serializeResponse(object $object): array
    {
        return json_decode(
            $this->serializerFactory()
                ->create()
                ->serialize($object, 'json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    private function getPasskeyIdentity(): PasskeyIdentityInterface
    {
        $identity = Yii::$app->user->identity;

        if (!$identity instanceof PasskeyIdentityInterface) {
            throw new InvalidConfigException(
                sprintf(
                    'The user identity class "%s" must implement %s.',
                    get_debug_type($identity),
                    PasskeyIdentityInterface::class
                )
            );
        }

        return $identity;
    }
}