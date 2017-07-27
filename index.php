<?php

namespace WP_Dbug;

if (!function_exists('WP_Dbug\version')) {
    require __DIR__.'/autoload.php';
}

Dbug::setup();

if (is_admin()) {
    new Admin;
}
