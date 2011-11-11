    <?php if($this->pager->getRowCount() > $this->pager->getRowsPerPage()): ?>
    <div class="rex-section rex-toolbar">

        <ul class="rex-navi-paginate">
        	<li class="rex-first rex-prev"><a href="<?php echo $this->urlprovider->getUrl(array($this->pager->getCursorName() => $this->pager->getCursor($this->pager->getPrevPage()))) ?>" title="<?php echo $this->i18n('list_previous') ?>"><span><?php echo $this->i18n('list_previous') ?></span></a></li>
          <?php for($page = $this->pager->getFirstPage(); $page < $this->pager->getLastPage(); $page++): ?>
            <?php $class = ($this->pager->isActivePage($page)) ? ' class="rex-active"' : ''; ?>
            <li><a href="<?php echo $this->urlprovider->getUrl(array($this->pager->getCursorName() => $this->pager->getCursor($page))) ?>"<?php echo $class ?>><span><?php echo ($page+1) ?></span></a></li>
          <?php endfor; ?>
          <li class="rex-next"><a href="<?php echo $this->urlprovider->getUrl(array($this->pager->getCursorName() => $this->pager->getCursor($this->pager->getNextPage()))) ?>" title="<?php echo $this->i18n('list_next') ?>"><span><?php echo $this->i18n('list_next') ?></span></a></li>
          <li class="rex-last"><span><?php echo $this->i18n('list_rows_found', $this->pager->getRowCount()) ?></span></li>
        </ul>

    </div>
    <?php endif; ?>