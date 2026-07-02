<?php

use Afernandes\Yii2Passkey\Assets\PasskeyAsset;

PasskeyAsset::register($this);

?>

<button id="register">
    Registar Passkey
</button>

<button id="login">
    Entrar com Passkey
</button>

<script>

    document.getElementById('register').onclick = async () => {

        try {

            const result = await Passkey.register();

            console.log(result);

        } catch (e) {

            console.error(e);

            alert(e.message);

        }

    };

    document.getElementById('login').onclick = async () => {

        try {

            Passkey.configure({
                onRegistered() {
                    alert('Registo feito com sucesso');
                },
                onAuthenticated() {

                    location.reload();
                },
                onError() {
                    alert('Erro no processo');
                }
            });

            const result = await Passkey.authenticate();

            console.log(result);

        } catch (e) {

            console.error(e);

            alert(e.message);

        }

    };

</script>