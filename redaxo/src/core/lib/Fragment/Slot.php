<?php

namespace Redaxo\Core\Fragment;

use Closure;
use rex_functional_exception;

class Slot
{
    public function __construct(

        /**
         * @var list<string|null>|string|callable
         */
        public array|string|Closure $value,
    ) {
        if (is_array($this->value)) {
            $this->value = implode('',
                array_filter($this->value, function($value) {
                    return null !== $value;
                })
            );
        }
    }

    public function get(): string
    {
        if (is_callable($this->value)) {
            ob_start();
            call_user_func($this->value, func_get_args());
            $this->value = ob_get_clean();
        }
        return $this->value;
    }

    public function prepare(string $slot): static
    {
        $this->value = trim($this->get());
        if (!str_contains($this->value, 'slot="'.$slot.'"')) {
            $this->value = preg_replace('/\A(<[a-z-]+)/', '$1 slot="'.$slot.'"', $this->value);
        }

        if (!str_contains($this->value, 'slot="'.$slot.'"')) {
            throw new rex_functional_exception('The '.$slot.' property requires the attribute `slot="'.$slot.'"`');
        }

        return $this;
    }
}
