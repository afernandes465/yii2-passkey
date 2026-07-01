<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Factories;

use Afernandes\Yii2Passkey\PasskeyConfig;
use Afernandes\Yii2Passkey\Repositories\CredentialRepository;
use Cose\Algorithm\Manager;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AndroidSafetyNetAttestationStatementSupport;
use Webauthn\AttestationStatement\AppleAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AttestationStatement\TpmAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManager;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;

class WebauthnFactory
{
    private ?AttestationStatementSupportManager $attestationStatementSupportManager = null;

    private ?AuthenticatorAttestationResponseValidator $attestationValidator = null;

    private ?AuthenticatorAssertionResponseValidator $assertionValidator = null;

    private ?SerializerInterface $serializer = null;



    public function __construct(
        private readonly PasskeyConfig $config
    ) {
    }


    /**
     * Cria o Serializer utilizado pela biblioteca.
     */
    public function createSerializer(): SerializerInterface
    {
        if ($this->serializer !== null) {
            return $this->serializer;
        }

        $factory = new WebauthnSerializerFactory(
            $this->createAttestationStatementSupportManager()
        );

        return $this->serializer = $factory->create();
    }

    /**
     * Cria o AttestationStatementSupportManager.
     */
    public function createAttestationStatementSupportManager(): AttestationStatementSupportManager
    {
        if ($this->attestationStatementSupportManager !== null) {
            return $this->attestationStatementSupportManager;
        }

        $manager = AttestationStatementSupportManager::create();

        $manager->add(new NoneAttestationStatementSupport());
        $manager->add(new PackedAttestationStatementSupport(
            new Manager()
        ));
        // $manager->add(new FidoU2FAttestationStatementSupport());
        // $manager->add(new AndroidKeyAttestationStatementSupport());
        // $manager->add(new TpmAttestationStatementSupport());
        // $manager->add(new AppleAttestationStatementSupport());

        if ($this->config->enableSafetyNet) {
            $manager->add(
                new AndroidSafetyNetAttestationStatementSupport()
            );
        }

        return $this->attestationStatementSupportManager = $manager;
    }


    private function createCeremonyFactory(): CeremonyStepManagerFactory
    {
        $factory = new CeremonyStepManagerFactory();

        $factory->setAttestationStatementSupportManager(
            $this->createAttestationStatementSupportManager()
        );

        $factory->setSecuredRelyingPartyId([
            $this->config->rpId,
        ]);

        return $factory;
    }


    public function createCreationCeremony(): CeremonyStepManager
    {
        return $this->createCeremonyFactory()
            ->creationCeremony();
    }

    public function createRequestCeremony(): CeremonyStepManager
    {
        return $this->createCeremonyFactory()
            ->requestCeremony();
    }

    public function createAttestationValidator(): AuthenticatorAttestationResponseValidator
    {
        if ($this->attestationValidator !== null) {
            return $this->attestationValidator;
        }

        return $this->attestationValidator =
            new AuthenticatorAttestationResponseValidator(
                ceremonyStepManager: $this->createCreationCeremony()
            );
    }

    public function createAssertionValidator(
        CredentialRepository $credentialRepository
    ): AuthenticatorAssertionResponseValidator {

        if ($this->assertionValidator !== null) {
            return $this->assertionValidator;
        }

        return $this->assertionValidator =
            new AuthenticatorAssertionResponseValidator(
                publicKeyCredentialSourceRepository: $credentialRepository,
                ceremonyStepManager: $this->createRequestCeremony()
            );
    }
}