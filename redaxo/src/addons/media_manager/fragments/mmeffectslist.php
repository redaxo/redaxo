<?php

    // $this->class         Klasse für die Content-gruppe
    // $this->visible      true/false
    // $this->content       [id] = [ label, content ]

    foreach( $this->content as $k=>$v )
    {
        echo '<div class="panel panel-default'.(isset($this->class)?(' '.$this->class):'').'">';
        if( $v['label'] ) echo "<div class=\"panel-heading\">{$v['label']}</div>";
        echo '<dl class="small panel-body'.(isset($this->visible)&&$this->visible?'':' hidden').'">';
        foreach( $v['effects'] as $ek=>$ev )
        {
            echo "<dt>$ek</dt><dd>$ev</dd>";
        }
        echo '</dl>';
        echo '</div>';
    }
