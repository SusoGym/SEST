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
	* @var array() various data to be shown in view
	*/
	private $dataForView=null;

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
	* @var array containing link and text to display menue item
	*/
	private $simpleMenueItems;

	/**
	* @var string link for backButton
	*/
	private $backButton;

	/**
	*@var array
	*/
	private $teachersOfForm;

	/**
	*@var string Klasse die bearbeitet wird
	*/
	private $currentForm;
	
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
	*set dataForView
	* @param array
	*/
	public function setDataForView($data){
		$this->dataForView=$data;
		}
	
    /**
     *set Data
     * @param array
     */
    public function setViewData($data)
    {
        $this->data =  $data;
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
    public function setHeaderInfo($string)
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

	/**
	*set menue Items for simple menue
	*@param array
	*/
	public function setSimpleMenueItems($menue){
		$this->simpleMenueItems=$menue;
		}
		
		
	/**
	*set backbuutton link
	*@param array
	*/
	public function setBackButton($buttonLink){
		$this->backButton=$buttonLink;
		}
		
	/**
	*set array including forms of each teacher
	*@param array
	*/
	public function setTeachersOfForm($tArray){
		$this->teachersOfForm=$tArray;
		}


	/**
	*set form that is currently being worked on
	*@param array
	*/
	public function setCurrentForm($form){
		$this->currentForm=$form;
		}

}


?>
