const authAddPasskey = function (form) {
    form.classList.remove('hidden');

    const passkey = form.querySelector('[data-auth-passkey]');

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        const options = JSON.parse(passkey.dataset.authPasskey);
        recursiveBase64StrToArrayBuffer(options);

        navigator.credentials.create(options).then(data => {
            data = {
                clientDataJSON: data.response.clientDataJSON  ? arrayBufferToBase64(data.response.clientDataJSON) : null,
                attestationObject: data.response.attestationObject ? arrayBufferToBase64(data.response.attestationObject) : null
            }

            passkey.value = JSON.stringify(data);

            form.submit();
        });
    });
}

const authPasskeyVerify = function (form) {
    const verify = form.querySelector('[data-auth-passkey-verify]');
    if (!verify) {
        return;
    }

    const fields = form.querySelectorAll('input[type=password], button[type=submit]');
    fields.forEach(field => field.disabled = true);

    verify.addEventListener('click', () => {
        const passkey = form.querySelector('[data-auth-passkey]');

        authVerify(passkey, 'required', () => {
            verify.disabled = true;
            form.querySelector('[data-auth-passkey-verify-success]').classList.remove('hidden');
            fields.forEach(field => field.disabled = false);
        });
    });
}

const authLogin = function (form) {
    const passkey = form.querySelector('[data-auth-passkey]');

    authVerify(passkey, 'conditional', () => form.submit());
}

const authVerify = function (passkey, mediation, onSuccess) {
    const options = JSON.parse(passkey.dataset.authPasskey);
    recursiveBase64StrToArrayBuffer(options);

    // const abortController = new AbortController();
    // options.signal = abortController.signal;

    options.mediation = mediation;

    navigator.credentials.get(options).then(data => {
        data = {
            id: data.id,
            clientDataJSON: data.response.clientDataJSON  ? arrayBufferToBase64(data.response.clientDataJSON) : null,
            authenticatorData: data.response.authenticatorData ? arrayBufferToBase64(data.response.authenticatorData) : null,
            signature: data.response.signature ? arrayBufferToBase64(data.response.signature) : null,
            userHandle: data.response.userHandle ? arrayBufferToBase64(data.response.userHandle) : null
        }

        passkey.value = JSON.stringify(data);

        onSuccess();
    });
}

const recursiveBase64StrToArrayBuffer = function (obj) {
    let prefix = '=?BINARY?B?';
    let suffix = '?=';
    if (typeof obj === 'object') {
        for (let key in obj) {
            if (typeof obj[key] === 'string') {
                let str = obj[key];
                if (str.substring(0, prefix.length) === prefix && str.substring(str.length - suffix.length) === suffix) {
                    str = str.substring(prefix.length, str.length - suffix.length);

                    let binary_string = window.atob(str);
                    let len = binary_string.length;
                    let bytes = new Uint8Array(len);
                    for (let i = 0; i < len; i++)        {
                        bytes[i] = binary_string.charCodeAt(i);
                    }
                    obj[key] = bytes.buffer;
                }
            } else {
                recursiveBase64StrToArrayBuffer(obj[key]);
            }
        }
    }
}

const arrayBufferToBase64 = function (buffer) {
    let binary = '';
    let bytes = new Uint8Array(buffer);
    let len = bytes.byteLength;
    for (let i = 0; i < len; i++) {
        binary += String.fromCharCode( bytes[ i ] );
    }
    return window.btoa(binary);
}

// Availability of `window.PublicKeyCredential` means WebAuthn is usable.
// `isUserVerifyingPlatformAuthenticatorAvailable` means the feature detection is usable.
// `isConditionalMediationAvailable` means the feature detection is usable.
if (window.PublicKeyCredential &&
    PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable &&
    PublicKeyCredential.isConditionalMediationAvailable) {
    // Check if user verifying platform authenticator is available.
    Promise.all([
        PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable(),
        PublicKeyCredential.isConditionalMediationAvailable(),
    ]).then(results => {
        if (!results.every(r => r === true)) {
            return;
        }

        $(document).on('rex:ready', function (event, container) {
            container = container.get(0);

            let form = container.querySelector('form[data-auth-add-passkey]');
            if (form) {
                authAddPasskey(form);
                authPasskeyVerify(form);
            }
            form = container.querySelector('form[data-auth-change-password]');
            if (form) {
                authPasskeyVerify(form);
            }

            form = container.querySelector('form[data-auth-login]');
            if (form) {
                authLogin(form);
            }
        });
    });
}
