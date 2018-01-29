<?php
/*
Plugin Name:    dbug
Author: 		pinecone-dot-website, postpostmodern
Author URI: 	https://rack.and.pinecone.website/
Description:    Helps with Dev'n
Domain Path:	/lang
Plugin URI:     https://github.com/pinecone-dot-website/dbug
Text Domain:	
Version:        1.9.8

This file must be parsable by php 5.2
*/

if (version_compare(phpversion(), '5.4', "<")) {
    add_action('admin_notices', create_function("", 'function(){
        echo "<div class=\"notice notice-success is-dismissible\">
                <p>Dbug requires PHP 5.4 or greater</p>
              </div>";
    };'));
} else {
    require __DIR__.'/index.php';
}
