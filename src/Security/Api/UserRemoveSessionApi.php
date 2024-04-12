<?php

namespace Redaxo\Core\Security\Api;

use Redaxo\Core\Api\ApiException;
use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Api\ApiResult;
use Redaxo\Core\Core;
use Redaxo\Core\Security\User;
use Redaxo\Core\Security\UserSession;
use Redaxo\Core\Translation\I18n;
use rex_request;

/**
 * @internal
 */
class UserRemoveSessionApi extends ApiFunction
{
    public function execute()
    {
        $userId = rex_request::get('user_id', 'int');
        $user = Core::requireUser();

        if ($userId !== $user->getId() && !$user->isAdmin() && (!$user->hasPerm('users[]') || User::require($userId)->isAdmin())) {
            throw new ApiException('Permission denied');
        }

        $sessionId = rex_request::get('session_id', 'string');

        if (UserSession::getInstance()->removeSession($sessionId, $userId)) {
            return new ApiResult(true, I18n::msg('session_removed'));
        }

        return new ApiResult(false, I18n::msg('session_remove_error'));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
