<?php

namespace WP_Dbug;

if (!function_exists('WP_Dbug\version')) {
    require __DIR__.'/autoload.php';
}

call_user_func( function () {
    $dbug = Dbug::instance();

    if (is_admin()) {
        new Admin($dbug);
    }
});
