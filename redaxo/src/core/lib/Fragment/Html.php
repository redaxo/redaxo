<?php

namespace Redaxo\Core\Fragment;

use Closure;
use rex_type;

use function is_string;

class Html extends Fragment
{
    public function __construct(
        /** @var string|list<string|Fragment|null>|Closure */
        public string|array|Closure $value,
    ) {}

    public function render(): string
    {
        if (is_string($this->value)) {
            return $this->value;
        }

        if ($this->value instanceof Closure) {
            ob_start();
            ($this->value)();

            return rex_type::string(ob_get_clean());
        }

        return implode('', array_map(static function (string|Fragment|null $value): string {
            if ($value instanceof Fragment) {
                return $value->render();
            }

            return $value ?? '';
        }, $this->value));
    }
}
