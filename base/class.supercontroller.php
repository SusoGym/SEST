<?php
namespace base;
class SuperController extends SuperUtility
{

    /** @var bool defines whether this execution is back- or frontend */
    protected $console = false;
    /** @var array User input */
    protected $data;
    /** @var  SuperModel */
    protected $model;

    // Response Variables
    /** @var int The response code (based upon HTML codes) */
    protected $code = 200;
    /** @var string The response status message */
    protected $message = "OK";
    /** @var mixed */
    protected $responseData = null;
    /** @var string */
    protected $action = null;


    /**
     * Controller constructor.
     *
     * @param $data array input data
     * @param $model SuperModel
     */
    public function __construct($data, $model) {
        $this->data = $data;
        $this->model = $model;
        $this->console = isset($data["console"]);//self::getExistentAndValue($data, "console");

    }


    /**
     * Deconstruct. Combines all the result of the processing of the input in a json response
     */
    function __destruct() {

        if (!$this->console)
            return;

        if (!self::$errorless_exit)
            return;

        $outputArray = array();

        if ($this->action != null) {
            $outputArray = array("action" => $this->action);
        }

        $outputArray = array_merge($outputArray, array("code" => $this->code, "message" => $this->message));

        if ($this->responseData != null) {
            $outputArray = array_merge($outputArray, array("payload" => $this->responseData));
        } else {
            $outputArray = array_merge($outputArray, array("payload" => null));
        }

        die(json_encode($outputArray, JSON_PRETTY_PRINT));
    }

    /**
     * Starts the process of data processing
     */
    public function start() {

        if (!$this->console) {
            $template = self::getOrFallBack($this->data, 'template', SuperUtility::$DEFAULT_TEMPLATE);
            self::displayTemplate($template);

            return;
        } else {
            header('Content-Type: text/json');
        }

        $responseData = null;

        // following will call methods with the same name as is requested in $data['action'] (method must not be private),
        // data will be provided if parameter count > 0
        if (isset($this->data['action']) && method_exists($this, $this->data['action'])) {

            $reflect = new \ReflectionMethod($this, $this->data['action']);

            if ($reflect->isProtected()) {
                $reflect->setAccessible(true);


                if ($reflect->getNumberOfParameters() == 0) {
                    $this->responseData = $reflect->invoke($this);
                } else {
                    $this->responseData = $reflect->invoke($this, $this->data);
                }
                $this->action = $reflect->getName();

                die();
            }
        }

        $this->code = 404;
        $this->message = "Invalid or unknown action!";

    }

    /**
     * Creates response for missing arguments
     * @param  $names array[string]
     */
    protected function missingArgs(...$names) {
        $this->code = 400;

        if (is_array($names[0]))
            $names = $names[0];

        $msg = "";

        for ($i = 0; $i < sizeof($names); $i++) {

            $msg .= "'" . $names[$i] . "'";
            if ($i != sizeof($names) - 1)
                $msg .= ", ";

        }

        $this->message = "Missing parameter" . (sizeof($names) > 1 ? "s" : "") . " $msg!";
        die();
    }

    /**
     * Creates response for being unauthorized
     */
    protected function unauthorized() {
        $this->code = 401;
        $this->message = "User is not allowed to perform this action!";
        die();
    }

    /**
     * Return array with all requested parameters, if existent in $data use that value, else call missingArgs()
     *
     * @param array ...$requested
     *
     * @return array
     */
    protected function handleParameters(...$requested) {
        $data = $this->data;
        $response = array();
        $missing = array();

        foreach ($requested as $request) {
            $value = SuperUtility::getIgnoreCaseOrNull($data, $request);
            if ($value == null) {
                array_push($missing, $request);
            } else {
                $response[$request] = $value;
            }
        }

        if (sizeof($missing) != 0) {
            $this->missingArgs($missing);
        }

        return $response;
    }

    /**
     * Return array with all requested parameters, if existent in $data use that value, else use null
     *
     * @param array ...$requested
     *
     * @return array
     */
    protected function handleOptionalParameters(...$requested) {
        $data = $this->data;
        $response = array();

        foreach ($requested as $request) {
            $value = SuperUtility::getIgnoreCaseOrNull($data, $request);
            $response[$request] = $value;

        }

        return $response;
    }

}