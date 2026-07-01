<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey;

use Afernandes\Yii2Passkey\Factories\SerializerFactory;
use Afernandes\Yii2Passkey\Factories\WebauthnFactory;
use Afernandes\Yii2Passkey\PasskeyConfig;
use Afernandes\Yii2Passkey\Repositories\CredentialRepository;
use Afernandes\Yii2Passkey\Services\ChallengeService;
use Afernandes\Yii2Passkey\Services\RegistrationService;
use Closure;
use Yii;
use yii\base\Module as BaseModule;

class Module extends BaseModule
{

    public $controllerNamespace = 'Afernandes\\Yii2Passkey\\Controllers';

    public $defaultRoute = 'passkey';

    public PasskeyConfig $config;


    public ?Closure $identityLoader = null;


    public function init(): void
    {
        parent::init();

        $this->config ??= Yii::createObject(PasskeyConfig::class);

        $this->registerServices();

        $this->registerRoutes();
    }


    protected function registerServices(): void
    {
        $container = Yii::$container;

        $container->setSingleton(
            PasskeyConfig::class,
            fn() => $this->config
        );

        $container->setSingleton(
            ChallengeService::class
        );

        $container->setSingleton(
            CredentialRepository::class
        );

        $container->setSingleton(
            WebauthnFactory::class
        );

        $container->setSingleton(
            SerializerFactory::class
        );

        $container->setSingleton(
            RegistrationService::class,
            [],
            [
                Yii::$container->get(PasskeyConfig::class),
                Yii::$app->session,
            ]
        );
    }


    protected function registerRoutes()
    {
        if (Yii::$app->has('urlManager')) {

            Yii::$app->urlManager->addRules([

                'passkey/registration-options'
                  => 'passkey/passkey/registration-options',

                'passkey/registration'
                          => 'passkey/passkey/registration',

                'passkey/authentication-options'
                => 'passkey/passkey/authentication-options',

                'passkey/authentication'
                        => 'passkey/passkey/authentication',

            ], false);

        }
    }
}