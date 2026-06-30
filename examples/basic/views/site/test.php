<?php

use Afernandes\Yii2Passkey\Assets\PasskeyAsset;

PasskeyAsset::register($this);


?>



<button id="register" class="btn btn-outline-secondary">
    Registar Passkey
</button>

<pre id="result"></pre>

<script>
    document.getElementById('register').onclick = async () => {

        try {

            const result = await Passkey.register({
                registrationOptionsUrl: <?= json_encode(
                    \yii\helpers\Url::to(['/passkey/passkey/registration-options'])
                ) ?>,

                registrationUrl: <?= json_encode(
                    \yii\helpers\Url::to(['/passkey/passkey/registration'])
                ) ?>
            });

            console.log(result);

            document.getElementById('result').textContent =
                JSON.stringify(result, null, 4);

        } catch (e) {

            console.error(e);

            alert(e.message);
        }

    };
</script>