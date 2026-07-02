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


class AuthenticationService
{
    public function __construct(
        private readonly PasskeyConfig $config,
        private readonly ChallengeService $challengeService,
        private readonly SerializerFactory $serializerFactory,
        private readonly WebauthnFactory $webauthnFactory,
        private readonly CredentialRepository $credentialRepository,
    ) {
    }

    public function createOptions(): PublicKeyCredentialRequestOptions
    {
        $options = new PublicKeyCredentialRequestOptions(
            challenge: $this->challengeService->generate(),
            timeout: $this->config->timeout,
            rpId: $this->config->rpId,
        );

        return $options;
    }

    public function authenticate(string $payload): string
    {

        $challenge = $this->challengeService->get();

        if ($challenge === null) {
            throw new BadRequestHttpException(
                'Authentication session expired.'
            );
        }

        $requestOptions = new PublicKeyCredentialRequestOptions(
            challenge: $challenge,
            timeout: $this->config->timeout,
            rpId: $this->config->rpId,
        );

        try {

            $credential = $this->deserializeCredential($payload);
            $response = $this->getAssertionResponse($credential);

            $validator = $this->webauthnFactory->createAssertionValidator(
                $this->credentialRepository
            );

            $credentialSource = $validator->check(
                $credential->rawId,
                $response,
                $requestOptions,
                $this->config->rpId,
                null
            );
 
            return $credentialSource->userHandle;

        } finally {

            $this->challengeService->clear();

        }
    }

    private function deserializeCredential(string $payload): PublicKeyCredential
    {

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

        return $credential;
    }

    private function getAssertionResponse(
        PublicKeyCredential $credential
    ): AuthenticatorAssertionResponse {
        if (!$credential->response instanceof AuthenticatorAssertionResponse) {
            throw new BadRequestHttpException(
                'Invalid authenticator response.'
            );
        }

        return $credential->response;
    }
}