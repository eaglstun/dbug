<?php
/*
Plugin Name:    dbug
Author: 		pinecone-dot-website, postpostmodern
Author URI: 	https://rack.and.pinecone.website/
Description:    Helps with Dev'n
Domain Path:	/lang
Plugin URI:     https://github.com/pinecone-dot-website/dbug
Text Domain:	
Version:        1.9.6

This file must be parsable by php 5.2
*/

register_activation_hook( __FILE__, create_function("", '$ver = "5.4"; if( version_compare(phpversion(), $ver, "<") ) die( "This plugin requires PHP version $ver or greater be installed." );') );

require __DIR__.'/index.php';
