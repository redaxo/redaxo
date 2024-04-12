<?php

use Redaxo\Core\Api\ApiFunction;
use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_api_user_remove_auth_method extends ApiFunction
{
    public function execute()
    {
        $userId = rex_request::get('user_id', 'int');
        $user = Core::requireUser();

        if ($userId !== $user->getId() && !$user->isAdmin() && (!$user->hasPerm('users[]') || rex_user::require($userId)->isAdmin())) {
            throw new rex_api_exception('Permission denied');
        }

        if (rex_get('password', 'bool')) {
            return $this->removePassword($userId);
        }

        return $this->removePasskey($userId);
    }

    protected function requiresCsrfProtection()
    {
        return true;
    }

    private function removePassword(int $userId): rex_api_result
    {
        $sql = Sql::factory()
            ->setTable(Core::getTable('user'))
            ->setWhere(['id' => $userId])
            ->setValue('password', null)
            ->addGlobalUpdateFields()
            ->setValue('password_change_required', 0)
            ->setDateTimeValue('password_changed', time())
            ->update();

        if (!$sql->getRows()) {
            return new rex_api_result(false, I18n::msg('password_remove_error'));
        }

        rex_user::clearInstance($userId);
        Core::getProperty('login')->changedPassword(null);

        return new rex_api_result(true, I18n::msg('password_removed'));
    }

    private function removePasskey(int $userId): rex_api_result
    {
        $passkeyId = rex_request::get('passkey_id', 'string');

        $sql = Sql::factory()
            ->setTable(Core::getTable('user_passkey'))
            ->setWhere(['id' => $passkeyId, 'user_id' => $userId])
            ->delete();

        if (!$sql->getRows()) {
            return new rex_api_result(false, I18n::msg('passkey_remove_error'));
        }

        return new rex_api_result(true, I18n::msg('passkey_removed'));
    }
}
