<?php

/**
 * View Class
 */
class View {
    
    
    /**
     * @var View
     */
    private static $instance;
    
    /**
     * @var bool
     */
    public static $DEBUG = false;
    
    /**
     * @return View
     */
    public static function getInstance() {
        return self::$instance == null ? (self::$instance = new View()) : self::$instance;
    }
    
    
    /**
     * @Constant string $PATH path to template files
     */
    public static $PATH = 'templates';
    
    
    /**
     * @var array() various data to be shown in view
     */
    private $dataForView = null;
    
    
    /**
     * @var bool whether or not the loadTemplate was called
     */
    private $loaded = false;
    
    /**
     *Template Dateien werden geladen
     *
     * @param $template string
     * @return void
     */
    public function loadTemplate($template) {
        
        if ($this->loaded)
            return;
        $this->loaded = true;
        
        $templateFile = self::$PATH . DIRECTORY_SEPARATOR . $template . '.php';
        $exists = file_exists($templateFile);
        
        if (self::$DEBUG)
            ChromePhp::info("Displaying 'templates/$template.php'");
        
        if ($exists) {
            /** @noinspection PhpIncludeInspection */
            include($templateFile);
        } else {
            die('Could not find template. Please tell your system admin of choice.');
        }
    }
    
    /**
     * @param $str string Adds header
     */
    public function header($str) {
        if (!$this->loaded)
            echo $str;
        
    }
    
    /**
     *set dataForView
     *
     * @param array
     */
    public function setDataForView($data) {
        $this->dataForView = $data;
    }
    
    /**
     * @return array
     */
    public function getDataForView() {
        return $this->dataForView;
    }
    
    /**
     * @return string
     */
    public function getTitle() {
        return isset($this->dataForView['title']) ? $this->dataForView['title'] : "";
    }
}

?>
