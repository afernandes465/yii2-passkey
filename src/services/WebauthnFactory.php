<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Services;

use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AndroidSafetyNetAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AttestationStatement\TpmAttestationStatementSupport;
use Webauthn\AttestationStatement\AppleAttestationStatementSupport;
use Webauthn\Denormalizer\WebauthnSerializerFactory;

class WebauthnFactory
{
    private ?AttestationStatementSupportManager $attestationStatementSupportManager = null;

    private ?SerializerInterface $serializer = null;

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
        $manager->add(new PackedAttestationStatementSupport());
        $manager->add(new FidoU2FAttestationStatementSupport());
        $manager->add(new AndroidKeyAttestationStatementSupport());
        $manager->add(new AndroidSafetyNetAttestationStatementSupport());
        $manager->add(new TpmAttestationStatementSupport());
        $manager->add(new AppleAttestationStatementSupport());

        return $this->attestationStatementSupportManager = $manager;
    }
}