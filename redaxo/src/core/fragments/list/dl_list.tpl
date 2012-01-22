	
 	<?php foreach($this->lists as $list): ?>
  <dl<?php foreach($list['attributes'] as $key => $value) echo ' '.$key.'="'.$value.'"'; ?>>
	 	<?php foreach($list['entries'] as $term => $description): ?>
		<dt><?php echo $term; ?></dt>
		<dd><?php echo $description; ?></dd>
		<?php endforeach; ?>
	</dl>
	<?php endforeach; ?>