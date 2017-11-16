<?php

namespace WP_Dbug;

if (file_exists(__DIR__.'/vendor/autoload.php')) {
    require __DIR__.'/vendor/autoload.php';
}

// dont pollute globals
call_user_func( function () {
    $dbug = Dbug::instance();

    if (is_admin()) {
        new Admin($dbug);
    }
});
