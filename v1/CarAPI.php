<?php
require_once("API.php");
/**
* API endpoints
*/
class CarAPI extends API {
    public function __construct($origin) {
        parent::__construct();
    }
    protected function cars()
    {
        switch (strtoupper($this->method)) {
            case "GET":
                if (is_null($this->id)) {
                    
                    if (is_null($this->addArgs)) {
                        /*Get all cars*/
                        if (($carList=$this->listAllCars())===false) {
                            return $this->returnJSON(array(), "cars", array()
                                , $this->httpStatusCode(500, $this->errorMsg));
                        } else {
                            return $this->returnJSON($carList, "cars", array()
                            , $this->httpStatusCode(200));
                        }
                    } else {
                        /*Get cars with args*/
                        if (($carList=$this->listCarsWArgs($this->addArgs))===false) {
                            return $this->returnJSON(array(), "cars", array()
                                , $this->httpStatusCode(500, $this->errorMsg));
                        } else {
                            return $this->returnJSON($carList, "cars", array()
                            , $this->httpStatusCode(200));
                        }
                    }
                } else {
                    if (($carList=$this->getOneCar($this->id))===false) {
                        return $this->returnJSON(array(), "cars", array()
                            , $this->httpStatusCode(500, $this->errorMsg));
                    } else {
                        return $this->returnJSON($carList, "cars", array()
                        , $this->httpStatusCode(200));
                    }    
                }
                break;
            case "DELETE":
                if (is_null($this->id)) {
                    return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(400, $this->errorMsg));    
                } else {
                    if ($this->deleteOneCar($this->id)) {
                        return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(204));
                    } else {
                        return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(304, $this->errorMsg));
                    }
                }
                break;
            case "POST":
                if (!is_null($this->arrayInput) AND is_array($this->arrayInput)) {
                    if (empty($this->verb)) {
                        /* save one car */
                        if ($this->saveOneCar($this->arrayInput)) {
                            return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(204));
                        } else {
                            return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(304, $this->errorMsg));
                        }
                    } elseif ($this->verb === "batch") {
                        /* batch save car */
                        if ($this->saveBatchCar($this->arrayInput)) {
                            return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(204));
                        } else {
                            return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(304, $this->errorMsg));
                        }
                    
                    } else {
                        return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(400, "No such endpoint verb"));
                    }
                } else {
                    return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(400, "No Data"));    
                }
                break;
            case "PATCH":
                /* update a car using an id */
                if (is_null($this->id) OR is_null($this->arrayInput)) {
                    return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(400, $this->errorMsg));    
                } else {
                    if ($this->updateOneCar($this->id, $this->arrayInput)) {
                        return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(204));
                    } else {
                        return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(304, $this->errorMsg));
                    }
                }
                break;
            case "PUT":
                /* Overwrite a car or create if not exist */
                if (is_null($this->id) OR is_null($this->arrayInput)) {
                    return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(400, $this->errorMsg));    
                } else {
                    if ($this->overwriteOneCar($this->id, $this->arrayInput)) {
                        return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(204));
                    } else {
                        return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(304, $this->errorMsg));
                    }
                }
                break;    
            default:
                return $this->returnJSON(array(), "cars", array()
                        , $this->httpStatusCode(404, "Unknown Method"));     
        }          
    }
    private function listAllCars()
    {
        if(($db = $this->connectToDB()) === false) { return false; }
        $strSQL = "SELECT id, Make, Model, Platform 
            FROM cars_test
            ORDER BY Make ASC";
        try{
            $stmt = $db->prepare($strSQL);
            $stmt->execute();
            return $stmt->fetchall(PDO::FETCH_ASSOC);
        } catch(PDOException $ex) {
            printf("<br>Error %s: %s", __METHOD__, $ex->getMessage());
            $this->errorMsg = $ex->getMessage();
            return false;    
        }
    }
    private function listCarsWArgs(array $args)
    {   
        if (($cleanArgs = $this->checkSuppliedData($args, array("make", "model")))===false) { return false; }
        
        if(($db = $this->connectToDB()) === false) { return false; }
        $strSQL = "SELECT id, Make, Model, Platform 
            FROM cars_test 
            WHERE";
            
        foreach ($cleanArgs as $key => $val) {
            $strSQL.= " ".ucfirst($key)." = :".$key." AND"; 
        }
        $strSQL = rtrim($strSQL, 'AND');

        try{
            $stmt = $db->prepare($strSQL);
            
            foreach ($cleanArgs as $key => $val) {
                $stmt->bindValue(":".$key, $val, PDO::PARAM_STR);    
            }
            
            $stmt->execute();
            return $stmt->fetchall(PDO::FETCH_ASSOC);
        } catch(PDOException $ex) {
            printf("<br>Error %s: %s", __METHOD__, $ex->getMessage());
            $this->errorMsg = $ex->getMessage();
            return false;    
        }
    }
    private function getOneCar($id)
    {
        if(($db = $this->connectToDB()) === false) { return false; }
        $strSQL = "SELECT id, Make, Model, Platform 
            FROM cars_test
            WHERE id = ?";
        try{
            $stmt = $db->prepare($strSQL);
            $stmt->bindValue(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchall(PDO::FETCH_ASSOC);
        } catch(PDOException $ex) {
            printf("<br>Error %s: %s", __METHOD__, $ex->getMessage());
            $this->errorMsg = $ex->getMessage();
            return false;    
        }
    }
    private function deleteOneCar($id)
    {
        if(($db = $this->connectToDB()) === false) { return false; }
        $strSQL = "DELETE  
            FROM cars_test
            WHERE id = ?";
        try{
            $stmt = $db->prepare($strSQL);
            $stmt->bindValue(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                return true;
            } else {
                return false;
            }
        } catch(PDOException $ex) {
            printf("<br>Error %s: %s", __METHOD__, $ex->getMessage());
            $this->errorMsg = $ex->getMessage();
            return false;    
        }    
    }
    private function saveOneCar($data)
    {
        if ($this->checkSuppliedKeys($data, array("make", "model", "platform"))===false) { return false; }
        
        if (($cleanData = $this->checkSuppliedData($data, array("make", "model", "platform"))) === false) {
            return false;
        }
        if(($db = $this->connectToDB()) === false) { return false; }
        $strSQL = "INSERT INTO cars_test 
        (Make, Model, Platform)
        VALUE(?,?,?)";
        try {
            $stmt = $db->prepare($strSQL);
            $stmt->bindValue(1, $cleanData['make'], PDO::PARAM_STR);
            $stmt->bindValue(2, $cleanData['model'], PDO::PARAM_STR);
            $stmt->bindValue(3, $cleanData['platform'], PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                return true;
            } else {
                return false;
            }
        } catch(PDOException $ex) {
            printf("<br>Error %s: %s", __METHOD__, $ex->getMessage());
            $this->errorMsg = $ex->getMessage();
            return false;     
        }
    }
    private function saveBatchCar($data)
    {
        foreach ($data AS $value) {
            if (is_array($value)) {
                if ($this->saveOneCar($value)===false) { return false; }
            }
        }
        return true;
    }
    private function updateOneCar($id, $data)
    {
        if (($cleanData = $this->checkSuppliedData($data, array("make", "model", "platform"))) === false) {
            return false;
        }
        if(($db = $this->connectToDB()) === false) { return false; }
        $strSQL = "UPDATE cars_test 
        SET ";
        foreach ($cleanData as $key => $val) {
            $strSQL.= ucfirst($key).' = :'.$key.',';
        }
        $strSQL = rtrim($strSQL, ',');    
        $strSQL.= " WHERE id = :id";
        
        try {
            $stmt = $db->prepare($strSQL);
            foreach ($cleanData as $key => $val) {
                $stmt->bindValue(":".$key, $val, PDO::PARAM_STR);       
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                return true;
            } else {
                return false;
            }    
        } catch(PDOException $ex) {
            printf("<br>Error %s: %s", __METHOD__, $ex->getMessage());
            $this->errorMsg = $ex->getMessage();
            return false;
        }
        
        
    }
    private function overwriteOneCar($id, $data)
    {
        if (($cleanData = $this->checkSuppliedData($data, array("make", "model", "platform"))) === false) {
            return false;
        }
        if(($db = $this->connectToDB()) === false) { return false; }
        /* check if the id exist in the table */
        $strSQL = "SELECT id  
            FROM cars_test 
            WHERE id = ?";
        try {
            $stmt = $db->prepare($strSQL);
            $stmt->bindValue(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount()>0) {
                /* record exist, update the record */
                return $this->updateOneCar($id, $data);
            } else {
                /* record does not exist, create the record */
                return $this->saveOneCar($data);
            }
            
        } catch(PDOException $ex) {
            printf("<br>Error %s: %s", __METHOD__, $ex->getMessage());
            $this->errorMsg = $ex->getMessage();
            return false;    
        }
    
    
    }
    
    private function checkSuppliedData(array $data, array $fields)
    {
        $cleanData = array();
        foreach ($data as $key => $value) {
            if (!in_array(strtolower($key), $fields)) { return false; }
            $val = filter_var($value, FILTER_SANITIZE_STRING);
            $val = filter_var($val, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $cleanData[$key] = $val;    
        }
        return $cleanData;
    }
    private function checkSuppliedKeys(array $data, array $keys)
    {
        /**
        * To return true, all keys need to be in the data array
        */
        foreach ($keys AS $key) {
            if (!key_exists($key, $data)) { return false; }
        }
        return true;
    }
}/* end of class */  
?>
