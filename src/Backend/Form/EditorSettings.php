<?php

namespace Redaxo\Core\Backend\Form;

use Redaxo\Core\Form\Field\Choice;
use Redaxo\Core\Form\Field\Text;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\Util\Editor;

/** @internal */
final class EditorSettings
{
    #[Choice(
        new Translatable('system_editor_name'),
        choices: static function () {
            return Editor::factory()->getSupportedEditors();
        },
        placeholder: new Translatable('system_editor_no_editor'),
    )]
    public ?string $name = null;

    #[Text(new Translatable('system_editor_basepath'), note: new Translatable('system_editor_basepath_note'))]
    public ?string $basepath = null;
}
