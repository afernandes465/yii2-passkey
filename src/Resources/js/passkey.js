class Passkey {

    static async register(options = {}) {

        const publicKey = await this.#getRegistrationOptions(options);

        publicKey.challenge = this.#base64urlToBuffer(
            publicKey.challenge
        );

        publicKey.user.id = this.#base64urlToBuffer(
            publicKey.user.id
        );

        if (publicKey.excludeCredentials) {

            publicKey.excludeCredentials =
                publicKey.excludeCredentials.map(item => ({
                    ...item,
                    id: this.#base64urlToBuffer(item.id),
                }));

        }

        const credential = await navigator.credentials.create({
            publicKey
        });

        return await this.#finishRegistration(
            credential,
            options
        );
    }

    static async authenticate(options = {}) {

    }

    static async #getRegistrationOptions(options = {}) {

        const url = options.registrationOptionsUrl
            ?? '/passkey/registration-options';

        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        };

        // Adiciona automaticamente o CSRF do Yii2, caso exista
        const csrf = document.querySelector('meta[name="csrf-token"]');

        if (csrf) {
            headers['X-CSRF-Token'] = csrf.content;
        }

        const response = await fetch(url, {
            method: 'POST',
            headers,
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(
                `Unable to obtain registration options (${response.status}).`
            );
        }

        return await response.json();
    }

    static async #finishRegistration(credential, options = {}) {

        const url = options.registrationUrl
            ?? '/passkey/registration';

        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        };

        const csrf = document.querySelector('meta[name="csrf-token"]');

        if (csrf) {
            headers['X-CSRF-Token'] = csrf.content;
        }

        const payload = {
            id: credential.id,
            rawId: this.#bufferToBase64url(credential.rawId),
            type: credential.type,
            response: {
                clientDataJSON: this.#bufferToBase64url(
                    credential.response.clientDataJSON
                ),
                attestationObject: this.#bufferToBase64url(
                    credential.response.attestationObject
                ),
            },
        };

        // Extensões (caso existam)
        if (typeof credential.getClientExtensionResults === 'function') {
            payload.clientExtensionResults =
                credential.getClientExtensionResults();
        }

        const response = await fetch(url, {
            method: 'POST',
            headers,
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            throw new Error(
                `Unable to complete registration (${response.status}).`
            );
        }

        return await response.json();
    }

    static #base64urlToBuffer(base64url) {

        const base64 = base64url
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const padding = '='.repeat((4 - (base64.length % 4)) % 4);

        const binary = atob(base64 + padding);

        const bytes = new Uint8Array(binary.length);

        for (let i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }

        return bytes;
    }

    static #bufferToBase64url(buffer) {

        const bytes = buffer instanceof Uint8Array
            ? buffer
            : new Uint8Array(buffer);

        let binary = '';

        for (const byte of bytes) {
            binary += String.fromCharCode(byte);
        }

        return btoa(binary)
            .replace(/\+/g, '-')
            .replace(/\//g, '_')
            .replace(/=+$/, '');
    }
}

window.Passkey = Passkey;