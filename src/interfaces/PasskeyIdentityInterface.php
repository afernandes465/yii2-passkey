<?php
declare(strict_types=1);

namespace Afernandes\Yii2Passkey\interfaces;


use yii\web\IdentityInterface;

/**
 * Interface que deve ser implementada pelo modelo de utilizador
 * que pretende utilizar autenticação por Passkeys.
 */
interface PasskeyIdentityInterface extends IdentityInterface
{
    /**
     * Identificador único do utilizador.
     *
     * Este valor será utilizado como User Handle no WebAuthn.
     * Deve ser único e nunca mudar durante a vida da conta.
     */
    public function getPasskeyId(): string;

    /**
     * Nome único do utilizador.
     *
     * Normalmente o email ou username.
     */
    public function getPasskeyName(): string;

    /**
     * Nome apresentado ao utilizador quando cria uma Passkey.
     */
    public function getPasskeyDisplayName(): string;
}