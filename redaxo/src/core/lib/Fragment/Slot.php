<?php

namespace Redaxo\Core\Fragment;

use rex_functional_exception;

class Slot
{
    public function __construct(
        public string $value,
    ) {
    }

    public function get(): string
    {
        return $this->value;
    }

    public function prepare(string $slot): static
    {
        $this->value = trim($this->value);
        if (!str_contains($this->value, 'slot="'.$slot.'"')) {
            $this->value = preg_replace('/\A(<[a-z-]+)/', '$1 slot="'.$slot.'"', $this->value);
        }

        if (!str_contains($this->value, 'slot="'.$slot.'"')) {
            throw new rex_functional_exception('The '.$slot.' property requires the attribute `slot="'.$slot.'"`');
        }

        return $this;
    }
}
