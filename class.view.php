<?php

/**
 * View Class
 */
class View
{


    /**
     * @var View
     */
    private static $instance;

    /**
     * @return View
     */
    public static function getInstance()
    {
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
     *Template Dateien werden geladen
     * @param $template string
     * @return void
     */
    public function loadTemplate($template)
    {
        $templateFile = self::$PATH . DIRECTORY_SEPARATOR . $template . '.php';
        $exists = file_exists($templateFile);


        ChromePhp::info("Displaying 'templates/$template.php' with data: " . json_encode($this->dataForView));

        if ($exists) {
            /** @noinspection PhpIncludeInspection */
            include($templateFile);
        } else {
            die('could not find template');
        }
    }


    /**
     *set dataForView
     * @param array
     */
    public function setDataForView($data)
    {
        $this->dataForView = $data;
    }

    /**
     * @return array
     */
    public function getDataForView()
    {
        return $this->dataForView;
    }

    /**
     * @return mixed
     */
    public function getPATH()
    {
        return $this->PATH;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return isset($this->dataForView['title']) ? $this->dataForView['title'] : "";
    }
}

?>
