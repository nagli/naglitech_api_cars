<?php
    /**
    * This is the entry point of the REST API
    * 
    * GET /v1/cars/
    * GET /v1/cars/?make={}&model={}
    * GET /v1/cars/{id}
    * DELETE /v1/cars/{id}
    * POST /v1/cars/  Body{"make":"", "model":"", "platform":""}
    * POST /v1/cars/  Body[{"make":"", "model":"", "platform":""}, {}, {}]
    * PATCH /v1/cars/{id}  Body{"make":"", "model":"", "platform":""}
    * PUT /v1/cars/{id}  Body{"make":"", "model":"", "platform":""}
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
