class Passkey {

    static #v = 'v1';

    static #config = {};

    static configure(config) {

        this.#config = {
            ...this.#config,
            ...config
        };

        return this;
    }


    static config(name) {

        const value = this.#config[name];

        if (typeof value === 'undefined') {
            throw new Error(
                `Missing Passkey configuration: ${name}`
            );
        }

        return value;
    }


    static async execute(action, callback) {

        try {
            const result = await action();

            try {
                this.#config[callback]?.(result);
            } catch (e) {
                console.error(e);
            }

            return result;

        } catch (error) {

            try {
                this.#config.onError?.(error);
            } catch (e) {
                console.error(e);
            }
            throw error;
        }
    }

    static async register() {

        if (!this.isSupported()) {
            throw new Error(
                'Passkeys are not supported by this browser.'
            );
        }
        return this.execute(async () => {
            const options = await this.getRegistrationOptions();

            options.challenge =
                this.base64urlToBuffer(options.challenge);

            options.user.id =
                this.base64urlToBuffer(options.user.id);

            if (options.excludeCredentials) {

                for (const credential of options.excludeCredentials) {
                    credential.id =
                        this.base64urlToBuffer(credential.id);
                }
            }

            const credential =
                await navigator.credentials.create({
                    publicKey: options
                });

            return this.finishRegistration(
                credential
            );
        }, 'onRegistered');
    }

    static async authenticate() {

        if (!this.isSupported()) {
            throw new Error(
                'Passkeys are not supported by this browser.'
            );
        }

        return this.execute(async () => {
            const options =
                await this.getAuthenticationOptions();

            options.challenge =
                this.base64urlToBuffer(options.challenge);

            if (options.allowCredentials) {

                for (const credential of options.allowCredentials) {
                    credential.id =
                        this.base64urlToBuffer(credential.id);
                }
            }

            const credential =
                await navigator.credentials.get({
                    publicKey: options
                });

            return this.finishAuthentication(
                credential
            );
        }, 'onAuthenticated');

    }

    static async getRegistrationOptions() {

        return this.post(
            this.config('registrationOptionsUrl')
        );

    }

    static async finishRegistration(
        credential
    ) {

        return this.post(
            this.config('registrationUrl'),
            this.serializeCredential(credential)
        );

    }

    static async getAuthenticationOptions() {

        return this.post(
            this.config('authenticationOptionsUrl')
        );

    }

    static async finishAuthentication(
        credential
    ) {

        return this.post(
            this.config('authenticationUrl'),
            this.serializeCredential(credential)
        );

    }

    static async isUserVerifyingPlatformAuthenticatorAvailable() {

        if (!this.isSupported()) {
            return false;
        }

        return await PublicKeyCredential
            .isUserVerifyingPlatformAuthenticatorAvailable();

    }

    static base64urlToBuffer(base64url) {

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

    static bufferToBase64url(buffer) {

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

    static async post(
        url,
        body = null
    ) {

        const response =
            await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type':
                        'application/json',
                    'X-CSRF-Token':
                        this.getCsrfToken()
                },
                body:
                    body
                        ? JSON.stringify(body)
                        : null

            });

        if (!response.ok) {

            let message = response.statusText;

            try {
                const error = await response.json();
                message = error.message ?? message;

            } catch (_) {
                message = await response.text();
            }

            throw new Error(message);
        }

        const result = await response.json();

        return result;

    }

    static getCsrfToken() {

        return document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;

    }

    static serializeCredential(credential) {

        let response;

        if (credential.response instanceof AuthenticatorAttestationResponse) {

            response = {
                clientDataJSON: this.bufferToBase64url(
                    credential.response.clientDataJSON
                ),
                attestationObject: this.bufferToBase64url(
                    credential.response.attestationObject
                ),
                transports: credential.response.getTransports?.()
            };

        } else {

            response = {
                clientDataJSON: this.bufferToBase64url(
                    credential.response.clientDataJSON
                ),
                authenticatorData: this.bufferToBase64url(
                    credential.response.authenticatorData
                ),
                signature: this.bufferToBase64url(
                    credential.response.signature
                ),
                userHandle: credential.response.userHandle
                    ? this.bufferToBase64url(
                        credential.response.userHandle
                    )
                    : null
            };
        }

        return {
            id: credential.id,
            rawId: this.bufferToBase64url(
                credential.rawId
            ),
            type: credential.type,
            response,
            clientExtensionResults:
                credential.getClientExtensionResults(),
        };
    }

    static isSupported() {

        return (
            typeof window !== 'undefined' &&
            window.isSecureContext &&
            typeof PublicKeyCredential !== 'undefined' &&
            !!navigator.credentials
        );

    }
}

window.Passkey = Passkey;