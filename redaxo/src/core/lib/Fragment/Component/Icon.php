<?php

namespace Redaxo\Core\Fragment\Component;

use rex_fragment;

class Icon extends rex_fragment
{
    private string $fileName = 'core/Component/Icon.php';

    public function __construct(
        // The name of the icon to draw. Available names depend on the icon library being used.
        public IconLibrary $name,
        // An alternate description to use for assistive devices. If omitted, the icon will be considered presentational and ignored by assistive devices.
        public ?string $label = null,
        // An external URL of an SVG file. Be sure you trust the content you are including, as it will be executed as code and can result in XSS attacks.
        public ?string $src = null,
        public ?string $slot = null,
    ) {
        parent::__construct([]);
    }

    public function parse($filename = null): string
    {
        if (!$filename) {
            $filename = $this->fileName;
        }
        return parent::parse($filename);
    }
}

enum IconLibrary
{
    case Add;
    case Debug;
    case Save;
}
