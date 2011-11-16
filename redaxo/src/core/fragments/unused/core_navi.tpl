
	<?php foreach($this->lists as $list): ?>
  <ul>
 		<?php
 		$c = 0;
    $count = count($list['items']);
 		foreach($list['items'] as $item): $c++; ?>
  		<li<?php 
        if ($c == 1 && !isset($item['attributes']))
          $item['attributes']['class'] = '';
        if ($c == $count && !isset($item['attributes']))
          $item['attributes']['class'] = '';

        if (isset($item['attributes'])):
          foreach($item['attributes'] as $key => $value):
            $value = ($c == 1 && $key == 'class') ? $value .= ' rex-first' : $value;
            $value = ($c == $count && $key == 'class') ? $value .= ' rex-last' : $value;
            echo ' '.$key.'="'.trim($value).'"';
          endforeach;
        endif; ?>><?php echo $item['content']; ?></li>
		<?php endforeach; ?>
	</ul>
	<?php endforeach; ?>