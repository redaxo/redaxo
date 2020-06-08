<?php

rex_sql_table::get(rex::getTable('webvitals'))->drop();
rex_sql_table::get(rex::getTable('webvitals_95p'))->drop();
