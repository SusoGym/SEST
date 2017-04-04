<?php namespace blog;

class Controller extends Utility {
    
    /** @var bool defines whether this execution is back- or frontend */
    private $console = false;
    /** @var array User input */
    private $data;
    
    // Response Variables
    /** @var int The response code (based upon HTML codes) */
    private $code = 200;
    /** @var string The response status message */
    private $message = "OK";
    
    
    /**
     * Controller constructor.
     *
     * @param $data array input data
     */
    function __construct($data) {
        $this->data = $data;
        $this->console = self::getExistentAndValue($data, "console");
        
        if ($this->console) {
            header('Content-Type: text/json');
        }
    }
    
    /**
     * Starts the process of data processing
     */
    function go()
    {
    
        if (!$this->console) {
            $template = self::getOrFallBack($this->data, 'template', Utility::$DEFAULT_TEMPLATE);
            self::displayTemplate($template);
        
            return;
        }
    
        $responseData = null;
    
        // following will call methods with the same name as is requested in $data['action'] (method must not be private),
        // data will be provided if parameter count > 0
        if (isset($this->data['action']) && method_exists($this, $this->data['action'])) {
        
            $reflect = new \ReflectionMethod($this, $this->data['action']);
        
            if ($reflect->isPublic()) {
            
            
                if ($reflect->getNumberOfParameters() == 0) {
                    $responseData = $reflect->invoke($this);
                } else {
                    $responseData = $reflect->invoke($this, $this->data);
                }
            
            } else {
                $this->code = 404;
                $this->message = "Invalid or unknown action!";
            }
        } else {
            $this->code = 404;
            $this->message = "Invalid or unknown action!";
        }
    
    
        $response = array("code" => $this->code, "message" => $this->message);
    
        if ($responseData != null) {
            $response = array_merge($response, array("payload" => $responseData));
        }
    
        die(json_encode($response, JSON_PRETTY_PRINT));
    }
    
    // Processing methods [may have 1 arg to receive $data | may return $payload (preferably objects) | must be public]
    // objects can be created with ->  (object) [ key1 => value1, key2 => value2, ... ]
    
    public function test()
    {
       return "yay";
    }
}