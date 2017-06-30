<?php
/*
Plugin Name: dbug
Plugin URI: https://wordpress.org/extend/plugins/dbug/
Description: Helps with Dev'n
Author: Eric Eaglstun
Version: 1.9.3
Author URI: http://ericeaglstun.com

This file must be parsable by php 5.2
*/

register_activation_hook( __FILE__, create_function("", '$ver = "5.3"; if( version_compare(phpversion(), $ver, "<") ) die( "This plugin requires PHP version $ver or greater be installed." );') );

require __DIR__.'/index.php';
