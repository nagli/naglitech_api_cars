<?php
class API {
    /**
    * GET get either the full list of cars or the car with the requested id.
    * PATCH update the requested car id
    * PUT replace the car with the requested id or create it if not exist 
    * POST create a new car or cars in batch mode
    * DELETE the car with the requested id
    * 
    * 
    */
    protected $args = null;
    protected $endpoint = '';
    protected $verb = '';
    protected $id = null;
    protected $addArgs = null;
    protected $method = '';
    protected $arrayInput = null;
    protected $request = null;
    protected $errorMsg = "";
    
    public function __construct() 
    {
        //printf("<br>Request:<pre>%s</pre>", var_export($request, true));
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        //header("Content-Type: application/json");
        
        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' AND array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PATCH') {
                $this->method = 'PATCH';
            } else {
                throw new Exception("Unexpected Header");
            }
        }
        
        switch($this->method) {
            case "GET":
            case "DELETE":
                $this->request = $this->cleanInputs($_GET);
                break;    
            case "POST":
            case "PUT":
            case "PATCH":
                $this->request = $this->cleanInputs($_GET);
                $this->arrayInput = json_decode(file_get_contents('php://input'), true);
                break;
            default:
                throw new Exception("Invalid Header");
        }
        
        if (isset($this->request['request'])) {
            $this->args = explode('/', rtrim($this->request['request'], '/'));

            $this->endpoint = array_shift($this->args);
            if (array_key_exists(0, $this->args) AND !is_numeric($this->args[0])) {
                $this->verb = $this->args[0];
            }
            if (array_key_exists(0, $this->args) AND is_numeric($this->args[0])) {
                $this->id = $this->args[0];
            }
        }
        if (count($this->request) > 1) {
            foreach ($this->request AS $key => $val) {
                if ($key !== "request") {
                    $cleanKey = strip_tags($key);
                    $cleanVal = strip_tags($val);
                    $this->addArgs[$cleanKey] = $cleanVal;
                }
            }
        }
         /* Print out for testing */
        /*
        printf("<br>Method: %s", var_export($this->method, true));
        printf("<br>Endpoint: %s", var_export($this->endpoint, true));
        printf("<br>Verb: %s", var_export($this->verb, true));
        printf("<br>Id: %s", var_export($this->id, true));
        printf("<br>Args:<pre>%s</pre>", var_export($this->args, true));
        printf("<br>Add Args:<pre>%s</pre>", var_export($this->addArgs, true));
        printf("<br>Input:<pre>%s</pre>", var_export($this->arrayInput, true));
        */
        if ($this->endpoint === "cars") { 
            echo $this->cars(); 
        } else { 
            throw new Exception("Invalid Endpoint"); 
        }
    }
    protected function calcOffset(array $offset)
    {
        if (!empty($this->addArgs) AND isset($this->addArgs['offset']) 
            AND isset($this->addArgs['limit'])) {
            if (!is_null($this->addArgs['offset']) 
                AND $this->addArgs['offset'] != "" 
                AND is_numeric($this->addArgs['offset'])) {
                $offset['offset'] = intVal($this->addArgs['offset']);
            }
            if (!is_null($this->addArgs['limit']) 
                AND $this->addArgs['limit'] != "" 
                AND is_numeric($this->addArgs['limit'])) {
                $offset['limit'] = intVal($this->addArgs['limit']);
            }
            if (($offset['prevOffset'] = 
                ($offset['offset'] - $offset['limit']))<0) { 
                    $offset['prevOffset'] = 0; 
            };
            $offset['nextOffset'] = ($offset['offset'] + $offset['limit']);
        }

        return $offset;
    }
    protected function connectToDB()
    {
        try {
            $db = new PDO('mysql:host=localhost;dbname=naglitech;charset=utf8mb4', 'nagli', 'arn2302');
            //$db = new PDO('mysql:host=localhost;dbname=brandyna_naglitech;charset=utf8mb4', 'brandyna_naglite', 'arn?23!02');
        } catch(PDOException $ex) {
            $this->errorMsg = $ex->getMessage();
            return false;         
        }
        return $db;
    }
    protected function httpStatusCode($status, $addInfo=null) 
    {
        /**
        * {
        *   "status":400,
        *   "code": "Invalid_address",
        *   "message": "The origin address is invalid",
        *   "additionalInformation":""
        * }
        */
        $arrCodes = array(
            '200' => "OK",
            '201' => "Created",
            '204' => "OK",
            '301' => "Moved_Permanently",
            '304' => "Not_Modified",
            '400' => "Bad_Request",
            '401' => "Unauthorized",
            '403' => "Forbidden",
            '404' => "Not_Found",
            '500' => "Internal_Server_Error"
        );
        $arrCodeMessages = array(
            '200' => "Success",
            '201' => "Success. The request has been fulfilled and resulted in a new resource being created.",
            '204' => "Success. The server has fulfilled the request but does not need to return a response body.",
            '301' => "Moved_Permanently. The request does not exist anymore on this API and is most likly moved to another location.",
            '304' => "Not_Modified. Nothing was modified, if the purpose of the request was to modify records, no record was modified to any reason.",
            '400' => "Bad request. The request could not be understood by the server due to malformed syntax.",
            '401' => "Unauthorized. The request requires authentication with a valid client authorization token.",
            '403' => "Forbidden. The request has no rights to this API",
            '404' => "Not found. The server has not found anything matching the Request-URI.",
            '500' => "Internal server error. The server encountered an unexpected condition which prevented it from fulfilling the request."
        );
        $code = "N/A";
        $message = "N/A";
        if (empty($addInfo)) { $addInfo = "N/A"; }
        if (empty($status) OR !key_exists($status, $arrCodes)) { 
            $status = 500;
            $addInfo = "No status generated or status is not valid. Error!";
        }

        $code = $arrCodes[$status];
        if (isset($arrCodeMessages[$status])) { 
            $message = $arrCodeMessages[$status];
        }

        return array(
            "status" => $status, 
            "code" => $code, 
            "message" => $message, 
            "additionalInformation" => $addInfo);
    }
    protected function returnJSON(array $content, $contentKey="content"
        , array $meta, array $status)
    {
        $arr = array();
        $arr['status'] = $status;
        $arr['meta'] = $meta;
        $arr[$contentKey] = $content;
        $json = json_encode($arr);
        return $json;
    }
    private function cleanInputs($data) 
    {
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $clean_input[$key] = $this->cleanInputs($value);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }
}
?>
