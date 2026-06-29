<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Services;

use Yii;
use yii\web\Session;

class ChallengeService
{
    private const SESSION_KEY = '__passkey_challenge';

    public function __construct(
        private ?Session $session = null
    ) {
        $this->session ??= Yii::$app->session;
    }

    /**
     * Gera um novo challenge e guarda-o na sessão.
     */
    public function generate(int $length = 32): string
    {
        $challenge = random_bytes($length);

        $this->session->set(
            self::SESSION_KEY,
            base64_encode($challenge)
        );

        return $challenge;
    }

    /**
     * Obtém o challenge atual.
     */
    public function get(): ?string
    {
        $challenge = $this->session->get(self::SESSION_KEY);

        if ($challenge === null) {
            return null;
        }

        return base64_decode($challenge);
    }

    /**
     * Remove o challenge.
     */
    public function clear(): void
    {
        $this->session->remove(self::SESSION_KEY);
    }

    /**
     * Obtém e remove o challenge.
     */
    public function pull(): ?string
    {
        $challenge = $this->get();

        $this->clear();

        return $challenge;
    }
}