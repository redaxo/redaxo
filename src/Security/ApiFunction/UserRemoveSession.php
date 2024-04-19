<?php

namespace Redaxo\Core\Security\ApiFunction;

use Redaxo\Core\ApiFunction\ApiFunction;
use Redaxo\Core\ApiFunction\Exception\ApiFunctionException;
use Redaxo\Core\ApiFunction\Result;
use Redaxo\Core\Core;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Security\User;
use Redaxo\Core\Security\UserSession;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class UserRemoveSession extends ApiFunction
{
    public function execute()
    {
        $userId = Request::get('user_id', 'int');
        $user = Core::requireUser();

        if ($userId !== $user->getId() && !$user->isAdmin() && (!$user->hasPerm('users[]') || User::require($userId)->isAdmin())) {
            throw new ApiFunctionException('Permission denied');
        }

        $sessionId = Request::get('session_id', 'string');

        if (UserSession::getInstance()->removeSession($sessionId, $userId)) {
            return new Result(true, I18n::msg('session_removed'));
        }

        return new Result(false, I18n::msg('session_remove_error'));
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }
}
