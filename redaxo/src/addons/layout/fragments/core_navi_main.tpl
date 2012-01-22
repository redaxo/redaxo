
	<?php foreach($this->lists as $list): ?>
  <ul<?php foreach($list['attributes'] as $key => $value) echo ' '.$key.'="'.$value.'"'; ?>>
 		<?php
 		$c = 0;
 		foreach($list['entries'] as $entry): $c++; ?>
		<li<?php echo ($c == 1) ? ' class="rex-first"' : ''; ?>><?php echo $entry; ?></li>
		<?php endforeach; ?>
	</ul>
	<?php endforeach; ?>