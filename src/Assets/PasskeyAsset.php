<?php

namespace Afernandes\Yii2Passkey\Assets;

use yii\web\AssetBundle;

class PasskeyAsset extends AssetBundle
{
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
}