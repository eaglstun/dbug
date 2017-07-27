<?php

namespace WP_Dbug;

class Dbug
{

    protected static $instance = null;

    protected $html = '';                  // html echoed for `screen` logging
    
    protected $settings = [
        /*
        'error_handler' => ''               // 'screen' or 'log'
        'log_filesize' => 1048576           // in bytes 1048576 = 1 megabyte 
        'log_path' => ''                    // absolute path to logs on server
        */
    ];
    
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
    *   sets up log path, error handling, admin screens
    *   @return
    */
    protected function __construct()
    {
        //
        $this->error_handler = set_error_handler();

        // set default error handling to screen to logs
        $this->set_error_level();

        $this->settings = get_option('dbug_settings');
    }
    
    /**
    *   output debug info to screen
    *   @param mixed
    *   @param string|int
    *   @param int
    *   @param int
    *   @return NULL
    */
    public function debug($v, $k, $t = 1)
    {
        // dont show
        if ($this->error_handler == 'log') {
            return self::delog( $v, $k, 'dbug' );
        }
        
        self::debug_value_html( $k, $v, 0 );
        
        $backtrace = $t ? self::get_backtrace( $t ) : [];
        
        echo render( 'dbug', [
            'error' => $this->html,
            'backtrace' => $backtrace
        ] );

        $this->html = '';
    }
    
    /**
    *
    *   @todo if we can not write to the log directory, handle the failure in a way that lets the site admin know
    *   @param
    *   @param
    *   @param string
    *   @return
    */
    public static function delog($v, $k = 'DEBUG', $file)
    {
        $now = time();
        
        self::debug_value_html( $k, $v, 0 );
        
        $log = $_SERVER['REQUEST_URI']."\n";
        $log .= date( 'M jS Y h:i:s a', $now )." ( $now ) \n";
        $log .= strip_tags( str_replace('&nbsp;', ' ', $this->html)). "\n\n";
        
        $log = html_entity_decode( $log );
        $log = utf8_decode( $log );
        
        if (!file_exists($this->settings['log_path'].$file)) {
            touch( $this->settings['log_path'].$file );
        }
            
        file_put_contents( $this->settings['log_path'].$file, $log, FILE_APPEND );
        $this->html = '';
        
        $m = filesize( $this->settings['log_path'].$file );
        $path = $this->settings['log_path'];
        
        if ($m >= $this->settings['log_filesize']) {
            $i = 1;
            while (file_exists($path.$file."_".$i)) {
                $i++;
            }
            
            copy( $path.$file, $path.$file."_".$i );
            unlink( $path.$file );
        }
    }
    
    /**
    *   removes the `dbug` elements from backtrace
    *   @param int
    *   @return array
    */
    public static function get_backtrace($levels = 1)
    {
        $bt = debug_backtrace();
        $bt = array_map( __NAMESPACE__.'\file_set', $bt );
        $bt = array_filter( $bt );
        
        if ($bt > 0) {
            $bt = array_slice( $bt, 2, $levels );
        }
        
        return $bt;
    }

    /**
    *
    *   @param string
    *   @return mixed
    */
    public function get_setting($which)
    {
        if (array_key_exists($which, $this->settings)) {
            return $this->settings[$which];
        }
    }
    
    /**
    *
    *   @param
    *   @param
    *   @param
    *   @param
    *   @return
    */
    public function debug_value_html($k, $v, $indent, $hack = false)
    {
        if ($indent > 100) {
            return;
        }
        
        // dont display arrays/objects as key
        if (is_int($k) || is_float($k)) {
            $k = strval( $k );
        } elseif (!is_string($k)) {
            $k = '?';
        }
        
        $k = urlencode( (string) $k );
        $k = str_replace( '%00%2A%00_', '', $k );
        $k = urldecode( $k );
    
        self::debug_indent_html( $indent );
        
        if (is_null($v)) {
            $this->html .= ( htmlentities($k) . " = <strong>Null</strong><br/>\n" );
        } elseif (is_bool($v)) {
            $this->html .= ( htmlentities($k) . " = <strong>Bool:</strong> [ " . ( $v == true ? 'TRUE' : 'FALSE') . " ]<br/>\n" );
        } elseif (is_int($v)) {
            $this->html .= ( htmlentities($k) . " = <strong>Int:</strong> [ $v ]<br/>\n" );
        } elseif (is_float($v)) {
            $this->html .= ( htmlentities($k) . " = <strong>Float:</strong> [ $v ]<br/>\n" );
        } elseif (is_string($v)) {
            $this->html .= $hack ?
                           htmlentities($k) ." = [ ". htmlentities($v) ." ]<br/>\n" :
                           htmlentities($k) ." = <strong>String:</strong> [ ". htmlentities($v) ." ]<br/>\n";
        } elseif (is_array($v)) {
            $this->html .= $hack ?
                           htmlentities($k) ."<br/>\n" :
                           htmlentities($k) ." = <strong>Array</strong> containing ". count($v) ." elements:<br/>\n";
                           
            foreach ($v as $k1 => $v1) {
                $hack ?
                self::debug_value_html( $k1, $v1, ( $indent + 5), true ) :
                self::debug_value_html( $k1, $v1, ( $indent + 5) );
            }
        } elseif (($v_class = get_class($v)) && ($v_class != 'stdClass')) {
            // TODO: figure out a way to make this work.
            // there is a problem with get_class on certain objects...
            $this->html .= ( $k . " = <strong>Class</strong> $v_class:<br/>\n" );
            
            $RC = new \ReflectionClass( $v );
            
            $properties = $RC->getProperties();
            $this->html .= count($properties) ." properties:<br/>\n";
            foreach ($properties as $k1 => $v1) {
                $type = get_type($v1);
                
                $property_mockup = array();
                
                if ($v_class != $v1->class) {
                    $property_mockup['Class:'] = $v1->class;
                }
                
                // TODO: find better way to not use small tags
                self::debug_value_html( "$".$v1->name." <small>( $type )</small>", $property_mockup, ($indent + 5), true );
            }
            
            $methods = $RC->getMethods();
            $this->html .= count($methods) ." methods:<br/>\n";
            foreach ($methods as $k1 => $v1) {
                $type = get_type($v1);
                
                $params = $v1->getParameters();
                $params = implode( ', ', $params );
                
                $method_mockup = array(
                    'Parameters' => $params
                );
                
                if ($v_class != $v1->class) {
                    $method_mockup['Class:'] = $v1->class;
                }
                
                self::debug_value_html( $v1->name." <small>( $type )</small> ", $method_mockup, ($indent + 5), true );
            }
        } elseif (is_object($v)) {
            $vars = (array) $v;
            $count = count( $vars );
            
            $this->html .= ( $k . " = <strong>Object</strong> with $count elements:<br/>\n" );
            
            foreach ($vars as $k1 => $v1) {
                self::debug_value_html( $k1, $v1, ($indent + 5) );
            }
        }
    }
    
    /**
    *   add any number of non breaking spaces (&npsp;) to html
    *   @param int
    *   @return
    */
    private static function debug_indent_html($indent)
    {
        if ($indent > 0) {
            for ($x=0; $x<$indent; $x++) {
                $this->html .= '&nbsp;';
            }
        }
    }
    
    /**
    *
    *   @return
    */
    private static function set_error_level()
    {
        // get the saved error level and calculate val
        $error_level = 0;
        $error_levels = get_option( 'dbug_error_level' );
        
        if (is_array($error_levels)) {
            foreach ($error_levels as $e_level) {
                $error_level = $error_level | $e_level;
            }
        }

        // @todo what happends to $error_level ??
    }
}
