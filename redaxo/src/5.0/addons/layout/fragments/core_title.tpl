
	<div class="rex-section" id="rex-title">

		<div class="rex-header">
			<h1><?php echo $this->title; ?></h1>
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
	</div>