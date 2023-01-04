<?php

use lbuchs\WebAuthn\Binary\ByteBuffer;
use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\WebAuthnException;

/**
 * @package redaxo\core\login
 *
 * @internal
 */
class rex_webauthn
{
    private const SESSION_CHALLENGE = 'webauthn_challenge';

    private WebAuthn $webauthn;

    public function __construct()
    {
        $this->webauthn = new WebAuthn(rex::getServerName(), rex::getRequest()->getHost());
    }

    public function getCreateArgs(): string
    {
        $user = rex::requireUser();

        $args = $this->webauthn->getCreateArgs((string) $user->getId(), $user->getLogin(), $user->getName(), requireResidentKey: true);

        rex_set_session(self::SESSION_CHALLENGE, $this->webauthn->getChallenge());

        return json_encode($args);
    }

    /** @return array{string, string} */
    public function processCreate(string $data): array
    {
        $data = rex_type::instanceOf(json_decode($data), stdClass::class);

        $clientDataJSON = base64_decode(rex_type::string($data->clientDataJSON));
        $attestationObject = base64_decode(rex_type::string($data->attestationObject));

        /** @var ByteBuffer $challenge */
        $challenge = rex_session(self::SESSION_CHALLENGE);

        $data = $this->webauthn->processCreate($clientDataJSON, $attestationObject, $challenge);

        return [base64_encode(rex_type::string($data->credentialId)), rex_type::string($data->credentialPublicKey)];
    }

    public function getGetArgs(): string
    {
        $args = $this->webauthn->getGetArgs();

        rex_set_session(self::SESSION_CHALLENGE, $this->webauthn->getChallenge());

        return json_encode($args);
    }

    public function processGet(string $data): ?rex_user
    {
        $data = rex_type::instanceOf(json_decode($data), stdClass::class);

        $id = (int) base64_decode(rex_type::string($data->userHandle));

        $user = rex_user::get($id);

        if (!$user || $user->getValue('passkey_id') !== $data->id) {
            return null;
        }

        $clientDataJSON = base64_decode(rex_type::string($data->clientDataJSON));
        $authenticatorData = base64_decode(rex_type::string($data->authenticatorData));
        $signature = base64_decode(rex_type::string($data->signature));

        /** @var ByteBuffer $challenge */
        $challenge = rex_session(self::SESSION_CHALLENGE);

        try {
            $this->webauthn->processGet($clientDataJSON, $authenticatorData, $signature, $user->getValue('passkey_public_key'), $challenge);
        } catch (WebAuthnException) {
            return null;
        }

        return $user;
    }
}
