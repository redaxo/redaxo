
  <ul id="rex-navi-path">
	 	<?php foreach($this->list['items'] as $key => $values): ?>
		<li><?php echo $key; ?></li>
		 	<?php foreach($values as $value): ?>
		 	<?php foreach($value['items'] as $item): ?>
  		<li><?php echo $item['content']; ?></li>
  		<?php endforeach; ?>
  		<?php endforeach; ?>
		<?php endforeach; ?>
	</ul>

  <?php /*
  <div class="rex-navi-path">
  <dl>
	 	<?php foreach($this->list['items'] as $key => $value): ?>
		<dt><?php echo $key; ?></dt>
		<dd><?php echo $this->subfragment('core_navi', array('lists' => $value)); ?></dd>
		<?php endforeach; ?>
	</dl>
  </div>
  */ ?>