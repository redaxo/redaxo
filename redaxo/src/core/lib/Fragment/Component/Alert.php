<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;

class Alert extends Fragment
{
    public function __construct(
        /**
         * The alert's main content.
         */
        public string|Fragment $body,

        /**
         * An icon to show in the alert.
         */
        public ?Fragment $icon = null,

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
        public AlertType $type = AlertType::Neutral,

        /**
         * The length of time, in milliseconds, the alert
         * will show before closing itself. If the user
         * interacts with the alert before it closes
         * (e.g. moves the mouse over it), the timer will
         * restart. Defaults to Infinity, meaning the alert
         * will not close on its own.
         */
        public ?int $duration = null,

        /** @var array<string, string|int> */
        public array $attributes = [],
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Alert.php';
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
