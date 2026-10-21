<?php

namespace Redaxo\Core\Backend\Form;

use Redaxo\Core\Form\Field\Choice;
use Redaxo\Core\Form\Field\Email;
use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\Translation\Translatable;

/**
 * The settings users edit in their own profile.
 *
 * @internal
 */
final class Profile
{
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

    #[Choice(
        new Translatable('backend_language'),
        choices: SystemSettings::locales(...),
        placeholder: new Translatable('user_system_default'),
    )]
    public ?string $language = null;

    #[Choice(
        new Translatable('theme'),
        choices: ['light' => new Translatable('theme_light'), 'dark' => new Translatable('theme_dark')],
        placeholder: new Translatable('theme_auto'),
    )]
    public ?string $theme = null;
}
