<?php
    /**
    * This is the entry point of the REST API
    * 
    */
    
    require_once("CarAPI.php");
    // Requests from the same server don't have a HTTP_ORIGIN header
    if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
        $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
    }

    try {
        $API = new CarAPI($_SERVER['HTTP_ORIGIN']);
    } catch (Exception $e) {
        echo json_encode(Array('error' => $e->getMessage()));
    }  
?>
