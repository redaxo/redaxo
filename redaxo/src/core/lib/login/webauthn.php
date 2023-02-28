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
    private const SESSION_CHALLENGE_CREATE = 'webauthn_challenge_create';
    private const SESSION_CHALLENGE_GET = 'webauthn_challenge_get';

    public function getCreateArgs(): string
    {
        $user = rex::requireUser();

        $webauthn = $this->createWebauthnBase();
        $args = $webauthn->getCreateArgs((string) $user->getId(), $user->getLogin(), $user->getName(), requireResidentKey: true, requireUserVerification: true);

        rex_set_session(self::SESSION_CHALLENGE_CREATE, $webauthn->getChallenge());

        return json_encode($args);
    }

    /** @return array{string, string} */
    public function processCreate(string $data): array
    {
        $data = rex_type::instanceOf(json_decode($data), stdClass::class);

        $clientDataJSON = base64_decode(rex_type::string($data->clientDataJSON));
        $attestationObject = base64_decode(rex_type::string($data->attestationObject));

        /** @var ByteBuffer $challenge */
        $challenge = rex_session(self::SESSION_CHALLENGE_CREATE);

        $data = $this->createWebauthnBase()->processCreate($clientDataJSON, $attestationObject, $challenge, requireUserVerification: true);

        $credentialId = rex_type::string($data->credentialId);
        $credentialId = rtrim(strtr(base64_encode($credentialId), '+/', '-_'), '=');

        return [$credentialId, rex_type::string($data->credentialPublicKey)];
    }

    public function getGetArgs(?string $id = null): string
    {
        $webauthn = $this->createWebauthnBase();
        $args = $webauthn->getGetArgs($id ? [ByteBuffer::fromBase64Url($id)] : [], requireUserVerification: true);

        rex_set_session(self::SESSION_CHALLENGE_GET, $webauthn->getChallenge());

        return json_encode($args);
    }

    /** @return array{string, rex_user}|null */
    public function processGet(string $data): ?array
    {
        $data = rex_type::instanceOf(json_decode($data), stdClass::class);

        $id = (int) base64_decode(rex_type::string($data->userHandle));

        $user = rex_user::get($id);

        if (!$user) {
            return null;
        }

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT public_key FROM '.rex::getTable('user_passkey').' WHERE id = ? AND user_id = ?', [$data->id, $id]);

        if (!$sql->getRows()) {
            return null;
        }

        $clientDataJSON = base64_decode(rex_type::string($data->clientDataJSON));
        $authenticatorData = base64_decode(rex_type::string($data->authenticatorData));
        $signature = base64_decode(rex_type::string($data->signature));

        /** @var ByteBuffer $challenge */
        $challenge = rex_session(self::SESSION_CHALLENGE_GET);

        try {
            $this->createWebauthnBase()->processGet($clientDataJSON, $authenticatorData, $signature, rex_type::string($sql->getValue('public_key')), $challenge, requireUserVerification: true);
        } catch (WebAuthnException) {
            return null;
        }

        return [rex_type::string($data->id), $user];
    }

    private function createWebauthnBase(): WebAuthn
    {
        return new WebAuthn(rex::getServerName(), rex::getRequest()->getHost());
    }
}
