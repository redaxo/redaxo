<div class="dropdown pull-right<?= ((isset($this->class) && $this->class != '') ? ' ' . $this->class : '') ?>">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
        <?php if (isset($this->button_prefix) && $this->button_prefix != ''): ?>
        <?= $this->button_prefix ?>
        <?php endif; ?>
        <?php if (isset($this->button_label) && $this->button_label != ''): ?>
        <?= ' <b>' . $this->button_label . '</b>' ?>
        <?php endif; ?>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
        <?php if (isset($this->header) && $this->header != ''): ?>
            <li class="dropdown-header"><?= $this->header ?></li>
        <?php endif; ?>
        <?php
        foreach ($this->items as $item) {
            echo '<li' . ((isset($item['active']) && $item['active']) ? ' class="active"' : '') . '>';
            echo (isset($item['href']) && $item['href'] != '') ? '<a href="' . $item['href'] . '">' . $item['title'] . '</a>' : $item['title'];
            echo '</li>';
        }
        ?>
        <?php if (isset($this->footer) && $this->footer != ''): ?>
            <li class="divider"></li>
            <li><?= $this->footer ?></li>
        <?php endif; ?>
    </ul>
</div>
