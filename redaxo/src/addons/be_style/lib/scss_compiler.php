<?php

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Formatter\Compressed;

/**
 * @package redaxo\be-style
 */
class rex_scss_compiler
{
    protected $root_dir;
    protected $scss_file;
    protected $css_file;
    protected $formatter;

    public function __construct()
    {
        $this->root_dir = rex_path::addon('be_style');
        $this->scss_file = rex_path::addon('be_style', 'assets') . 'styles.scss';
        $this->css_file = rex_path::addon('be_style', 'assets') . 'styles.css';
        $this->formatter = Compressed::class;
    }

    public function setRootDir($value)
    {
        $this->root_dir = $value;
    }

    public function setScssFile($value)
    {
        $this->scss_file = $value;
    }

    public function setCssFile($value)
    {
        $this->css_file = $value;
    }

    /**
     * @param string $value scss_formatter (default) or scss_formatter_nested or scss_formatter_compressed
     */
    public function setFormatter($value)
    {
        $this->formatter = $value;
    }

    public function compile()
    {
        // go on even if user "stops" the script by closing the browser, closing the terminal etc.
        ignore_user_abort(true);
        // set script running time to unlimited
        set_time_limit(0);

        $root_dir = $this->root_dir;

        $scss_compiler = new Compiler();
        $scss_compiler->setNumberPrecision(10);

        $scss_compiler->addImportPath(static function ($path) use ($root_dir) {
            $path = $root_dir . $path . '.scss';

            $path_parts = pathinfo($path);
            $underscore_file = $path_parts['dirname'] . '/_' . $path_parts['basename'];

            if (file_exists($underscore_file)) {
                $path = $underscore_file;
            }

            if (!file_exists($path)) {
                return null;
            }

            return $path;
        });
        // set the path to your to-be-imported mixins. please note: custom paths are coming up on future releases!
        //$scss_compiler->setImportPaths($scss_folder);

        // set css formatting (normal, nested or minimized), @see http://leafo.net/scssphp/docs/#output_formatting
        $scss_compiler->setFormatter($this->formatter);

        // get .scss's content, put it into $string_sass
        $string_sass = '';
        if (is_array($this->scss_file)) {
            foreach ($this->scss_file as $scss_file) {
                $string_sass .= rex_file::get($scss_file);
            }
        } else {
            $string_sass = rex_file::get($this->scss_file);
        }

        // try/catch block to prevent script stopping when scss compiler throws an error
        try {
            // compile this SASS code to CSS
            $string_css = $scss_compiler->compile($string_sass) . "\n";

            // $string_css = csscrush_string($string_css, $options = array('minify' => true));

            // write CSS into file with the same filename, but .css extension
            rex_file::put($this->css_file, $string_css);
        } catch (Exception $e) {
            // here we could put the exception message, but who cares ...
            echo $e->getMessage();
            exit();
        }
    }
}
