<?php

class rex_editor {
    use rex_factory_trait;

    private $handlers = [];

    private $editors = [
        "sublime"  => "subl://open?url=file://%file&line=%line",
        "textmate" => "txmt://open?url=file://%file&line=%line",
        "emacs"    => "emacs://open?url=file://%file&line=%line",
        "macvim"   => "mvim://open/?url=file://%file&line=%line",
        "phpstorm" => "phpstorm://open?file=%file&line=%line",
        "idea"     => "idea://open?file=%file&line=%line",
        "vscode"   => "vscode://file/%file:%line",
    ];

    public function urlFromFile($filePath, $line) {
        // make internal urls work with parse_url()
        $filePath = str_replace('rex:///', 'rex://', $filePath);

        $parsedUrl = parse_url($filePath);
        // rex:// internal url?
        if ($parsedUrl['scheme'] === 'rex') {
            if ($parsedUrl['host'] === 'template') {
                $templateId = ltrim($parsedUrl['path'], '/');
                return rex_url::backendPage('templates', ['function' => 'edit', 'template_id' => $templateId]);
            } elseif($parsedUrl['host'] === 'module') {
                $moduleId = (int) ltrim($parsedUrl['path'], '/');
                return rex_url::backendPage('modules/modules', ['function' => 'edit', 'module_id' => $moduleId]);
            }
        }

        $systemEditor = rex::getProperty('system_editor');
        $editorUrl = $this->editors[$systemEditor];

        $editorUrl = str_replace("%line", rawurlencode($line), $editorUrl);
        $editorUrl = str_replace("%file", rawurlencode($filePath), $editorUrl);

        return $editorUrl;
    }

    public function registerUrlHandler(callable $urlHandler) {
        $this->handlers[] = $urlHandler;
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
}