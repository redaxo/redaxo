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
        $filePath = str_replace('rex:///', 'rex://', $filePath);

        $editor = rex_editor::factory();
        $editor->registerUrlHandler(function($filePath, $line) {
            var_dump($filePath);
            if (preg_match('{^rex://template/\d+}', $filePath, $matches)) {
                var_dump($matches);
            }
            exit();
        });

        foreach($this->handlers as $urlHandler) {
            $url = $urlHandler($filePath, $line);
            if ($url) {
                return $url;
            }
        }

        /*
        $parsedUrl = parse_url($filePath);
        if ($parsedUrl['scheme'] === 'rex') {
            if ($parsedUrl['host'] === 'template') {
                $templateId = ltrim($parsedUrl['path'], '/');
                return 'http://localhost/redaxo/redaxo/index.php?page=templates&start=0&function=edit&template_id='. $templateId;
            }
        }
        */

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