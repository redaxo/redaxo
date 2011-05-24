	<div id="rex-title">
  	<div class="rex-title-row"><h1 class="rex-hl1"><?php echo $this->title; ?></h1></div>

  	<?php if ($this->subtitle == ''): ?>
  	  <div class="rex-title-row rex-title-row-sub rex-title-row-empty"><p>&nbsp;</p></div>
  	<?php else: ?>
  	  <div class="rex-title-row rex-title-row-sub"><div id="rex-navi-page"><?php echo $this->subtitle; ?></div></div>
  	<?php endif; ?>

	</div>


	<?php
  rex_extension::registerPoint('PAGE_TITLE_SHOWN', $this->subtitle,
    array(
      'category_id' => $this->category_id,
      'article_id' => $this->article_id,
      'page' => $this->page
    )
  );
	?>

	<!-- *** OUTPUT OF CONTENT - START *** -->
	<div id="rex-output">