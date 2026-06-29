<?php
namespace Afernandes\Yii2Passkey;

class PasskeyConfig
{
    public string $rpName;

    public string $rpId;

    public string $origin;

    public int $timeout = 60000;

    public bool $requireUserVerification = true;
}