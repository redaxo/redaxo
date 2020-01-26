<?php

/**
 * @package redaxo\core
 */
class rex_editor
{
    use rex_factory_trait;

    // see https://github.com/filp/whoops/blob/master/docs/Open%20Files%20In%20An%20Editor.md
    // keep this list in sync with the array in getSupportedEditors()
    /**
     * @var string[]
     */
    private $editors = [
        'atom' => 'atom://core/open/file?filename=%f&line=%l',
        'emacs' => 'emacs://open?url=file://%f&line=%l',
        'idea' => 'idea://open?file=%f&line=%l',
        'macvim' => 'mvim://open/?url=file://%f&line=%l',
        'phpstorm' => 'phpstorm://open?file=%f&line=%l',
        'sublime' => 'subl://open?url=file://%f&line=%l',
        'textmate' => 'txmt://open?url=file://%f&line=%l',
        'vscode' => 'vscode://file/%f:%l',
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

        $editorUrl = null;

        $editorBasepath = rex::getProperty('editor_basepath');
        if ($editorBasepath) {
            // replace remote base path with local base path
            $filePath = str_replace(rex_path::base(), $editorBasepath, $filePath);
        }

        if (false !== strpos($filePath, '://')) {
            // don't provide editor urls for paths containing "://", like "rex://..."
            // but they can be converted into an url by the extension point below
        } elseif (isset($this->editors[$editor]) || 'xdebug' === $editor) {
            if ('xdebug' === $editor) {
                // if xdebug is not enabled, use `get_cfg_var` to get the value directly from php.ini
                $editorUrl = ini_get('xdebug.file_link_format') ?: get_cfg_var('xdebug.file_link_format');
            } else {
                $editorUrl = $this->editors[$editor];
            }

            $editorUrl = str_replace('%l', $line, $editorUrl);
            $editorUrl = str_replace('%f', $filePath, $editorUrl);
        }

        $editorUrl = rex_extension::registerPoint(new rex_extension_point('EDITOR_URL', $editorUrl, [
            'file' => $filePath,
            'line' => $line,
        ]));

        return $editorUrl;
    }

    /**
     * @return string[]
     */
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
