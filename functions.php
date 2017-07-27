<?php

namespace WP_Dbug;

/**
*   create the log directory if it does not exist
*   default to /logs/ in wordpress root
*   @TODO find a better way to make sure the path is writeable and valid
*   @TODO fix error when log path is not on same server.
*   @param string
*   @return string absolute path to directory or FALSE
*/
function check_log_dir($dir)
{
    if (!is_dir($dir)) {
        $wp_upload_dir = wp_upload_dir();
        $dir = $wp_upload_dir['basedir'].'/logs/';
    }
    
    $pathinfo = pathinfo( $dir );
    $dirname = isset( $pathinfo['dirname'] ) ? $pathinfo['dirname'] : null;
    if (!is_dir($dirname)) {
        return false;
    }
    
    // force trailing slash!
    if (strrpos($dir, '/') != (strlen($dir)-1)) {
        $dir .= '/';
    }
    
    // make directory if it doesnt exist
    if (!is_dir($dir)) {
        wp_mkdir_p( $dir, 0755 );
    }
    
    // change permissions if we cant write to it
    if (!is_writable($dir)) {
        @chmod( $dir, 0755 );
    }
    
    // test and make sure we can write to it
    if (!is_dir($dir) || !is_writable($dir)) {
        return false;
    }
    
    // make sure htaccess is in place to protect log files
    if (!file_exists($dir.'.htaccess') && file_exists(__DIR__.'/_htaccess.php')) {
        copy( __DIR__.'/_htaccess.php',
              $dir.'.htaccess' );
    }
     
    return $dir;
}

/**
*   array_map callback
*/
function file_set($e)
{
    if (isset($e['file'])) {
        return $e;
    }
}

/**
*   get the type of method or property.  is there a better way to do this?
*   @param ReflectionMethod | ReflectionProperty
*   @return string
*/
function get_type($r)
{
    if ($r->isPublic()) {
        $type = 'public';
    } elseif ($r->isPrivate()) {
        $type = 'private';
    } elseif ($r->isProtected()) {
        $type = 'protected';
    }
    
    if ($r instanceof \ReflectionProperty) {
        return $type;
    }
    
    // ReflectionMethod only below
    
    if ($r->isStatic()) {
        $type =  "static $type";
    }
    
    if ($r->isAbstract()) {
        $type =  "abstract $type";
    }
    
    if ($r->isFinal()) {
        $type =  "final $type";
    }
            
    return $type;
}

/**
*   catch all php errors to log file rather than screen
*   usually only enabled on production
*   @param
*   @param
*   @param
*   @param
*   @return bool
*/
function handle_error_log($err_no, $err_str, $err_file, $err_line)
{
    dlog( $err_str,                                       // php error
          "PHP ERROR ($err_no) $err_file $err_line", // file name, line
          'php_errors' );
    return true;
}

/**
*   catch all php errors to screen rather than log file
*   usually only enabled on development
*   @param
*   @param
*   @param
*   @param
*   @return bool
*/
function handle_error_screen($err_no, $err_str, $err_file, $err_line)
{
    dbug( $err_str,                                 // php error
          "PHP ERROR ($err_no) ", 2, 1 );
    return true;
}

/**
*   register fancy styles for screen
*   attached to `init` action
*/
function register_styles()
{
    wp_register_style( 'dbugStyle', plugins_url('public/dbug.css', __FILE__) );
    wp_enqueue_style( 'dbugStyle' );
}

/**
*   render a page into wherever
*   @param string
*   @param object|array
*/
function render($_template, $vars = array())
{
    if (file_exists(__DIR__.'/views/'.$_template.'.php')) {
        $_template_file = __DIR__.'/views/'.$_template.'.php';
    } else {
        return "<div>template missing: $_template</div>";
    }
        
    extract( (array) $vars, EXTR_SKIP );
    
    ob_start();
    require $_template_file;
    $html = ob_get_contents();
    ob_end_clean();
    
    return $html;
}

/**
*
*   @return string
*/
function version()
{
    $data = get_plugin_data( __DIR__.'/_plugin.php' );
    return $data['Version'];
}
