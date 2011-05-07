	<div id="rex-title">
  		<div class="rex-title-row"><h1 class="rex-hl1"><?php echo $this->title; ?></h1></div>
  		<?php echo $this->subtitle; ?>
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