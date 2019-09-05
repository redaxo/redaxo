<?php if (isset($this->type) && $this->type == rex_structure_field_group::HEADER): ?>
    <th <?=isset($this->attributes) ? rex_string::buildAttributes($this->attributes) : '';?>>
        <?=$this->fields;?>
    </th>
<?php else: ?>
    <td <?=isset($this->attributes) ? rex_string::buildAttributes($this->attributes) : '';?>>
        <?=$this->fields;?>
    </td>
<?php endif;?>
