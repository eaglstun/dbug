<?php

namespace WP_Dbug;

/**
*	create the log directory if it does not exist
*	default to /logs/ in wordpress uploads dir
*	@todo find a better way to make sure the path is writeable and valid
*	@todo fix error when log path is not on same server.
*	@param string
*	@return string absolute path to directory or FALSE
*/
function check_log_dir( $dir ){
	if( !is_dir($dir) ){
		$wp_upload_dir = wp_upload_dir();
		$dir = $wp_upload_dir['basedir'].'/logs/';
	}
	
	$pathinfo = pathinfo( $dir );
	$dirname = isset( $pathinfo['dirname'] ) ? $pathinfo['dirname'] : NULL;
	if( !is_dir($dirname) )	
		return FALSE;
	
	// force trailing slash!
	if( strrpos($dir, '/') != (strlen($dir)-1) )
		$dir .= '/';
	
	// make directory if it doesnt exist
	if( !is_dir($dir) )
		wp_mkdir_p( $dir, 0755 );
	
	// change permissions if we cant write to it
	if( !is_writable($dir) )	
		@chmod( $dir, 0755 );
	
	// test and make sure we can write to it
	if( !is_dir($dir) || !is_writable($dir) )
		return FALSE;
	
	// make sure htaccess is in place to protect log files
	if( !file_exists($dir.'.htaccess') && file_exists(__DIR__.'/_htaccess.php') ) 
		copy( __DIR__.'/_htaccess.php',
			  $dir.'.htaccess' );
	 
	return $dir;
}

/**
*	array_map callback
*/
function file_set( $e ){
	if( isset($e['file']) )
		return $e;
}

/**
*	get the type of method or property.  is there a better way to do this?
*	@param ReflectionMethod | ReflectionProperty
*	@return string
*/
function get_type( $r ){
	if( $r->isPublic() )
		$type = 'public';
	elseif( $r->isPrivate() )
		$type = 'private';
	elseif( $r->isProtected() )
		$type = 'protected';
	
	if( $r instanceof \ReflectionProperty )
		return $type;
	
	// ReflectionMethod only below
	
	if( $r->isStatic() )
		$type =  "static $type";
	
	if( $r->isAbstract() )
		$type =  "abstract $type";
	
	if( $r->isFinal() )
		$type =  "final $type";
			
	return $type;
}

/**
*	gets the max filesize of logs in bytes
*	@return int
*/
function get_log_filesize(){
	$dbug_log_filesize = (int) get_option( 'dbug_log_filesize' );
	$dbug_log_filesize = $dbug_log_filesize < 1024 ? 1048576 : $dbug_log_filesize;
	
	return $dbug_log_filesize;
}

/**
*	gets the saved path to log files and creates if doesnt exist
*	@return string absolute path to directory or FALSE
*/
function get_log_path(){
	$path = get_option( 'dbug_log_path' );
	
	return check_log_dir( $path );
}

/**
*	catch all php errors to log file rather than screen
*	usually only enabled on production
*	@param
*	@param
*	@param
*	@param
*	@return bool
*/
function handle_error_log( $err_no, $err_str, $err_file, $err_line ){
	dlog( $err_str, 						 			  // php error
		  "PHP ERROR ($err_no) $err_file $err_line", // file name, line
		  'php_errors' );
	return TRUE;
}

/**
*	catch all php errors to screen rather than log file
*	usually only enabled on development
*	@param
*	@param
*	@param
*	@param
*	@return bool
*/
function handle_error_screen( $err_no, $err_str, $err_file, $err_line ){
	dbug( $err_str, 								// php error
		  "PHP ERROR ($err_no) ", 2, 1 );
	return TRUE;
}

/**
*	register fancy styles for screen
*	attached to `init` action
*/
function register_styles(){
	wp_register_style( 'dbugStyle', plugins_url('public/dbug.css', __FILE__) );
	wp_enqueue_style( 'dbugStyle' );
}

/**
*	render a page into wherever
*	@param string
*	@param object|array
*/
function render( $_template, $vars = array() ){
	if( file_exists(__DIR__.'/views/'.$_template.'.php') )
		$_template_file = __DIR__.'/views/'.$_template.'.php';
	else
		return "<div>template missing: $_template</div>";
		
	extract( (array) $vars, EXTR_SKIP );
	
	ob_start();
	require $_template_file;
	$html = ob_get_contents();
	ob_end_clean();
	
	return $html;
}

/**
*	whether to output errors or log to file
*	@return string 'log' or 'screen'
*/
function set_error_handler(){
	$error_level = (array) get_option( 'dbug_error_level' ); 
	$error_level = array_reduce( $error_level, function($a, $b){
		return $a | intval( $b ); 
	}, 0 );

	$logging = get_option( 'dbug_logging' );

	switch( $logging ){
		case 'log':
			\set_error_handler( __NAMESPACE__.'\handle_error_log', $error_level );
			return 'log';
			break;

		case 'screen':
		default:
			add_action( 'init', __NAMESPACE__.'\register_styles' );
			\set_error_handler( __NAMESPACE__.'\handle_error_screen', $error_level );
			return 'screen';
			break;
	}
}