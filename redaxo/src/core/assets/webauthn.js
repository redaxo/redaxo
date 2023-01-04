const authChange = function (form) {
    const passkey = form.querySelector('[data-auth-passkey]');
    const checkbox = passkey.querySelector('input');
    const password = form.querySelector('[data-auth-password]');

    passkey.classList.remove('hidden');

    checkbox.addEventListener('change', () => {
        password.classList.toggle('hidden', passkey.checked);

        password.querySelectorAll('input').forEach(input => {
            input.disabled = checkbox.checked;
        });
    });

    form.addEventListener('submit', (event) => {
        if (!checkbox.checked || '0' !== checkbox.value) {
            return;
        }

        event.preventDefault();

        const options = JSON.parse(passkey.dataset.authPasskey);
        recursiveBase64StrToArrayBuffer(options);

        navigator.credentials.create(options).then(data => {
            data = {
                clientDataJSON: data.response.clientDataJSON  ? arrayBufferToBase64(data.response.clientDataJSON) : null,
                attestationObject: data.response.attestationObject ? arrayBufferToBase64(data.response.attestationObject) : null
            }

            checkbox.value = JSON.stringify(data);

            form.querySelector('[data-auth-save]').click();
        });
    });
}

const authLogin = function (form) {
    const passkey = form.querySelector('[data-auth-passkey]');

    const options = JSON.parse(passkey.dataset.authPasskey);
    recursiveBase64StrToArrayBuffer(options);

    const abortController = new AbortController();
    options.signal = abortController.signal;

    options.mediation = 'conditional';

    navigator.credentials.get(options).then(data => {
        data = {
            id: data.rawId ? arrayBufferToBase64(data.rawId) : null,
            clientDataJSON: data.response.clientDataJSON  ? arrayBufferToBase64(data.response.clientDataJSON) : null,
            authenticatorData: data.response.authenticatorData ? arrayBufferToBase64(data.response.authenticatorData) : null,
            signature: data.response.signature ? arrayBufferToBase64(data.response.signature) : null,
            userHandle: data.response.userHandle ? arrayBufferToBase64(data.response.userHandle) : null
        }

        passkey.value = JSON.stringify(data);

        form.submit();
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

            let form = container.querySelector('form[data-auth-change]');
            if (form) {
                authChange(form);
            }

            form = container.querySelector('form[data-auth-login]');
            if (form) {
                authLogin(form);
            }
        });

    });
}
