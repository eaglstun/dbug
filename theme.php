<?php

/**
*   output debug information to screen
*   @param mixed
*   @param string optional
*   @param int optional
*   @param int optional
*/
function dbug($v = null, $k = null, $trace = 1)
{
    // dont use DEBUG for integer 0
    if (is_null($k)) {
        $k = 'DEBUG';
    }
    
    WP_Dbug\Dbug::instance()->debug( $v, $k, $trace );
    return;
}

/**
*   write debug information to log
*   @param mixed
*   @param string optional
*   @param string optional
*/
function dlog($v = null, $k = null, $file = 'dlog')
{
    // dont use DEBUG for integer 0
    if (is_null($k)) {
        $k = 'DEBUG';
    }
    
    WP_Dbug\Dbug::instance()->delog( $v, $k, $file );
    return;
}

/**
*   dbug and die
*   @param mixed
*   @param string optional
*   @param int optional number of lines to backtrace
*   ends script
*/
function ddbug($v = null, $k = null, $trace = 1)
{
    // dont call dbug() from here because it screws up backtrace
    // dont use DEBUG for integer 0
    if (is_null($k)) {
        $k = 'DEBUG';
    }
    
    WP_Dbug\Dbug::instance()->debug( $v, $k, $trace );
    die();
}

/**
*   dlog and die
*   @param mixed
*   @param string optional
*   @param int optional number of lines to backtrace
*   ends script
*/
function ddlog($v = null, $k = null, $file = 'dlog')
{
    // dont call dbug() from here because it screws up backtrace
    // dont use DEBUG for integer 0
    if (is_null($k)) {
        $k = 'DEBUG';
    }
    
    WP_Dbug\Dbug::instance()->delog( $v, $k, $file );
    die();
}
