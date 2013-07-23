<?php

/**
 * @package redaxo\core
 */
class rex_compiler
{
    /**
     * All of the available compiler functions.
     *
     * @var array
     */
    private $compilers = [
        'Comments',
        'Echos',
        'Openings',
        'Closings',
        'Else',
        'Unless',
        'EndUnless',
        'Language',
    ];

    /**
     * Array of opening and closing tags for echos.
     *
     * @var array
     */
    private $contentTags = ['{{', '}}'];

    /**
     * Array of opening and closing tags for escaped echos.
     *
     * @var array
     */
    private $escapedTags = ['{{{', '}}}'];

    /**
     * Compile a fragment.
     *
     * @param string $value
     * @return void
     */
    public function compile($file)
    {
        if ($this->isExpired($file)) {
            $content = rex_file::get($file);
            if (rex_file::put($this->getCompiledFile($file), $this->compileString($content)) === false) {
                throw new rex_exception('Unable to generate fragment ' . $file . '!');
            }
        }

        return $this->getCompiledFile($file);
    }

    /**
     * Compile the given Fragment contents.
     *
     * @param string $value
     * @return string
     */
    public function compileString($value)
    {
        foreach ($this->compilers as $compiler) {
            $value = $this->{
                "compile{$compiler
            }"
        }($value);
        }

        return $value;
    }

    /**
     * Compile Fragment comments into valid PHP.
     *
     * @param string $value
     * @return string
     */
    protected function compileComments($value)
    {
        $pattern = sprintf('/%s--((.|\s)*?)--%s/', $this->contentTags[0], $this->contentTags[1]);

        return preg_replace($pattern, '<?php /* $1 */ ?>', $value);
    }

    /**
     * Compile Fragment echos into valid PHP.
     *
     * @param string $value
     * @return string
     */
    protected function compileEchos($value)
    {
        $pattern = [];
        $pattern[] = sprintf('/%s\s*(.+?)\s*%s/s', $this->escapedTags[0], $this->escapedTags[1]);
        $pattern[] = sprintf('/%s\s*(.+?)\s*%s/s', $this->contentTags[0], $this->contentTags[1]);

        $replace = [];
        $replace[] = '<?php echo rex_e($1); ?>';
        $replace[] = '<?php echo $1; ?>';

        return preg_replace($pattern, $replace, $value);
    }

    /**
     * Compile Fragment structure openings into valid PHP.
     *
     * @param string $value
     * @return string
     */
    protected function compileOpenings($value)
    {
        $pattern = '/(?(R)\((?:[^\(\)]|(?R))*\)|(?<!\w)(\s*)@(if|elseif|foreach|for|while)(\s*(?R)+))/';

        return preg_replace($pattern, '$1<?php $2$3: ?>', $value);
    }

    /**
     * Compile Fragment structure closings into valid PHP.
     *
     * @param string $value
     * @return string
     */
    protected function compileClosings($value)
    {
        $pattern = '/(\s*)@(endif|endforeach|endfor|endwhile)(\s*)/';

        return preg_replace($pattern, '$1<?php $2; ?>$3', $value);
    }

    /**
     * Compile Fragment else statements into valid PHP.
     *
     * @param string $value
     * @return string
     */
    protected function compileElse($value)
    {
        $pattern = $this->createPlainMatcher('else');

        return preg_replace($pattern, '$1<?php else: ?>$2', $value);
    }

    /**
     * Compile Fragment unless statements into valid PHP.
     *
     * @param string $value
     * @return string
     */
    protected function compileUnless($value)
    {
        $pattern = $this->createMatcher('unless');

        return preg_replace($pattern, '$1<?php if ( !$2): ?>', $value);
    }

    /**
     * Compile Fragment end unless statements into valid PHP.
     *
     * @param string $value
     * @return string
     */
    protected function compileEndUnless($value)
    {
        $pattern = $this->createPlainMatcher('endunless');

        return preg_replace($pattern, '$1<?php endif; ?>$2', $value);
    }

    /**
     * Compile language statements into valid PHP.
     *
     * @param string $value
     * @return string
     */
    protected function compileLanguage($value)
    {
        $pattern = $this->createMatcher('lang');

        return preg_replace($pattern, '$1<?php echo rex_i18n::msg$2; ?>', $value);
    }

    /**
     * Get the regular expression for a generic Fragment function.
     *
     * @param string $function
     * @return string
     */
    public function createMatcher($function)
    {
        return '/(?<!\w)(\s*)@' . $function . '(\s*\(.*\))/';
    }

    /**
     * Create a plain Fragment matcher.
     *
     * @param string $function
     * @return string
     */
    public function createPlainMatcher($function)
    {
        return '/(?<!\w)(\s*)@' . $function . '(\s*)/';
    }

    /**
     * Get the path to the compiled version of a fragment.
     *
     * @param string $path
     * @return string
     */
    public function getCompiledFile($file)
    {
        return rex_path::cache($file);
    }

    /**
     * Determine if the fragment at the given path is expired.
     *
     * @param string $path
     * @return bool
     */
    public function isExpired($file)
    {
        $compiled = $this->getCompiledFile($file);

        if (!file_exists($compiled)) {
            return true;
        }

        $lastModified = filemtime($file);

        return $lastModified >= filemtime($compiled);
    }
}
