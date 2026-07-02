<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Services;


use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Session;

class SessionStorageService
{
    private const SESSION_CREATION_OPTIONS = 'passkey.creation.options';
    private const SESSION_REQUEST_OPTIONS = 'passkey.request.options';

    public function __construct(
        private readonly Session $session
    ) {
    }


    public function saveCreationOptions(PublicKeyCredentialCreationOptions $options): void
    {
        $this->save(self::SESSION_CREATION_OPTIONS, $options);
    }

    public function loadCreationOptions(): PublicKeyCredentialCreationOptions
    {
        /** @var PublicKeyCredentialCreationOptions */
        return $this->load(
            self::SESSION_CREATION_OPTIONS,
            PublicKeyCredentialCreationOptions::class,
            'Registration session expired.'
        );
    }


    public function saveRequestOptions(PublicKeyCredentialRequestOptions $requestOptions): void
    {
        $this->save(self::SESSION_REQUEST_OPTIONS, $requestOptions);
    }

    public function loadRequestOptions(): PublicKeyCredentialRequestOptions
    {

        /** @var PublicKeyCredentialRequestOptions */
        return $this->load(
            self::SESSION_CREATION_OPTIONS,
            PublicKeyCredentialRequestOptions::class,
            'Registration session expired.'
        );

    }

    private function save(string $key, object $object): void
    {
        $this->session->set($key, $object);
    }

    private function load(string $key, string $expectedClass, string $errorMessage): object
    {

        $object = $this->session->get($key);

        if (!$object instanceof $expectedClass) {
            throw new BadRequestHttpException($errorMessage);
        }

        return $object;
    }

    public function clearRequestOptions(): void
    {
        $this->session->remove(self::SESSION_REQUEST_OPTIONS);
    }

    public function clearCreationOptions(): void
    {
        $this->session->remove(self::SESSION_CREATION_OPTIONS);
    }

}