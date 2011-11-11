
  <div class="rex-navi-path">
  <dl>
	 	<?php foreach($this->list['items'] as $key => $value): ?>
		<dt><?php echo $key; ?></dt>
		<dd><?php echo $this->subfragment('core_navi', array('lists' => $value)); ?></dd>
		<?php endforeach; ?>
	</dl>
  </div>