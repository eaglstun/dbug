<?php
/*
Plugin Name: dbug
Plugin URI: https://wordpress.org/extend/plugins/dbug/
Description: Helps with Dev'n
Author: Eric Eaglstun
Version: 1.9
Author URI: http://ericeaglstun.com
*/

if( !function_exists('dbug') && !class_exists('Dbug') ){
	
	/*
	*	output debug information to screen
	*	@param mixed
	*	@param string optional
	*	@param int optional
	*	@param int optional
	*/
	function dbug( $v = null, $k = null, $trace = 1 ){
		// dont use DEBUG for integer 0
		if( is_null($k) ){
			$k = 'DEBUG';
		}
		
		Dbug::debug( $v, $k, $trace );
		return;
	}
	
	/*
	*	write debug information to log
	*	@param mixed
	*	@param string optional
	*	@param string optional
	*/
	function dlog( $v = null, $k = null, $file = 'dlog' ){
		// dont use DEBUG for integer 0
		if( is_null($k) ){
			$k = 'DEBUG';
		}
		
		Dbug::delog( $v, $k, $file );
		return;
	}
	
	/*
	*	dbug and die
	*	@param mixed
	*	@param string optional
	*	@param int optional number of lines to backtrace
	*	ends script
	*/
	function ddbug( $v = null, $k = null, $trace = 1 ){
		// dont call dbug() from here because it screws up backtrace
		// dont use DEBUG for integer 0
		if( is_null($k) ){
			$k = 'DEBUG';
		}
		
		Dbug::debug( $v, $k, $trace );
		die();
	}
	
	/*
	*	dlog and die
	*	@param mixed
	*	@param string optional
	*	@param int optional number of lines to backtrace
	*	ends script
	*/
	function ddlog( $v = null, $k = null, $file = 'dlog' ){
		// dont call dbug() from here because it screws up backtrace
		// dont use DEBUG for integer 0
		if( is_null($k) ){
			$k = 'DEBUG';
		}
		
		Dbug::delog( $v, $k, $file );
		die();
	}
	
	class Dbug{
		
		private static $error_handler = 'screen';	// or 'log'
	 	private static $html = '';					// html echoed for `screen` logging
	 	private static $is_mu = FALSE;				// is multi-user install
	 	
	 	private static $LOG_PATH = '';				// absolute path to logs on server
	 	private static $LOG_FILESIZE = 1048576;		// in bytes 1048576 = 1 megabyte
	 	
	 	/*
	 	*	sets up log path, error handling, admin screens
	 	*	@return NULL
	 	*/
	 	public static function setup(){
	 		// set whether we are on MU or not
			if( function_exists('delete_blog_option') )
				self::$is_mu = TRUE;
			
			// set path to logs
			self::$LOG_PATH = self::getLogPath();
			
			// set default error handling to screen to logs
	 		Dbug::set_error_handler();
	 		
	 		// only admin stuff below
	 		if( !is_admin() )
	 			return;
	 		
	 		require_once 'dbug-admin.php';
	 		DbugAdmin::setup();
	 	}
	 	
	 	/*
	 	*	output debug info to screen
	 	*	@param mixed
	 	*	@param string|int
	 	*	@param int
	 	*	@param int
	 	*	@return NULL
	 	*/
		public static function debug( $v, $k, $t = 1 ){
			// dont show
			if( self::$error_handler == 'log' )
				return self::delog( $v, $k, 'dbug' );
			
			self::$html = '<div class="dbug">';
			
			self::debug_value_html( $k, $v, 0 );
			
			if( $t ){
				$bt = self::get_backtrace( $t );
				
				self::$html .= '<span class="backtrace"><strong>backtrace:</strong></span><br/>';
	
				foreach( $bt as $debug ){
					self::$html .= '<span class="backtrace">
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
										$debug['file'].' line '.$debug['line'].'
									</span><br/>'."\r\n";
				}
			}
			
			self::$html .= "</div>\n\n";
			
			echo self::$html;
			self::$html = '';
		}
		
		/*
		*	
		*	TODO: if we can not write to the log directory, handle the failure in a way that lets the site admin know
		*	@param
		*	@param
		*	@param string
		*	@return
		*/
		public static function delog( $v, $k = 'DEBUG', $file ){
			$now = time();
			
			self::debug_value_html( $k, $v, 0 );
			
			$log = $_SERVER['REQUEST_URI']."\n";
			$log .= date( 'M jS Y h:i:s a', $now )." ( $now ) \n";
			$log .= strip_tags( str_replace('&nbsp;', ' ', self::$html)). "\n\n";
			
			$log = html_entity_decode( $log );
			$log = utf8_decode( $log );
			
			if( !file_exists(self::$LOG_PATH.$file) )
				touch( self::$LOG_PATH.$file );
				
			file_put_contents( self::$LOG_PATH.$file, $log, FILE_APPEND );
			self::$html = '';
			
			$m = filesize( self::$LOG_PATH.$file );
			$path = self::$LOG_PATH;
			
			if( $m >= self::$LOG_FILESIZE ){
				$i = 1;
				while( file_exists($path.$file."_".$i) ){
					$i++;
				}
				
				copy( $path.$file, $path.$file."_".$i );
				unlink( $path.$file );
			}
		}
		
		/*
		*	removes the `dbug` elements from backtrace
		*	
		*	@param int
		*	@return array
		*/
		public static function get_backtrace( $levels = 1 ){
			$bt = debug_backtrace();
			$bt = array_map( 'Dbug::_get_backtrace', $bt );
			$bt = array_filter( $bt );
			
			if( $bt > 0 )
				$bt = array_slice( $bt, 2, $levels );
			
			return $bt;
		}
		
		/*
		*	array_map callback
		*/
		public static function _get_backtrace( $e ){
			if( isset($e['file']) )
				return $e;
		}
		
		/*
		*
		*	@param 
		*	@param
		*	@param
		*	@param
		*	@bool
		*/
		public static function debug_value_html( $k, $v, $indent, $hack = FALSE ){
			if( $indent > 100 ){
				return;
			}
			
			// dont display arrays/objects as key
			if( is_int($k) )
				$k = strval( $k );
			elseif( is_float($k) )
				$k = strval( $k );
			elseif( !is_string($k) )
				$k = '?';
			
			$k = urlencode( (string) $k );
			$k = str_replace( '%00%2A%00_', '', $k );
			$k = urldecode( $k );
		
			self::debug_indent_html( $indent );
			
			if( is_null($v) ){
				self::$html .= ( htmlentities($k) . " = <strong>Null</strong><br/>\n" );
			} else if( is_bool($v) ){
				self::$html .= ( htmlentities($k) . " = <strong>Bool:</strong> [ " . ( $v == TRUE ? 'TRUE' : 'FALSE') . " ]<br/>\n" );
			} else if( is_int($v) ){
				self::$html .= ( htmlentities($k) . " = <strong>Int:</strong> [ $v ]<br/>\n" );
			} else if( is_float($v) ){
				self::$html .= ( htmlentities($k) . " = <strong>Float:</strong> [ $v ]<br/>\n" );
			} else if( is_string($v) ){
				self::$html .= $hack ? 
							   htmlentities($k) ." = [ ". htmlentities($v) ." ]<br/>\n" : 
							   htmlentities($k) ." = <strong>String:</strong> [ ". htmlentities($v) ." ]<br/>\n";
			} else if( is_array($v) ){
				self::$html .= $hack ? 
							   htmlentities($k) ."<br/>\n" :
							   htmlentities($k) ." = <strong>Array</strong> containing ". count($v) ." elements:<br/>\n";
							   
				foreach( $v as $k1 => $v1 ){
					$hack ? 
					self::debug_value_html( $k1, $v1, ( $indent + 5), TRUE ) :
					self::debug_value_html( $k1, $v1, ( $indent + 5) );
				} 
				
			} else if( ($v_class = get_class($v)) && ($v_class != 'stdClass') ){
		  		// TODO: figure out a way to make this work.
		  		// there is a problem with get_class on certain objects...
				self::$html .= ( $k . " = <strong>Class</strong> $v_class:<br/>\n" );
				
				$RC = new ReflectionClass( $v );
				
				$properties = $RC->getProperties();
				self::$html .= count($properties) ." properties:<br/>\n";
				foreach( $properties as $k1 => $v1 ){
					$type = self::getType($v1);
					
					$property_mockup = array();
					
					if( $v_class != $v1->class ){
						$property_mockup['Class:'] = $v1->class;
					}
					
					// TODO: find better way to not use small tags
					self::debug_value_html( "$".$v1->name." <small>( $type )</small>", $property_mockup, ($indent + 5), TRUE );
				}
				
				$methods = $RC->getMethods();
				self::$html .= count($methods) ." methods:<br/>\n";
				foreach( $methods as $k1 => $v1 ){
					$type = self::getType($v1);
					
					$params = $v1->getParameters();
					$params = implode( ', ', $params );
					
					$method_mockup = array(
						'Parameters' => $params
					);
					
					if( $v_class != $v1->class ){
						$method_mockup['Class:'] = $v1->class;
					}
					
					self::debug_value_html( $v1->name." <small>( $type )</small> ", $method_mockup , ($indent + 5), TRUE );
				}
				
			} else if( is_object($v) ){
				$vars = (array) $v;
				$count = count( $vars );
				
				self::$html .= ( $k . " = <strong>Object</strong> with $count elements:<br/>\n" );
				
				foreach( $vars as $k1 => $v1 ){
					self::debug_value_html( $k1, $v1, ($indent + 5) );
				}
			}
		}
		
		/*
		*	add any number of non breaking spaces (&npsp;) to html
		*	@param int
		*	@return
		*/
		private static function debug_indent_html( $indent ){
			if( $indent > 0 ){
				for( $x=0; $x<$indent; $x++ ){
					self::$html .= '&nbsp;';
				}
			}
		}
		
		/*
		*	gets the max filesize of logs in bytes
		*	@return int
		*/
		protected static function getLogFilesize(){
			$dbug_log_filesize = (int) self::get_option( 'dbug_log_filesize' );
			$dbug_log_filesize = $dbug_log_filesize < 1024 ? 1048576 : $dbug_log_filesize;
			
			return $dbug_log_filesize;
		}
		
		/*
		*	gets the saved path to log files and creates if doesnt exist
		*	@return string absolute path to directory or FALSE
		*/
		protected static function getLogPath(){
			$path = self::get_option( 'dbug_log_path' );
			
			return self::checkLogDirectory( $path );
		}
		
		/* 
		*	get the type of method or property.  is there a better way to do this?
		*	@param ReflectionMethod|ReflectionProperty
		*	@return string
		*/
		private static function getType( $r ){
			if( $r->isPublic() )
				$type = 'public';
			elseif( $r->isPrivate() )
				$type = 'private';
			elseif( $r->isProtected() )
				$type = 'protected';
			
			if( $r instanceof ReflectionProperty )
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
		
		/*
		*	catch all php errors with dbug
	   	*	write to a log file if we are on production, screen otherwise.
	   	*	@return
	   	*/
		private static function set_error_handler(){
			// set max filesize of logs
			self::$LOG_FILESIZE = self::getLogFilesize(); 
			
			// get the saved error level and calculate val
			$error_level = 0;
			$error_levels = get_option( 'dbug_error_level' );
			
			if( is_array($error_levels) )
				foreach( $error_levels as $e_level ){
					//echo "$error_level | $e_level<br/>\n";
					$error_level = $error_level | $e_level;
				}	
				
			//die('$error_level: '.$error_level);
			//error_reporting( $error_level );
			
			// whether to output errors or log to file
			$logging = self::get_option( 'dbug_logging' );
			switch($logging){
				case 'log':
					set_error_handler( 'Dbug::handle_error_log', $error_level );
					self::$error_handler = 'log';
					return;
					break;
				case 'screen':
				default:
					add_action( 'init', 'Dbug::register_styles' );
					set_error_handler( 'Dbug::handle_error_screen', $error_level );
					self::$error_handler = 'screen';
					return;
					break;
			}
		}
		
		/*
		*	catch all php errors to screen rather than log file
		*	usually only enabled on development
		*	@param
		*	@param
		*	@param
		*	@param
		*	@return bool
		*/
		public static function handle_error_screen( $err_no, $err_str, $err_file, $err_line ){
			dbug( $err_str, 								// php error
				  "PHP ERROR ($err_no) ", 2, 1 );
			return TRUE;
		}
		
		/*
		*	catch all php errors to log file rather than screen
		*	usually only enabled on production
		*	@param
		*	@param
		*	@param
		*	@param
		*	@return bool
		*/
		public static function handle_error_log( $err_no, $err_str, $err_file, $err_line ){
			dlog( $err_str, 						 			  // php error
				  "PHP ERROR ($err_no) $err_file $err_line", // file name, line
				  'php_errors' );
			return TRUE;
		}
		
		/*
		*	create the log directory if it does not exist
		*	default to /logs/ in wordpress root
		*	@TODO find a better way to make sure the path is writeable and valid
		*	@TODO fix error when log path is not on same server.
		*	@TODO set up htaccess to copy from current directory ( mu compat )
		*	@param string
		*	@return string absolute path to directory or FALSE
		*/
		protected static function checkLogDirectory( $dir ){
			if( !is_dir($dir) )
				$dir = ABSPATH.'logs/';
			
			$pathinfo = pathinfo( $dir );
			$dirname = isset( $pathinfo['dirname'] ) ? $pathinfo['dirname'] : NULL;
			if( !is_dir($dirname) )	
				return FALSE;
			
			// force trailing slash!
			if( strrpos($dir, '/') != (strlen($dir)-1) )
				$dir .= '/';
			
			// make directory if it doesnt exist
			if( !is_dir($dir) )
				@mkdir( $dir, 0755 );
			
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
		
		/*
		*	register fancy styles for screen
		*	attached to `init` action
		*/
		public static function register_styles(){
			wp_register_style( 'dbugStyle', plugins_url('dbug/dbug.css', __DIR__) );
			wp_enqueue_style( 'dbugStyle' );
		}
		
		/* 
		*	render a page into wherever
		*	@param string
		*	@param object|array
		*/
		protected static function render( $filename, $vars = array() ){
			extract( (array) $vars, EXTR_SKIP );
			
			include $filename;
		}
		
		/*
		*	wrapper for single/mu delete_option/delete_blog_option
		*	deletes options for all blogs in blog #1 for mu
		*	@param string
		*/
		protected static function delete_option( $key ){
			return self::$is_mu ? delete_blog_option( 1, $key ) : delete_option( $key );
		}
		
		/*
		*	wrapper for single/mu get_option/get_blog_option
		*	gets options for all blogs in blog #1 for mu
		*	@param string
		*/
		protected static function get_option( $key ){
			return self::$is_mu ? get_blog_option( 1, $key ) : get_option( $key );
		}
			
		/*
		*	wrapper for single/mu update_option/update_blog_option
		*	updates options for all blogs in blog #1 for mu
		*	@param string
		*	@param mixed
		*/
		protected static function update_option( $key, $val ){
			return self::$is_mu ? update_blog_option( 1, $key, $val ) : update_option( $key, $val );
		}
	}
	
	// init
	Dbug::setup();
} 

// end of file 
// dbug/dbug.php