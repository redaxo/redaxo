<?php

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
    private function __construct() {}
    
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
        // make internal urls work with parse_url()
        $filePath = str_replace('rex:///', 'rex://', $filePath);

        $parsedUrl = parse_url($filePath);
        // rex:// internal url mapping to backend form-edit urls
        if ($parsedUrl['scheme'] === 'rex') {
            if ($parsedUrl['host'] === 'template') {
                $templateId = ltrim($parsedUrl['path'], '/');
                return rex_url::backendPage('templates', ['function' => 'edit', 'template_id' => $templateId]);
            }
            if ($parsedUrl['host'] === 'module') {
                $moduleId = (int) ltrim($parsedUrl['path'], '/');
                return rex_url::backendPage('modules/modules', ['function' => 'edit', 'module_id' => $moduleId]);
            }
        }

        $systemEditor = rex::getProperty('system_editor');
        $editorUrl = $this->editors[$systemEditor];

        $editorUrl = str_replace('%line', rawurlencode($line), $editorUrl);
        $editorUrl = str_replace('%file', rawurlencode($filePath), $editorUrl);

        return $editorUrl;
    }
    
    public function getSupportedEditors() {
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
