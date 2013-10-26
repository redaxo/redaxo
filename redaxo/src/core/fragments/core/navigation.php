<?php
    if ($this->navigation != '') {
        echo '
        <nav id="rex-page-navigation" class="rex-slide-container">
            <h2 class="rex-slide-legend"><a href="#">Hauptnavigation</a></h2>
            ' . $this->navigation . '
        </nav>';
    }
