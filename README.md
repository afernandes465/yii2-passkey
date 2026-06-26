# Yii2 Passkey

A modern Yii2 extension that adds passwordless authentication using **Passkeys (WebAuthn)**.

Supports Face ID, Touch ID, Windows Hello, Android Biometrics and hardware security keys through the WebAuthn standard.

## Features

- 🔐 Passwordless authentication
- 🍎 Face ID & Touch ID
- 🪟 Windows Hello
- 🤖 Android Biometrics
- 🔑 Hardware security keys (YubiKey, etc.)
- 🌐 WebAuthn Level 2
- 📱 Multiple devices per user
- 🏷 Device management
- 🔒 Secure challenge validation
- ⚡ Easy integration with existing Yii2 applications

## Requirements

- PHP 8.1+
- Yii2 2.0.53+
- HTTPS (required by WebAuthn)
- Modern browser with WebAuthn support

## Installation

Install via Composer.

```bash
composer require afernandes465/yii2-passkey
```

## Quick Start

Configure the module.

```php
'modules' => [
    'passkey' => [
        'class' => Afernandes\Yii2Passkey\Module::class,
    ],
],
```

Run the migrations.

```bash
php yii migrate --migrationPath=@vendor/afernandes465/yii2-passkey/src/migrations
```

Implement the identity interface in your User model.

```php
class User extends ActiveRecord implements PasskeyIdentityInterface
{
    // ...
}
```

Register a Passkey.

```php
$registrationService->createOptions($user);
```

Authenticate using a Passkey.

```php
$authenticationService->authenticate($request);
```

## Supported Authenticators

- Apple Face ID
- Apple Touch ID
- Windows Hello
- Android Biometrics
- Google Password Manager
- iCloud Keychain
- Hardware security keys (FIDO2)

## Roadmap

- [ ] Passkey registration
- [ ] Passkey authentication
- [ ] Device management
- [ ] Conditional UI
- [ ] Autofill support
- [ ] Device revocation
- [ ] Trusted device management
- [ ] PHPUnit tests
- [ ] Complete documentation

## Contributing

Pull requests are welcome.

Please open an issue first to discuss major changes.

## License

MIT
