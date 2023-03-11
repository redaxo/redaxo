<?php

namespace Redaxo\Core\Fragment;

use Closure;
use rex_exception;
use rex_fragment;
use rex_timer;
use rex_type;

use function is_array;
use function is_string;

abstract class Fragment
{
    public function render(): string
    {
        $path = $this->getPath();
        $fullPath = $this->resolvePath($path);

        $closure = Closure::bind(function () use ($fullPath) {
            ob_start();
            require $fullPath;

            return rex_type::string(ob_get_clean());
        }, $this, static::class);
        rex_type::instanceOf($closure, Closure::class);

        $ouput = rex_timer::measure('Fragment: '.$path, $closure);

        return rex_type::string($ouput);
    }

    protected function getPath(): string
    {
        throw new rex_exception('Missing fragment path for fragment class "'.static::class.'"');
    }

    public static function resolvePath(string $path): string
    {
        foreach (rex_fragment::getDirectories() as $fragDir) {
            $fragment = $fragDir.$path;
            if (!is_file($fragment)) {
                continue;
            }

            return $fragment;
        }

        throw new rex_exception(sprintf('Fragment file "%s" not found!', $path));
    }

    public static function slot(string|self|null $content, ?string $name = null): string
    {
        if (null === $content) {
            return '';
        }

        if (is_string($content)) {
            $content = rex_escape($content);

            return $name ? '<div slot="'.rex_escape($name).'">'.$content.'</div>' : $content;
        }

        if (null === $name) {
            return $content->render();
        }

        if (isset($content->attributes) && is_array($content->attributes)) {
            /** @psalm-suppress UndefinedPropertyAssignment */
            $content->attributes['slot'] = $name;

            return $content->render();
        }

        $content = trim($content->render());

        $count = 0;
        $content = preg_replace('/^(<[a-z-]+)/', '$1 slot="'.rex_escape($name).'"', $content, count: $count);

        if (1 !== $count) {
            throw new rex_exception('The content of the slot "'.$name.'" must start with an HTML element');
        }

        return $content;
    }
}
