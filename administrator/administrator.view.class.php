<?php namespace administrator;

/**
 * View Class
 */
class View
{

    /**
     * @Constant string $PATH path to template files
     */
    private $PATH = 'templates';


    /**
     * @var string $action Header Informationen
     */
    private $action;

    /**
     * @var array $data various data to be used in view
     */
    public $data;

    /**
     * @var string $file filename
     */
    private $file;

    /**
     * @var string $actionType
     */
    private $actionType; //z.B. zur Unterscheidung von leher oder Schüleraktionen

    /**
     *Template Dateien werden geladen
     * @param $template string
     * @return void
     */
    public function loadTemplate($template)
    {
        $templateFile = $this->PATH . DIRECTORY_SEPARATOR . $template . '.php';
        $exists = file_exists($templateFile);
        $data = $this->data;
        $file = $this->file;
        $actionType = $this->actionType;
        $action = $this->action;
        if ($exists) {
            /** @noinspection PhpIncludeInspection */
            include($templateFile);
        } else {
            die('could not find template');
        }
    }


    /**
     *set Data
     * @param array
     */
    public function setViewData($data)
    {
        $this->data = $data;
    }


    /**
     *set filename
     * @param array
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     *set header
     * @param array
     */
    public function setHeader($string)
    {
        $this->action = $string;
    }

    /**
     *set actionType
     * @param array
     */
    public function setActionType($string)
    {
        $this->actionType = $string;
    }



}


?>
