<?php

namespace Afernandes\Yii2Passkey\Assets;

use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\AssetBundle;

class PasskeyAsset extends AssetBundle
{
    //public $sourcePath = __DIR__ . '/../../resources';
    public $sourcePath = '@vendor/afernandes465/yii2-passkey/src/Resources';

    public $js = [
        'js/passkey.js',
    ];

    public $jsOptions = [
        'id' => 'yii2-passkey',
    ];

    public $depends = [
        \yii\web\YiiAsset::class,
    ];

    public static function register($view): self
    {
        /** @var self $asset */
        $asset = parent::register($view);

        $view->registerJs(sprintf(
            'Passkey.configure(%s);',
            Json::encode([
                'registrationOptionsUrl'   => Url::to(['/passkey/passkey/registration-options']),
                'registrationUrl'          => Url::to(['/passkey/passkey/registration']),
                'authenticationOptionsUrl' => Url::to(['/passkey/passkey/authentication-options']),
                'authenticationUrl'        => Url::to(['/passkey/passkey/authentication']),
            ])
        ));

        return $asset;
    }
}