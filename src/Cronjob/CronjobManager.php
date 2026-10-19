<?php

namespace Redaxo\Core\Cronjob;

use DateTimeImmutable;
use LogicException;
use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Env;
use Redaxo\Core\Exception\InvalidArgumentException;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Log\LogFile;
use Redaxo\Core\Log\Logger;
use Throwable;

use function array_diff;
use function array_filter;
use function array_keys;
use function array_map;
use function array_slice;
use function array_values;
use function explode;
use function get_debug_type;
use function in_array;
use function is_string;
use function sprintf;

/**
 * Executes the cronjobs registered via {@see AsCronjob} and keeps their state in the table `rex_cronjob`.
 *
 * @internal
 */
final class CronjobManager
{
    /** A run that has not finished after this many seconds is considered dead, so the job may start again. */
    private const int STALE_EXECUTION = 6 * 60 * 60;

    /** @var array{cronjob: Cronjob, trigger: 'schedule'|'manual'}|null */
    private static ?array $running = null;
    private static bool $shutdownRegistered = false;

    /**
     * Executes all due cronjobs which are enabled by `REX_CRONJOBS`.
     *
     * @return list<CronjobResult>
     */
    public static function runDue(): array
    {
        $cronjobs = CronjobRegistry::all();
        $states = self::getStates();

        $now = new DateTimeImmutable();
        $results = [];

        foreach ($cronjobs as $name => $cronjob) {
            if (!self::isEnabled($name)) {
                continue;
            }

            $state = $states[$name] ?? null;

            if (null === $state) {
                // A new cronjob waits for its first scheduled time instead of starting with the next run
                self::createState($name, $now);
                continue;
            }

            if ($cronjob->getNextRunDate($state['lastRun'] ?? $now) > $now) {
                continue;
            }

            if (!self::acquire($name, $state['lastRun'], $now)) {
                continue;
            }

            $results[] = self::execute($cronjob, 'schedule');
        }

        return $results;
    }

    /** Executes the cronjob immediately, regardless of its schedule and `REX_CRONJOBS`. */
    public static function run(Cronjob $cronjob): CronjobResult
    {
        $now = new DateTimeImmutable();
        self::createState($cronjob->name, $now);

        if (!self::acquire($cronjob->name, null, $now)) {
            return new CronjobResult($cronjob->name, false, 'The cronjob is already running.');
        }

        return self::execute($cronjob, 'manual');
    }

    /** Whether the cronjob runs on schedule on this instance, as controlled by the env var `REX_CRONJOBS`. */
    public static function isEnabled(string $name): bool
    {
        $setting = self::getSetting();

        return $setting['all'] !== in_array($name, $setting['names'], true);
    }

    /**
     * Returns the names listed in `REX_CRONJOBS` that do not belong to any cronjob.
     *
     * @return list<string>
     */
    public static function getUnknownNames(): array
    {
        return array_values(array_diff(self::getSetting()['names'], array_keys(CronjobRegistry::all())));
    }

    /**
     * Parses the env var `REX_CRONJOBS`: `all` (also if unset), `all` followed by excluded names (`all,-foo,-bar`),
     * `none` or the names of the only cronjobs to run (`foo,bar`).
     *
     * @return array{all: bool, names: list<string>} `all` with the excluded names, or only the given names
     */
    public static function getSetting(): array
    {
        $value = Env::get('REX_CRONJOBS') ?? 'all';
        $parts = array_values(array_filter(array_map(trim(...), explode(',', $value)), static fn (string $part) => '' !== $part));

        if (['none'] === $parts) {
            return ['all' => false, 'names' => []];
        }

        $all = 'all' === ($parts[0] ?? 'all');
        $names = [];
        foreach ($all ? array_slice($parts, 1) : $parts as $part) {
            $name = $all ? substr($part, 1) : $part;
            if ($all !== str_starts_with($part, '-') || '' === $name || in_array($name, ['all', 'none'], true)) {
                throw new InvalidArgumentException(sprintf('The env var "REX_CRONJOBS" must be "all", "none", "all" followed by excluded names ("all,-foo") or a list of names ("foo,bar"), "%s" given.', $value));
            }
            $names[] = $name;
        }

        return ['all' => $all, 'names' => $names];
    }

    /** @return array<string, array{lastRun: ?DateTimeImmutable, lastSuccess: ?bool, running: bool}> */
    public static function getStates(): array
    {
        $sql = Sql::factory();
        $sql->setQuery('SELECT name, last_run, last_success, execution_start FROM rex_cronjob');

        $states = [];
        foreach ($sql as $row) {
            $lastRun = $row->getDateTimeValue('last_run');
            $lastSuccess = $row->getValue('last_success');
            $states[(string) $row->getValue('name')] = [
                'lastRun' => null === $lastRun ? null : new DateTimeImmutable()->setTimestamp($lastRun),
                'lastSuccess' => null === $lastSuccess ? null : (bool) $lastSuccess,
                'running' => null !== $row->getValue('execution_start'),
            ];
        }

        return $states;
    }

