<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Repositories;

use Afernandes\Yii2Passkey\Factories\SerializerFactory;
use Afernandes\Yii2Passkey\Models\ActiveRecord\Passkey;
use ParagonIE\ConstantTime\Base64UrlSafe;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;
use yii\helpers\Json;

class CredentialRepository implements PublicKeyCredentialSourceRepository
{


    private ?SerializerInterface $serializer = null;

    public function __construct(
        private readonly SerializerFactory $serializerFactory,
    ) {
    }


    private function serializer(): SerializerInterface
    {
        return $this->serializer ??=
            $this->serializerFactory->create();
    }


    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        $passkey = $this->findPasskeyByCredentialId(
            $publicKeyCredentialId
        );

        if ($passkey === null) {
            return null;
        }

        return $this->deserializeSource($passkey->source);
    }


    public function findPasskeyByCredentialId(
        string $publicKeyCredentialId,
        bool $onlyEnabled = true
    ): ?Passkey {

        $query = Passkey::find()
            ->where([
                'credential_id' => $this->encodeCredentialId($publicKeyCredentialId),
            ]);

        if ($onlyEnabled) {
            $query->andWhere([
                'enabled' => Passkey::STATUS_ENABLED,
            ]);
        }

        return $query->one();

    }


    public function findAllForUserEntity(PublicKeyCredentialUserEntity $user): array
    {
        $passkeys = Passkey::find()
            ->where([
                'user_id' => $user->id,
                'enabled' => Passkey::STATUS_ENABLED,
            ])
            ->all();

        $result = [];

        foreach ($passkeys as $passkey) {
            $result[] = $this->deserializeSource($passkey->source);
        }

        return $result;
    }

    public function saveCredentialSource(
        PublicKeyCredentialSource $publicKeyCredentialSource
    ): void {

        $passkey = new Passkey();

        $passkey->user_id = $publicKeyCredentialSource->userHandle;

        $passkey->credential_id = $this->encodeCredentialId(
            $publicKeyCredentialSource->publicKeyCredentialId
        );

        $passkey->source = $this->serializeSource($publicKeyCredentialSource);

        $passkey->enabled = Passkey::STATUS_ENABLED;

        if (!$passkey->save()) {
            throw new RuntimeException(
                Json::encode($passkey->errors)
            );
        }
    }

    public function updateCredential(
        PublicKeyCredentialSource $source
    ): void {

        $passkey = $this->findPasskeyByCredentialId(
            $source->publicKeyCredentialId
        );

        if ($passkey === null) {
            throw new RuntimeException(
                'Credential not found.'
            );
        }

        $passkey->source = $this->serializeSource($source);

        $passkey->last_used_at = date('Y-m-d H:i:s');

        if (
            !$passkey->save(false, [
                'source',
                'last_used_at',
            ])
        ) {
            throw new RuntimeException(
                Json::encode($passkey->errors)
            );
        }
    }

    public function deleteCredentialSource(string $credentialId): bool
    {
        $passkey = $this->findPasskeyByCredentialId(
            $credentialId
        );

        if ($passkey === null) {
            return false;
        }

        if (!$passkey->delete()) {
            throw new RuntimeException(
                Json::encode($passkey->errors)
            );
        }

        return true;
    }


    public function touchCredential(string $credentialId): void
    {

        $passkey = $this->findPasskeyByCredentialId(
            $credentialId
        );

        if ($passkey === null) {
            throw new RuntimeException(
                'Credential not found.'
            );
        }

        $passkey->touch();
    }


    private function serializeSource(PublicKeyCredentialSource $source): string
    {

        return $this->serializer()->serialize(
            $source,
            'json'
        );
    }

    private function deserializeSource(string $json): PublicKeyCredentialSource
    {

        return $this->serializer()->deserialize(
            $json,
            PublicKeyCredentialSource::class,
            'json'
        );
    }

    private function encodeCredentialId(string $id): string
    {
        return Base64UrlSafe::encodeUnpadded($id);
    }
}