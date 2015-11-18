        <?php if ($this->pager->getRowCount() > $this->pager->getRowsPerPage()): ?>
            <nav class="rex-nav-pagination">
                <ul class="pagination">
                    <?php $class = ($this->pager->isActivePage($this->pager->getFirstPage())) ? ' disabled' : ''; ?>
                    <li class="rex-prev<?php echo $class; ?>"><a href="<?php echo $this->urlprovider->getUrl([$this->pager->getCursorName() => $this->pager->getCursor($this->pager->getPrevPage())]) ?>" title="<?php echo $this->i18n('list_previous') ?>"><i class="rex-icon rex-icon-previous"></i><span class="sr-only"><?php echo $this->i18n('list_previous') ?></span></a></li>
                    <?php for ($page = $this->pager->getFirstPage(); $page <= $this->pager->getLastPage(); ++$page): ?>
                        <?php $class = ($this->pager->isActivePage($page)) ? ' active' : ''; ?>
                        <li class="rex-page<?php echo $class ?>"><a href="<?php echo $this->urlprovider->getUrl([$this->pager->getCursorName() => $this->pager->getCursor($page)]) ?>"><?php echo($page + 1) ?></a></li>
                    <?php endfor; ?>
                    <?php $class = ($this->pager->isActivePage($this->pager->getLastPage())) ? ' disabled' : ''; ?>
                    <li class="rex-next<?php echo $class; ?>"><a href="<?php echo $this->urlprovider->getUrl([$this->pager->getCursorName() => $this->pager->getCursor($this->pager->getNextPage())]) ?>" title="<?php echo $this->i18n('list_next') ?>"><span class="sr-only"><?php echo $this->i18n('list_next') ?></span><i class="rex-icon rex-icon-next"></i></a></li>
                </ul>
                <div class="rex-pagination-count"><?php echo $this->i18n('list_rows_found', $this->pager->getRowCount()) ?></div>
            </nav>
        <?php endif;
