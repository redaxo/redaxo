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
                VarDumper::setHandler(self::dump(...));
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
                $dumper->setDumpBoundaries('<pre class="rex-var-dumper sf-dump" id="%s" data-indent-pad="%s">', '</pre><script>Sfdump(%s)</script>');
                $dumper->setIndentPad('    ');
                $dumper->setStyles([
                    'default' => '
                        background-color: transparent;
                        color: #eeffff;
                    ',
                    'expanded' => '
                        white-space: pre;
                        background: unset;
                        color: inherit;
                    ',
                    'const' => 'color: #F78C6C; font-weight: 700;',
                    'ellipsis' => 'color: #eeffff;',
                    'index' => 'color: #C3E88D;',
                    'key' => 'color: #C3E88D;',
                    'meta' => 'color: #89DDFF;',
                    'note' => 'color: #FFB62C;',
                    'num' => 'color: #F78C6C;',
                    'protected' => 'color: #C792EA;',
                    'private' => 'color: #C792EA;',
                    'public' => 'color: #C792EA;',
                    'ref' => 'color: #eeffff;',
                    'str' => 'color: #FF5370;',
                    'search-wrapper' => 'margin-bottom: 10px;',
                    'search-input' => 'height: 26px !important; background-color: #f3f6fb !important;',
                    'search-count' => 'height: 26px !important; line-height: 26px !important;',
                    'search-input-previous' => 'height: 26px !important;',
                    'search-input-next' => 'height: 26px !important;',
                ]);
            }

            $dumper->setDisplayOptions([
                'fileLinkFormat' => new class() {
                    public function format(string $file, string $line): ?string
                    {
                        /** @var rex_editor|null $editor */
                        static $editor;
                        $editor ??= rex_editor::factory();

                        return $editor->getUrl($file, $line);
                    }
                },
            ]);

            self::$dumper = new ContextualizedDumper($dumper, [new SourceContextProvider(null, rex_path::base())]);
        }

        self::$dumper->dump(self::$cloner->cloneVar($var));
    }
}
