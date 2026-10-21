<?php

namespace Redaxo\Core\Backend\Form;

use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Form\Field\Checkbox;
use Redaxo\Core\Form\Field\Choice;
use Redaxo\Core\Form\Field\Email;
use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\Translation\Translatable;

use function asort;

/**
 * A backend user as edited in the user management.
 *
 * @internal
 */
final class User
{
    #[Text(
        new Translatable('login_name'),
        required: true,
        maxLength: 50,
    )]
    public string $login = '';

    /** The new password; `null` keeps the current one. */
    #[NewPassword(new Translatable('password'))]
    public ?string $password = null;

    #[Checkbox(new Translatable('user_password_change_required'))]
    public bool $passwordChangeRequired = true;

    #[Text(
        new Translatable('name'),
        maxLength: 255,
    )]
    public ?string $name = null;

    #[Text(new Translatable('description'))]
    public ?string $description = null;

    #[Email(
        new Translatable('email'),
        maxLength: 255,
    )]
    public ?string $email = null;

    #[Checkbox(
        new Translatable('user_admin'),
        note: new Translatable('user_admin_note'),
    )]
    public bool $admin = false;

    #[Checkbox(new Translatable('user_status_active'))]
    public bool $status = true;

    /** Resets the failed login attempts; rendered by the page, as the label shows the current count. */
    #[Checkbox(new Translatable('user_reset_tries'))]
    public bool $resetLoginTries = false;

    /** @var list<int> */
    #[Choice(
        new Translatable('user_role'),
        choices: self::roles(...),
        multiple: true,
    )]
    public array $roles = [];

    #[Choice(
        new Translatable('startpage'),
        choices: self::startPages(...),
        placeholder: new Translatable('user_system_default'),
    )]
    public ?string $startPage = null;

    #[Choice(
        new Translatable('backend_language'),
        choices: SystemSettings::locales(...),
        placeholder: new Translatable('user_system_default'),
    )]
    public ?string $language = null;

    /** @return array<int, string> */
    public static function roles(): array
    {
        $roles = [];
        foreach (Sql::factory()->setQuery('SELECT id, name FROM rex_user_role ORDER BY name') as $role) {
            $roles[$role->getIntValue('id')] = $role->getNullableStringValue('name') ?? '';
        }

        return $roles;
    }

    /** @return array<string, string> */
    public static function startPages(): array
    {
        $pages = [];
        foreach (Controller::getPages() as $key => $page) {
            if ($page->hasNavigation() && !$page->isHidden()) {
                $pages[$key] = $page->getTitle();
            }
        }
        asort($pages);

        return $pages;
    }
}
