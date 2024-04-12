<?php

use Redaxo\Core\Core;
use Redaxo\Core\Security\User;
use Redaxo\Core\Security\UserSession;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_user_remove_session extends rex_api_function
{
    public function execute()
    {
        $userId = rex_request::get('user_id', 'int');
        $user = Core::requireUser();

        if ($userId !== $user->getId() && !$user->isAdmin() && (!$user->hasPerm('users[]') || User::require($userId)->isAdmin())) {
            throw new rex_api_exception('Permission denied');
        }

        $sessionId = rex_request::get('session_id', 'string');

        if (UserSession::getInstance()->removeSession($sessionId, $userId)) {
            return new rex_api_result(true, I18n::msg('session_removed'));
        }

        return new rex_api_result(false, I18n::msg('session_remove_error'));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