    /** Removes the state of cronjobs which no longer exist in code. */
    public static function removeOrphanedStates(): void
    {
        $orphaned = array_values(array_diff(array_keys(self::getStates()), array_keys(CronjobRegistry::all())));

        if ([] === $orphaned) {
            return;
        }

        $sql = Sql::factory();
        $sql->setQuery('DELETE FROM rex_cronjob WHERE name IN (' . $sql->in($orphaned) . ')');
    }

    private static function getLogFile(): LogFile
    {
        return LogFile::factory(Path::log('cronjob.log'), 2_000_000);
    }

    private static function createState(string $name, DateTimeImmutable $now): void
    {
        Sql::factory()->setQuery(
            'INSERT IGNORE INTO rex_cronjob (name, last_run) VALUES (?, ?)',
            [$name, Sql::datetime($now->getTimestamp())],
        );
    }

    /**
     * Marks the cronjob as running, unless another process has started it in the meantime.
     *
     * @param DateTimeImmutable|null $seenLastRun The last run as seen when the cronjob was found to be due, so
     *     that it is not run a second time by a concurrent process that started after the first one finished
     */
    private static function acquire(string $name, ?DateTimeImmutable $seenLastRun, DateTimeImmutable $now): bool
    {
        $params = [
            'now' => Sql::datetime($now->getTimestamp()),
            'name' => $name,
            'stale' => Sql::datetime($now->getTimestamp() - self::STALE_EXECUTION),
        ];

        $where = 'name = :name AND (execution_start IS NULL OR execution_start < :stale)';
        if (null !== $seenLastRun) {
            $where .= ' AND last_run = :last_run';
            $params['last_run'] = Sql::datetime($seenLastRun->getTimestamp());
        }

        $sql = Sql::factory();
        $sql->setQuery('UPDATE rex_cronjob SET execution_start = :now, last_run = :now WHERE ' . $where, $params);

        return 1 === $sql->getRows();
    }

    /** @param 'schedule'|'manual' $trigger */
    private static function execute(Cronjob $cronjob, string $trigger): CronjobResult
    {
        self::registerShutdown();
        self::$running = ['cronjob' => $cronjob, 'trigger' => $trigger];

        try {
            $message = self::call($cronjob);
            $success = true;
        } catch (Throwable $exception) {
            Logger::logException($exception);
            $message = $exception->getMessage();
            $success = false;
        } finally {
            self::$running = null;
        }

        return self::finish($cronjob->name, $success, $message, $trigger);
    }

    private static function call(Cronjob $cronjob): string
    {
        if ($cronjob->isStatic) {
            /** @var callable(): mixed $callable */
            $callable = [$cronjob->class, $cronjob->method];
        } else {
            /** @var callable(): mixed $callable */
            $callable = [self::getInstance($cronjob->class), $cronjob->method];
        }

        $result = $callable();

        if (null !== $result && !is_string($result)) {
            throw new LogicException(sprintf('The cronjob method "%s::%s()" must return a string or null, %s returned.', $cronjob->class, $cronjob->method, get_debug_type($result)));
        }

        return $result ?? '';
    }

    /**
     * Returns the project or addon instance the non-static cronjob method is called on.
     *
     * @param class-string $class
     */
    private static function getInstance(string $class): object
    {
        $project = Core::getProject();
        if ($project instanceof $class) {
            return $project;
        }

        foreach (Addon::getAll() as $addon) {
            if ($addon instanceof $class) {
                return $addon;
            }
        }

        throw new LogicException(sprintf('Non-static cronjob methods of "%s" cannot be called: neither the project nor an available addon is an instance of it.', $class));
    }

    /** @param 'schedule'|'manual' $trigger */
    private static function finish(string $name, bool $success, string $message, string $trigger): CronjobResult
    {
        $sql = Sql::factory();
        $sql->setTable('rex_cronjob');
        $sql->setWhere(['name' => $name]);
        $sql->setValue('execution_start', null);
        $sql->setValue('last_success', $success ? 1 : 0);
        $sql->update();

        self::getLogFile()->add([$success ? 'SUCCESS' : 'ERROR', $name, $message, $trigger]);

        return new CronjobResult($name, $success, $message);
    }

    /**
     * Fatal errors end the process without reaching the regular end of {@see self::execute()}, so the
     * cronjob is finished as failed in a shutdown function.
     */
    private static function registerShutdown(): void
    {
        if (self::$shutdownRegistered) {
            return;
        }
        self::$shutdownRegistered = true;

        register_shutdown_function(static function (): void {
            if (null === self::$running) {
                return;
            }

            $message = error_get_last()['message'] ?? 'Unknown error';

            self::finish(self::$running['cronjob']->name, false, $message, self::$running['trigger']);
            self::$running = null;
        });
    }
}
