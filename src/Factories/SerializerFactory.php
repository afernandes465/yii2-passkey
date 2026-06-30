<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Factories;

use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\Denormalizer\WebauthnSerializerFactory;

final class SerializerFactory
{
    public function __construct(
        private readonly AttestationStatementSupportManager $attestationStatementSupportManager
    ) {
    }

    public function create(): SerializerInterface
    {
        return (new WebauthnSerializerFactory(
            $this->attestationStatementSupportManager
        ))->create();
    }
}