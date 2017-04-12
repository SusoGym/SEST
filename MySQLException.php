<?php

class MySQLException extends Exception {
    
    private $mysqli;
    private $query;
    
    /**
     * MySQLException constructor.
     *
     * @param $mysqli mysqli
     * @param $query string
     */
    public function __construct($mysqli, $query) {
        $message = "Error with query \"$query\": " . $mysqli->error;
        $this->query = $query;
        $this->mysqli = $mysqli;
        parent::__construct($message);
    }
    
    public function getJson()
    {
        $data = array("code" => 500, "message" => "Exception", "payload" => $this->getData());
    
        return json_encode($data, JSON_PRETTY_PRINT);
    
    }
    
    
    public function getData()
    {
        return array("type" => "mysql", "query" => $this->query, "message" => $this->mysqli->error);
    }
    
}
