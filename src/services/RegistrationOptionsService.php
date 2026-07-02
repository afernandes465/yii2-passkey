<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Services;

use Yii;
use yii\web\Session;

class RegistrationOptionsService
{
    private const SESSION_KEY = 'passkey.registration.options';

    public function __construct(
        private ?Session $session = null
    ) {
        $this->session ??= Yii::$app->session;
    }


    /**
     * Salva o valor.
     */
    public function set($options): void
    {
        $this->session->set(
            self::SESSION_KEY,
            $options
        );
    }


    /**
     * Obtém o valor atual.
     */
    public function get(): ?string
    {
        $options = $this->session->get(self::SESSION_KEY);

        if ($options === null) {
            return null;
        }

        return $options;
    }

    /**
     * Remove o valor.
     */
    public function clear(): void
    {
        $this->session->remove(self::SESSION_KEY);
    }



}