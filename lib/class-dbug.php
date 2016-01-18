<?php 

namespace wp_dbug;

class Dbug{
	private static $error_handler = 'screen';	// or 'log'
 	private static $html = '';					// html echoed for `screen` logging
 	
 	private static $LOG_PATH = '';				// absolute path to logs on server
 	private static $LOG_FILESIZE = 1048576;		// in bytes 1048576 = 1 megabyte
 	
 	/*
 	*	sets up log path, error handling, admin screens
 	*	@return NULL
 	*/
 	public static function setup(){
		// set path to logs
		self::$LOG_PATH = get_log_path();
		
		// set default error handling to screen to logs
 		self::set_error_handler();
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
		$bt = array_map( __NAMESPACE__.'\file_set', $bt );
		$bt = array_filter( $bt );
		
		if( $bt > 0 )
			$bt = array_slice( $bt, 2, $levels );
		
		return $bt;
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
		self::$LOG_FILESIZE = get_log_filesize(); 
		
		// get the saved error level and calculate val
		$error_level = 0;
		$error_levels = get_option( 'dbug_error_level' );
		
		if( is_array($error_levels) )
			foreach( $error_levels as $e_level ){
				//echo "$error_level | $e_level<br/>\n";
				$error_level = $error_level | $e_level;
			}	
		
		// whether to output errors or log to file
		$logging = get_option( 'dbug_logging' );
		switch($logging){
			case 'log':
				set_error_handler( __NAMESPACE__.'\handle_error_log', $error_level );
				self::$error_handler = 'log';
				return;
				break;
			case 'screen':
			default:
				add_action( 'init', __NAMESPACE__.'\register_styles' );
				set_error_handler( __NAMESPACE__.'\handle_error_screen', $error_level );
				self::$error_handler = 'screen';
				return;
				break;
		}
	}
}