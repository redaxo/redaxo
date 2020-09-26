<?php

final class rex_analytics_metric {
    const TYPE_FID = 'FID', TYPE_LCP = 'LCP', TYPE_CLS = 'CLS';

    private static $thresholds = [
        self::TYPE_LCP => [0, 2.5 * 1000, 4.0 * 1000],
        self::TYPE_FID => [0, 100, 300,],
        self::TYPE_CLS => [0, 0.1 * 1000, 0.25 * 1000]
    ];

    /**
     * @psalm-var self::TYPE_*
     * @var string
     */
    private $type;
    /**
     * @var float
     */
    private $value;

    public function isRed():bool {
        return $this->value >= self::$thresholds[$this->type][2];
    }

    public function isYellow():bool {
        return $this->value >= self::$thresholds[$this->type][1] && $this->value < self::$thresholds[$this->type][2];
    }

    public function isGreen():bool {
        return $this->value <= self::$thresholds[$this->type][1];
    }

    public function getValue():float {
        return $this->value ?? 0;
    }

    public function getUnit() {
        switch($this->type) {
            case self::TYPE_LCP: return 'ms';
            case self::TYPE_FID: return 'ms';
            case self::TYPE_CLS: return ''; // no unit
        }
    }

    /**
     * @param float $value
     * @psalm-param self::TYPE_* $type
     */
    private function __construct($value, $type) {
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * @param float $value
     * @psalm-param self::TYPE_* $type
     * @return self
     */
    static public function forValue($value, $type):self {
        return new self($value, $type);
    }
}
