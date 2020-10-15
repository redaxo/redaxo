<?php
$context = $this->getVar('context');
?>

<sl-button-group>
<?php foreach (rex_clang::getAll() as $id => $clang): ?>
    <?php if (!rex::getUser()->getComplexPerm('clang')->hasPerm($id)): ?>
        <?php continue; ?>
    <?php endif; ?>
    <sl-button<?= ($id == $context->getParam('clang') ? ' type="primary"' : '') ?> href="<?= $context->getUrl(['clang' => $id]) ?>"><?= rex_i18n::translate($clang->getName()) ?></sl-button>
<?php endforeach; ?>
</sl-button-group>
