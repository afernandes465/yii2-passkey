<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Services;

use Afernandes\Yii2Passkey\Factories\SerializerFactory;
use Afernandes\Yii2Passkey\Factories\WebauthnFactory;
use Afernandes\Yii2Passkey\PasskeyConfig;
use Afernandes\Yii2Passkey\Repositories\CredentialRepository;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;
use yii\web\BadRequestHttpException;
use yii\web\Session;

class AuthenticationService
{
    public function __construct(
        private readonly PasskeyConfig $config,
        private readonly Session $session,
        private readonly SerializerFactory $serializerFactory,
        private readonly WebauthnFactory $webauthnFactory,
        private readonly CredentialRepository $credentialRepository,
    ) {
    }

    public function createOptions(): PublicKeyCredentialRequestOptions
    {
        $challenge = random_bytes(32);

        $options = new PublicKeyCredentialRequestOptions(
            challenge: $challenge,
            timeout: $this->config->timeout,
            rpId: $this->config->rpId,
        );

        $this->session->set(
            'passkey.authentication.options',
            $options
        );

        return $options;
    }

    public function authenticate(
        string $payload
    ): string {

        $requestOptions = $this->session->get(
            'passkey.authentication.options'
        );

        if (!$requestOptions instanceof PublicKeyCredentialRequestOptions) {
            throw new BadRequestHttpException(
                'Authentication session expired.'
            );
        }

        $credential = $this->serializerFactory
            ->create()
            ->deserialize(
                $payload,
                PublicKeyCredential::class,
                'json'
            );

        if (!$credential instanceof PublicKeyCredential) {
            throw new BadRequestHttpException(
                'Invalid public key credential.'
            );
        }

        $response = $credential->response;

        if (!$response instanceof AuthenticatorAssertionResponse) {
            throw new BadRequestHttpException(
                'Invalid authenticator response.'
            );
        }

        $validator = $this->webauthnFactory
            ->createAssertionValidator(
                $this->credentialRepository
            );

        try {
            $credentialSource = $validator->check(
                $credential->rawId,
                $response,
                $requestOptions,
                $this->config->rpId,
                null
            );

        } catch (\Throwable $e) {

            throw new BadRequestHttpException(
                $e->getMessage(),
                0,
                $e
            );

        }

        $this->credentialRepository
            ->updateCredential($credentialSource);


        $this->session->remove(
            'passkey.authentication.options'
        );

        return $credentialSource->userHandle;
    }
}