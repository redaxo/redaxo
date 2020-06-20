<?php

final class rex_analytics_metric {
    const TYPE_FID = 'FID', TYPE_LCP = 'LCP', TYPE_CLS = 'CLS';

    private static $thresholds = [
        self::TYPE_LCP => [0, 2.5, 4.0],
        self::TYPE_FID => [0, 100, 300,],
        self::TYPE_CLS => [0, 0.1, 0.25]
    ];

    /**
     * @var self::TYPE_*
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

    /**
     * @param float $value
     * @param self::TYPE_* $type
     * @return self
     */
    static public function fromValue($value, $type):self {
        $metric = new self();
        $metric->value = $value;
        $metric->type = $type;
        return $metric;
    }
}
