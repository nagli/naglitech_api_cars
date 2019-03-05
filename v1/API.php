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
    protected $id = '';
    protected $addArgs = null;
    protected $method = '';
    protected $arrayInput = null;
    protected $request = null;
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
        printf("<br>Method: %s", var_export($this->method, true));
        printf("<br>Endpoint: %s", var_export($this->endpoint, true));
        printf("<br>Verb: %s", var_export($this->verb, true));
        printf("<br>Id: %s", var_export($this->id, true));
        printf("<br>Args:<pre>%s</pre>", var_export($this->args, true));
        printf("<br>Add Args:<pre>%s</pre>", var_export($this->addArgs, true));
        printf("<br>Input:<pre>%s</pre>", var_export($this->arrayInput, true));
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
