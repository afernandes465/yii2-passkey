<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Services;

use Exception;
use Yii;
use yii\web\Session;

class SessionStorageService
{
    private const SESSION_KEY = 'passkey.storage';

    public function __construct(
        private ?Session $session = null
    ) {
        $this->session ??= Yii::$app->session;
    }


    /**
     * Salva o valor.
     */
    public function save($options): void
    {
        $this->session->set(
            self::SESSION_KEY,
            $options
        );
    }

    /**
     * Obtém o valor atual.
     */
    public function load(): ?string
    {
        $options = $this->session->get(self::SESSION_KEY);

        if ($options === null) {
            throw new Exception("Key not created");

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