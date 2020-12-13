<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>
<?php

$this->content = is_string($this->content) ? [$this->content] : $this->content;

$count = count($this->content);

$this->classes = isset($this->classes) && (is_array($this->classes) && count($this->classes) == $count) ? $this->classes : [];

switch ($count) {
    case '4':
        echo '<div class="row">';

        foreach ($this->content as $key => $content) {
            echo '<div class="' . ($this->classes[$key] ?? 'col-sm-6 col-md-3') . '">' . $content . '</div>';
        }

        echo '</div>';

        break;

    case '3':
        echo '<div class="row">';

        foreach ($this->content as $key => $content) {
            echo '<div class="' . ($this->classes[$key] ?? 'col-md-4') . '">' . $content . '</div>';
        }

        echo '</div>';

        break;

    case '2':
        echo '<div class="row">';

        foreach ($this->content as $key => $content) {
            echo '<div class="' . ($this->classes[$key] ?? 'col-md-6') . '">' . $content . '</div>';
        }

        echo '</div>';

        break;

    default:
        foreach ($this->content as $content) {
            echo $content;
        }

        break;
}
