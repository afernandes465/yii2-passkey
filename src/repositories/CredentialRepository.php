<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Repositories;

use Afernandes\Yii2Passkey\Models\Passkey;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

class CredentialRepository implements PublicKeyCredentialSourceRepository
{
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        $passkey = Passkey::find()
            ->where([
                'credential_id' => base64_encode($publicKeyCredentialId),
                'enabled' => 1,
            ])
            ->one();

        if ($passkey === null) {
            return null;
        }

        return $this->unserialize($passkey->source);
    }

    public function findAllForUserEntity(PublicKeyCredentialUserEntity $user): array
    {
        $passkeys = Passkey::find()
            ->where([
                'user_id' => $user->id,
                'enabled' => 1,
            ])
            ->all();

        $result = [];

        foreach ($passkeys as $passkey) {
            $result[] = $this->unserialize($passkey->source);
        }

        return $result;
    }

    public function saveCredentialSource(
        PublicKeyCredentialSource $publicKeyCredentialSource
    ): void {

        $passkey = new Passkey();

        $passkey->user_id = $publicKeyCredentialSource->userHandle;

        $passkey->credential_id = base64_encode(
            $publicKeyCredentialSource->publicKeyCredentialId
        );

        $passkey->source = $this->serialize($publicKeyCredentialSource);

        $passkey->enabled = true;

        $passkey->save(false);
    }

    protected function serialize(
        PublicKeyCredentialSource $source
    ): string {
        return json_encode(
            $source,
            JSON_THROW_ON_ERROR
        );
    }

    protected function unserialize(
        string $json
    ): PublicKeyCredentialSource {

        throw new \RuntimeException(
            'To be implemented.'
        );
    }
}