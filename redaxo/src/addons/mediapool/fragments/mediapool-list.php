<div class="mediapool-list is-grid">
<?php foreach ($this->getVar('elements') as $element): ?>
    <?= $this->subfragment('mediapool-element.php', $element); ?>
<?php endforeach; ?>
</div>
