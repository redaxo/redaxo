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
                $styleAll = 'font-family: "Fira Code", Menlo, Monaco, Consolas, monospace; font-size: 14px; line-height: 1.4 !important;';
                self::$dumper = new HtmlDumper();
                self::$dumper->setDumpBoundaries('<pre class="rex-var-dumper sf-dump" id="%s" data-indent-pad="%s">', '</pre><script>Sfdump(%s)</script>');
                self::$dumper->setIndentPad('    ');
                self::$dumper->setStyles([
                    'default' => $styleAll . '
                        position: relative;
                        z-index: 99999;
                        padding: 10px;
                        background-color: #263238;
                        border: 0;
                        color: #eeffff;
                        white-space: pre-wrap;
                        word-break: normal;
                        word-wrap: break-word;
                    ',
                    'const' => $styleAll . 'color: #F78C6C;font-weight: 700;',
                    'ellipsis' => $styleAll . 'color: #FFA500;',
                    'index' => $styleAll . 'color: #C3E88D;',
                    'key' => $styleAll . 'color: #C3E88D;',
                    'meta' => $styleAll . 'color: #800080;',
                    'note' => $styleAll . 'color: #FFB62C;',
                    'num' => $styleAll . 'color: #F78C6C;',
                    'protected' => $styleAll . 'color: #C792EA;',
                    'private' => $styleAll . 'color: #C792EA;',
                    'public' => $styleAll . 'color: #C792EA;',
                    'ref' => $styleAll . 'color: #eeffff;',
                    'str' => $styleAll . 'color: #FF5370;',
                ]);
            }
        }

        self::$dumper->dump(self::$cloner->cloneVar($var));
    }
}
