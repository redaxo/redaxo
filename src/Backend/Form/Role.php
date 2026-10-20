<?php

namespace Redaxo\Core\Backend\Form;

use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\Form\Field\Textarea;
use Redaxo\Core\Security\ComplexPermission;
use Redaxo\Core\Translation\Translatable;

/** @internal */
final class Role
{
    #[Text(
        new Translatable('name'),
        required: true,
        maxLength: 255,
    )]
    public string $name = '';

    #[Textarea(new Translatable('description'))]
    public ?string $description = null;

    /** @var array<string, list<string>|ComplexPermission::ALL> */
    #[Permissions(new Translatable('user_role_perms'))]
    public array $perms = [];

    /** Grants all complex permissions for all items, the default for new roles. */
    public function grantAllItems(): void
    {
        foreach (ComplexPermission::getAll() as $key => $class) {
            if ($class::getChoices()) {
                $this->perms[$key] = ComplexPermission::ALL;
            }
        }
    }
}
