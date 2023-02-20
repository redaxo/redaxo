<?php

/**
 * @package redaxo\core\login
 * @internal
 */
class rex_api_user_remove_auth_method extends rex_api_function
{
    public function execute()
    {
        $userId = rex_request::get('user_id', 'int');
        $user = rex::requireUser();

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
        $sql = rex_sql::factory()
            ->setTable(rex::getTable('user'))
            ->setWhere(['id' => $userId])
            ->setValue('password', null)
            ->addGlobalUpdateFields()
            ->setValue('password_change_required', 0)
            ->setDateTimeValue('password_changed', time())
            ->update();

        if (!$sql->getRows()) {
            return new rex_api_result(false, rex_i18n::msg('password_remove_error'));
        }

        rex_user::clearInstance($userId);
        rex::getProperty('login')->changedPassword(null);

        return new rex_api_result(true, rex_i18n::msg('password_removed'));
    }

    private function removePasskey(int $userId): rex_api_result
    {
        $passkeyId = rex_request::get('passkey_id', 'string');

        $sql = rex_sql::factory()
            ->setTable(rex::getTable('user_passkey'))
            ->setWhere(['id' => $passkeyId, 'user_id' => $userId])
            ->delete();

        if (!$sql->getRows()) {
            return new rex_api_result(false, rex_i18n::msg('passkey_remove_error'));
        }

        return new rex_api_result(true, rex_i18n::msg('passkey_removed'));
    }
}
