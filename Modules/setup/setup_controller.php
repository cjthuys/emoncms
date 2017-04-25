<?php

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

function setup_controller()
{
    global $path,$session,$route,$mysqli,$fullwidth;
    
    $result = false;
    
    if ($route->action=="hello") {
        $result = view("Modules/setup/hello.php",array());
    }
    
    $fullwidth = false;
    return array('content'=>$result, 'fullwidth'=>true);
}

