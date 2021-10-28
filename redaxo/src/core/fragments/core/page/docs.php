<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */

$content = $this->getVar('content');
preg_match_all('@<pre(?: [^>]*)?><code(?: [^>]*)?>(.*?)</code></pre>@s', $content, $matches, PREG_SET_ORDER);
if (count($matches)) {
    $search = [];
    $replace = [];
    foreach ($matches as $match) {
        $search[] = $match[0];
        $replace[] = str_replace("\n", '', '<pre>'.highlight_string(html_entity_decode($match[1]), true).'</pre>');
    }
    $content = str_replace($search, $replace, $content);
}
?>
<div class="rex-docs">
    <?php if ($this->getVar('sidebar') || $this->getVar('toc')): ?>
        <div class="rex-docs-sidebar">
            <?php if ($this->getVar('toc')): ?>
            <nav class="rex-nav-toc"><?= $this->getVar('toc') ?></nav>
            <?php endif ?>
            <?= $this->getVar('sidebar') ?>
        </div>
    <?php endif ?>
    <article class="rex-docs-content"><?= $content ?></article>
</div>
