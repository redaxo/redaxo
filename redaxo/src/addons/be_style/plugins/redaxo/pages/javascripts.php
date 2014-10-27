<?php

    $files = rex_request('be_style_' . $mypage . '_js_files', 'array');
    
    if (is_array($files) && count($files) > 0) {

        foreach ($files as $file) {
            
            rex_response::sendFile(rex_path::addon('be_style') . $file, 'text/javascript');

        }

        exit();

    }