<?php

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @package redaxo\core
 */
abstract class rex_var_dumper
{
    /** @var VarCloner */
    private static $cloner;

    /** @var DataDumperInterface */
    private static $dumper;

    public static function register()
    {
        VarDumper::setHandler(function ($var) {
            if (rex::isDebugMode() || ($user = rex_backend_login::createUser()) && $user->isAdmin()) {
                VarDumper::setHandler('self::dump');
                self::dump($var);

                return;
            }

            // register noop handler for non-admins (if not in debug mode)
            VarDumper::setHandler(function ($var) {
                // noop
            });
        });
    }

    public static function dump($var)
    {
        if (!self::$cloner) {
            self::$cloner = new VarCloner();
            if ('cli' === PHP_SAPI) {
                self::$dumper = new CliDumper();
            } else {
                self::$dumper = new HtmlDumper();
                self::$dumper->setDumpBoundaries('<pre class="rex-var-dumper sf-dump" id="%s" data-indent-pad="%s">', '</pre><script>Sfdump(%s)</script>');
                self::$dumper->setIndentPad('    ');
                self::$dumper->setStyles([
                    'default' => '',
                    'num' => '',
                    'const' => '',
                    'str' => '',
                    'note' => '',
                    'ref' => '',
                    'public' => '',
                    'protected' => '',
                    'private' => '',
                    'meta' => '',
                    'key' => '',
                    'index' => '',
                    'ellipsis' => '',
                ]);
            }
        }

        self::$dumper->dump(self::$cloner->cloneVar($var));
    }
}
