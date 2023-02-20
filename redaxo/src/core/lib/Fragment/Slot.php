<?php

namespace Redaxo\Core\Fragment;

use Closure;
use rex_functional_exception;

use function is_array;

class Slot
{
    public function __construct(
        /** @var list<string|null>|string|Closure */
        public array|string|Closure $value,
    ) {
        if (is_array($this->value)) {
            $this->value = implode('', array_filter($this->value, static function ($value) {
                return null !== $value;
            }));
        }
    }

    public function get(): string
    {
        if ($this->value instanceof Closure) {
            ob_start();
            ($this->value)();
            $this->value = ob_get_clean();
        }
        return $this->value;
    }

    public function prepare(string $slot): static
    {
        $this->value = trim($this->get());
        $this->value = preg_replace('/\A(<[a-z-]+)/', '$1 slot="'.$slot.'"', $this->value);

        if (!str_contains($this->value, 'slot="'.$slot.'"')) {
            throw new rex_functional_exception('The '.$slot.' property requires the attribute `slot="'.$slot.'"`');
        }

        return $this;
    }
}
