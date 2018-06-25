<?php

/**
 * @package redaxo\core
 */
class rex_editor
{
    use rex_factory_trait;

    // see https://github.com/filp/whoops/blob/master/docs/Open%20Files%20In%20An%20Editor.md
    // keep this list in sync with the array in getSupportedEditors()
    private $editors = [
        'atom' => 'atom://core/open/file?filename=%file&line=%line',
        'emacs' => 'emacs://open?url=file://%file&line=%line',
        'idea' => 'idea://open?file=%file&line=%line',
        'macvim' => 'mvim://open/?url=file://%file&line=%line',
        'phpstorm' => 'phpstorm://open?file=%file&line=%line',
        'sublime' => 'subl://open?url=file://%file&line=%line',
        'textmate' => 'txmt://open?url=file://%file&line=%line',
        'vscode' => 'vscode://file/%file:%line',
    ];

    // we expect instantiation via factory()
    private function __construct()
    {
    }

    /**
     * Creates a rex_editor instance.
     *
     * @return static Returns a rex_editor instance
     */
    public static function factory()
    {
        $class = static::getFactoryClass();
        return new $class();
    }

    public function getUrl($filePath, $line)
    {
        $editor = rex::getProperty('editor');
        $editorUrl = $this->editors[$editor];

        $editorUrl = str_replace('%line', $line, $editorUrl);
        $editorUrl = str_replace('%file', $filePath, $editorUrl);

        $editorUrl = rex_extension::registerPoint(new rex_extension_point('EDITOR_URL', $editorUrl, [
            'file' => $filePath,
            'line' => $line,
            'editor' => $this,
        ]));

        return $editorUrl;
    }

    public function getSupportedEditors()
    {
        return [
            'atom' => 'Atom',
            'emacs' => 'Emacs',
            'idea' => 'IDEA',
            'macvim' => 'MacVim',
            'phpstorm' => 'PhpStorm',
            'sublime' => 'Sublime Text',
            'textmate' => 'Textmate',
            'vscode' => 'Visual Studio Code',
            'xdebug' => 'Xdebug via xdebug.file_link_format (php.ini)',
        ];
    }
}
