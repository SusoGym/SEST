<?php

namespace base;

class SuperModel {
    
    /** @var $instance SuperModel */
    private static $instance;
    
    /** Returns instance of model
     *
     * @return SuperModel
     */
    public static function getInstance() {
        return self::$instance;
    }
    
    /** @var  \Connection */
    protected $connection;
    
    /**
     * @return \Connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * SuperModel constructor.
     *
     * @param $configPath string path to config file
     */
    public function __construct($configPath) {
        \Connection::$configFile = $configPath;
        $this->connection = new \Connection();
        self::$instance = $this;
    }
    
}