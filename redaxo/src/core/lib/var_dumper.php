<?php

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextualizedDumper;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @package redaxo\core
 */
abstract class rex_var_dumper
{
    /** @var VarCloner|null */
    private static $cloner;

    /** @var DataDumperInterface */
    private static $dumper;

    /**
     * @return void
     */
    public static function register()
    {
        VarDumper::setHandler(static function ($var) {
            if (rex::isDebugMode() || ($user = rex_backend_login::createUser()) && $user->isAdmin()) {
                VarDumper::setHandler([self::class, 'dump']);
                self::dump($var);

                return;
            }

            // register noop handler for non-admins (if not in debug mode)
            VarDumper::setHandler(static function () {
                // noop
            });
        });
    }

    /**
     * @return void
     */
    public static function dump($var)
    {
        if (!self::$cloner) {
            self::$cloner = new VarCloner();
            if ('cli' === PHP_SAPI) {
                $dumper = new CliDumper();
            } else {
                $styleAll = 'font-family: "Fira Code", Menlo, Monaco, Consolas, monospace; font-size: 14px; line-height: 1.4 !important;';
                $dumper = new HtmlDumper();
                $dumper->setDumpBoundaries('<pre class="rex-var-dumper sf-dump" id="%s" data-indent-pad="%s"><div class="sf-dump-rex-container">', '</div></pre><script>Sfdump(%s)</script>');
                $dumper->setIndentPad('    ');
                $dumper->setStyles([
                    'rex-container' => $styleAll . '
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
                    'default' => $styleAll . '
                        background-color: transparent;
                        color: #eeffff;
                    ',
                    'expanded' => $styleAll . '
                        white-space: pre;
                        background: unset;
                        color: inherit;
                    ',
                    'const' => $styleAll . 'color: #F78C6C; font-weight: 700;',
                    'ellipsis' => $styleAll . 'color: #eeffff;',
                    'index' => $styleAll . 'color: #C3E88D;',
                    'key' => $styleAll . 'color: #C3E88D;',
                    'meta' => $styleAll . 'color: #89DDFF;',
                    'note' => $styleAll . 'color: #FFB62C;',
                    'num' => $styleAll . 'color: #F78C6C;',
                    'protected' => $styleAll . 'color: #C792EA;',
                    'private' => $styleAll . 'color: #C792EA;',
                    'public' => $styleAll . 'color: #C792EA;',
                    'ref' => $styleAll . 'color: #eeffff;',
                    'str' => $styleAll . 'color: #FF5370;',
                ]);
            }

            $dumper->setDisplayOptions([
                'fileLinkFormat' => new class() {
                    public function format(string $file, string $line): ?string
                    {
                        /** @var rex_editor|null $editor */
                        static $editor;
                        $editor = $editor ?? rex_editor::factory();

                        return $editor->getUrl($file, $line);
                    }
                },
            ]);

            self::$dumper = new ContextualizedDumper($dumper, [new SourceContextProvider(null, rex_path::base())]);
        }

        self::$dumper->dump(self::$cloner->cloneVar($var));
    }
}
