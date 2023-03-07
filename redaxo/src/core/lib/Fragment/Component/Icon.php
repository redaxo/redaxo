<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;

class Icon extends Fragment
{
    public function __construct(
        /**
         * The name of the icon to draw. Available names
         * depend on the icon library being used.
         */
        public IconLibrary $name,

        /**
         * An alternate description to use for assistive
         * devices. If omitted, the icon will be considered
         * presentational and ignored by assistive devices.
         */
        public ?string $label = null,

        /**
         * An external URL of an SVG file. Be sure you trust
         *the content you are including, as it will be
         * executed as code and can result in XSS attacks.
         */
        public ?string $src = null,
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Icon.php';
    }
}

enum IconLibrary
{
    case Add;
    case AlertError;
    case AlertInfo;
    case AlertNeutral;
    case AlertSuccess;
    case AlertWarning;
    case Debug;
    case PhpInfo;
    case VersionUnstable;
    case Save;
}
