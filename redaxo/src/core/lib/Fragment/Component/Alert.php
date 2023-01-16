<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Slot;
use rex_fragment;

class Alert extends rex_fragment
{
    private string $fileName = 'core/Component/Alert.php';

    public function __construct(
        /**
         * The alert's main content.
         */
        public Slot $slotDefault,

        /**
         * An icon to show in the alert.
         */
        public ?Slot $slotIcon = null,

        /**
         * Indicates whether or not the alert is open. You
         * can toggle this attribute to show and hide the
         * alert, or you can use the show() and hide()
         * methods and this attribute will reflect the
         * alert's open state.
         */
        public bool $open = false,

        /**
         * Enables a close button that allows the user to
         * dismiss the alert.
         */
        public bool $closeable = false,

        /**
         * The alert's type.
         */
        public ?AlertType $type = null,

        /**
         * The length of time, in milliseconds, the alert
         * will show before closing itself. If the user
         * interacts with the alert before it closes
         * (e.g. moves the mouse over it), the timer will
         * restart. Defaults to Infinity, meaning the alert
         * will not close on its own.
         */
        public ?int $duration = null,

        /** @var array<string, string>|null */
        public ?array $attributes = null,
    ) {
        parent::__construct([]);
    }

    public function render(): string
    {
        return parent::parse($this->fileName);
    }
}

enum AlertType
{
    case Error;
    case Info;
    case Neutral;
    case Success;
    case Warning;
}
