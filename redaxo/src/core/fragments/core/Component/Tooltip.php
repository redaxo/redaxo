<?php

use Redaxo\Core\Fragment\Component\Tooltip;
use Redaxo\Core\Fragment\Fragment;

/** @var Tooltip $this */
?>
<sl-tooltip <?= $this->attributes->with([
    'content' => is_string($this->content) ? $this->content : null,
    'placement' => $this->placement,
    'disabled' => $this->disabled,
    'distance' => $this->distance,
    'open' => $this->open,
    'skidding' => $this->skidding,
    'trigger' => $this->trigger,
]) ?>>
    <?= $this->content instanceof Fragment ? Fragment::slot($this->content, 'content') : '' ?>
    <?= Fragment::slot($this->body) ?>
</sl-tooltip>
