<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
$sliceSelect = new rex_select();
$sliceSelect->setId('add_slice_select_pos_' . $this->position);
$sliceSelect->setSize('1');
$sliceSelect->addOption(rex_i18n::msg('add_block'), '', '0', '0',
    [
        "style"     => "display:none;",
        "class"     => "slice-select-placeholder",
        "selected"  => "selected",
        "disabled"  => "disabled"
    ]);
foreach ($this->items as $item) {
    $sliceSelect->addOption($item['title'], str_replace('&amp;', '&', $item['href']));
}
$sliceSelect->setAttribute('id', 'slice-select-pos-' . $this->position);
$sliceSelect->setAttribute('class', 'form-control selectpicker');
$sliceSelect->setAttribute('onchange', 'window.location = this.options[this.selectedIndex].value;');
if ($this->search && count($this->items) > 5) {
    $sliceSelect->setAttribute('data-live-search', 'true');
}
echo $sliceSelect->get();