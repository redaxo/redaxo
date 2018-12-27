<?php

class rex_stop_watch
{
    /**
     * The minimum duration (in milliseconds) a callable needs to consume for beeing recorded.
     * Faster callables will not be recorded.
     *
     * @var integer
     */
    const MIN_DURATION = 10;

    /**
     * List of already callables which exceeded MIN_DURATION
     *
     * @var string[]
     */
    public static $timers = array();

    private $label;

    private $start;

    private $duration;

    /**
     *
     * @param string $label
     */
    public function __construct($label)
    {
        $this->label = $label;
    }

    public function start()
    {
        if ($this->start) {
            throw new LogicException('can only be started once.');
        }
        $this->start = microtime(true);
    }

    public function stop()
    {
        if (! $this->start) {
            throw new LogicException('missing start() call before stop().');
        }

        $start = $this->start;
        $label = $this->label;

        $durationSec = microtime(true) - $start;
        $durationMs = $durationSec * 1000;

        // dont record events which are fast as hell.
        if ($durationMs > self::MIN_DURATION) {
            $this->duration = $durationMs;

            $durationMs = number_format($durationMs, 3);

            $uniqueLabel = $label;
            $i = 1;
            while (isset(self::$timers[$uniqueLabel])) {
                $uniqueLabel = $label . $i;
                $i++;
            }
            $this->probeMarker($uniqueLabel . ' ' . $durationMs . ' ms');
            self::$timers[$uniqueLabel] = $durationMs;
        }
    }

    public static function measure($label, callable $callable)
    {
        $watch = new self($label);

        $watch->start();
        $result = $callable();
        $watch->stop();

        return $result;
    }

    private function probeMarker($label)
    {
        // addMarker is available since ext-blackfire 1.15.0
        // https://github.com/blackfireio/php-sdk/issues/23
        if (class_exists('BlackfireProbe', false) && method_exists('BlackfireProbe', 'addMarker')) {
            BlackfireProbe::addMarker($label);
        }
    }

    /**
     * Returns the measured duration in milliseconds
     *
     * @return float
     */
    public function getDuration()
    {
        return $this->duration;
    }
}
