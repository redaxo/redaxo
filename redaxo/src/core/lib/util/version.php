<?php

/**
 * @author staabm
 *
 * @package redaxo\core
 */
class rex_version
{
    private function __construct()
    {
        // noop
    }

    public static function isUnstable(string $version): bool
    {
        // see https://www.php.net/manual/en/function.version-compare.php
        return (bool) preg_match('/(?<![a-z])(?:dev|alpha|a|beta|b|rc|pl)(?![a-z])/i', $version);
    }

    /**
     * Splits a version string into its parts.
     *
     * @return list<string>
     */
    public static function split(string $version): array
    {
        return preg_split('/(?<=\d)(?=[a-z])|(?<=[a-z])(?=\d)|[ ._-]+/i', $version);
    }

    /**
     * Compares two version number strings.
     *
     * In contrast to version_compare() it treats "1.0" and "1.0.0" as equal and it supports a space as separator for
     * the version parts, e.g. "1.0 beta1"
     *
     * @see http://www.php.net/manual/en/function.version-compare.php
     *
     * @param string $version1   First version number
     * @param string $version2   Second version number
     * @param null|'='|'=='|'!='|'<>'|'<'|'<='|'>'|'>=' $comparator Optional comparator
     *
     * @return bool
     */
    public static function compare(string $version1, string $version2, ?string $comparator = '<')
    {
        // bc
        $comparator = $comparator ?? '<';

        if (!in_array($comparator, ['=', '==', '!=', '<>', '<', '<=', '>', '>='], true)) {
            throw new InvalidArgumentException(sprintf('Unknown comparator "%s".', $comparator));
        }

        $version1 = self::split($version1);
        $version2 = self::split($version2);
        $max = max(count($version1), count($version2));
        $version1 = implode('.', array_pad($version1, $max, '0'));
        $version2 = implode('.', array_pad($version2, $max, '0'));

        return version_compare($version1, $version2, $comparator);
    }

    /**
     * Returns the current git version hash for the given path.
     *
     * @param string      $path A local filesystem path
     * @param null|string $repo If given, the version hash is returned only if the remote repository matches the
     *                          given github repo (e.g. `redaxo/redaxo`)
     */
    public static function gitHash($path, ?string $repo = null): ?string
    {
        if (!function_exists('exec')) {
            return null;
        }

        $output = [];
        $exitCode = -1;

        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            $command = 'where git';
        } else {
            $command = 'which git';
        }

        $git = @exec($command, $output, $exitCode);

        if (0 !== $exitCode) {
            return null;
        }

        if (null !== $repo) {
            $command = 'cd '. escapeshellarg($path).' && '.escapeshellarg($git).' ls-remote --get-url';
            $remote = @exec($command, $output, $exitCode);

            if (0 !== $exitCode || !preg_match('{github.com[:/]'.preg_quote($repo).'\.git$}i', $remote)) {
                return null;
            }
        }

        $command = 'cd '. escapeshellarg($path).' && '.escapeshellarg($git).' log -1 --pretty=format:%h';
        $version = @exec($command, $output, $exitCode);

        return 0 === $exitCode ? $version : null;
    }
}
