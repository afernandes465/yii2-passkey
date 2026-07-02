<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Services;

use Afernandes\Yii2Passkey\Factories\SerializerFactory;
use Afernandes\Yii2Passkey\Factories\WebauthnFactory;
use Afernandes\Yii2Passkey\Repositories\CredentialRepository;
use Afernandes\Yii2Passkey\Interfaces\PasskeyIdentityInterface;
use Afernandes\Yii2Passkey\PasskeyConfig;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredential;
use yii\web\BadRequestHttpException;



class RegistrationService
{
    public function __construct(
        private readonly PasskeyConfig $config,
        private readonly SessionStorageService $storage,
        private readonly SerializerFactory $serializerFactory,
        private readonly WebauthnFactory $webauthnFactory,
        private readonly CredentialRepository $credentialRepository,
    ) {
    }

    public function createOptions(
        PasskeyIdentityInterface $identity
    ): PublicKeyCredentialCreationOptions {

        $challenge = random_bytes(32);

        $rp = new PublicKeyCredentialRpEntity(
            $this->config->rpName,
            $this->config->rpId
        );

        $user = new PublicKeyCredentialUserEntity(
            $identity->getPasskeyName(),
            $identity->getPasskeyId(),
            $identity->getPasskeyDisplayName()
        );

        $algorithms = [];

        foreach ($this->config->algorithms as $algorithm) {
            $algorithms[] = PublicKeyCredentialParameters::createPk($algorithm);
        }

        $selection = new AuthenticatorSelectionCriteria(
            authenticatorAttachment: $this->config->authenticatorAttachment,
            residentKey: $this->config->residentKey,
            userVerification: $this->config->requireUserVerification
            ? AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED
            : AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED
        );

        $options = new PublicKeyCredentialCreationOptions(
            rp: $rp,
            user: $user,
            challenge: $challenge,
            pubKeyCredParams: $algorithms,
            authenticatorSelection: $selection,
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            timeout: $this->config->timeout
        );

        $this->storage->saveCreationOptions($options);

        return $options;
    }


    public function register(
        PasskeyIdentityInterface $user,
        string $payload
    ): array {

        $creationOptions = $this->storage->loadCreationOptions();

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

        if (!$response instanceof AuthenticatorAttestationResponse) {
            throw new BadRequestHttpException(
                'Invalid authenticator response.'
            );
        }

        $validator = $this->webauthnFactory
            ->createAttestationValidator();

        try {

            $source = $validator->check(
                $response,
                $creationOptions,
                $this->config->rpId
            );

        } catch (\Throwable $e) {

            throw new BadRequestHttpException(
                $e->getMessage(),
                0,
                $e
            );

        }

        $this->credentialRepository
            ->saveCredentialSource($source);

        $this->storage->clearCreationOptions();

        return [
            'success' => true,
            'message' => 'Passkey registered successfully.',
        ];

    }

}