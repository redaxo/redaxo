<?php
/** @todo Styles auslagern */
?>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); grid-auto-rows: 1fr; margin: 0 -1rem;">
<?php foreach ($this->getVar('elements') as $element): ?>
    <?= $this->subfragment('mediapool-element.php', $element); ?>
<?php endforeach; ?>
</div>

