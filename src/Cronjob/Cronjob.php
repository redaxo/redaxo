<?php

namespace Redaxo\Core\Cronjob;

use Cron\CronExpression;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * A cronjob registered via {@see AsCronjob}.
 *
 * @internal
 */
final readonly class Cronjob
{
    private CronExpression $expression;

    /**
     * @param non-empty-string $name
     * @param non-empty-string $schedule
     * @param class-string $class
     */
    public function __construct(
        public string $name,
        public string $schedule,
        public string $class,
        public string $method,
        public bool $isStatic,
    ) {
        $this->expression = new CronExpression($schedule);
    }

    public function getNextRunDate(DateTimeInterface $after): DateTimeImmutable
    {
        return DateTimeImmutable::createFromMutable($this->expression->getNextRunDate($after));
    }
}
