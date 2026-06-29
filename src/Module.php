<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey;

use Afernandes\Yii2Passkey\PasskeyConfig;
use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public const VERSION = '0.1.0';

    /**
     * Namespace dos controllers.
     */
    public $controllerNamespace = 'Afernandes\\Yii2Passkey\\Controllers';

    public PasskeyConfig $config;

    public function init(): void
    {
        parent::init();
    }
}