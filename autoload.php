<?php

namespace WP_Dbug;

// this file should only be included if composer autoload is not being used

require_once __DIR__.'/functions.php';
require_once __DIR__.'/theme.php';

/**
*   PSR-4
*   @todo detect if composer autoload is being used
*   @param string
*/
function autoload($class)
{
    if (strpos($class, __NAMESPACE__) !== 0) {
        return;
    }
  
    $file = __DIR__ .'/lib/'. str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
}
spl_autoload_register( __NAMESPACE__.'\autoload' );
