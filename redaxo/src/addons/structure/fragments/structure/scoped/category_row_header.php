<?php

echo '
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th class="rex-table-icon">' . $this->add_category . '</th>
                        <th class="rex-table-id">' . rex_i18n::msg('header_id') . '</th>
                        <th class="rex-table-category">' . rex_i18n::msg('header_category') . '</th>
                        <th class="rex-table-priority">' . rex_i18n::msg('header_priority') . '</th>
                        <th class="rex-table-action" colspan="'.$this->colspan.'">' . rex_i18n::msg('header_status') . '</th>
                    </tr>
                </thead>
                <tbody>';
