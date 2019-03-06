<?php
require_once("API.php");
/**
* Here going the api endpoints
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
                    /*Get all cars*/
                    if (($carList=$this->listAllCars())===false) {
                        return $this->returnJSON(array(), "cars", array()
                            , $this->httpStatusCode(500, $this->errorMsg));
                    } else {
                        return $this->returnJSON($carList, "cars", array()
                        , $this->httpStatusCode(200));
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
}/* end of class */  
?>
