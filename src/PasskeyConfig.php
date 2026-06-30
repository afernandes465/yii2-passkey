<?php
namespace Afernandes\Yii2Passkey;

use Webauthn\AuthenticatorSelectionCriteria;

final class PasskeyConfig extends \yii\base\BaseObject
{
    public string $rpName;

    public string $rpId;

    public string $origin;

    public int $timeout = 60000;

    public bool $requireUserVerification = true;

    public string $residentKey =
        AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED;

    public ?string $authenticatorAttachment =
        AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM;

    public array $algorithms = [
        -7,   // ES256
        -257, // RS256
    ];
}