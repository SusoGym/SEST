<?php

namespace api;

use base\FireBaseMessage;
use base\SuperController;
use base\SuperUtility;

/**
 * Class Controller
 *
 * @package api
 * @property Model $model
 */
class Controller extends SuperController
{
    /**
     * Controller constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $model = new Model();
        parent::__construct($data, $model);
        $this->console = true;
    }


    // Processing methods [may have 1 arg to receive $data | may return $payload (preferably objects) | must be protected]
    // objects can be created with ->  (object) [ key1 => value1, key2 => value2, ... ]

    /**
     * @param userLogin   string
     * @param userName    string
     * @param userSurname string
     * @param userId      int
     * @param userType    int
     *
     * @param $data       array
     *
     * @return User
     */
    protected function getUserData($data)
    {
        if (self::getIgnoreCaseOrNull($data, "userName") == null xor SuperUtility::getIgnoreCaseOrNull($data, "userSurname") == null) {
            $this->missingArgs("userName", "userSurname");
        } else if (SuperUtility::getIgnoreCaseOrNull($data, "userId") == null xor SuperUtility::getIgnoreCaseOrNull($data, "userType") == null) {
            $this->missingArgs("userId", "userType");
        } else if (self::getIgnoreCaseOrNull($data, "userName") != null && SuperUtility::getIgnoreCaseOrNull($data, "userSurname") != null) {
            //todo get user by name
        } else if (SuperUtility::getIgnoreCaseOrNull($data, "userId") != null && SuperUtility::getIgnoreCaseOrNull($data, "userType") != null) {
            return $this->model->getUserByIdAndType(SuperUtility::getIgnoreCaseOrNull($data, "userId"), SuperUtility::getIgnoreCaseOrNull($data, "userType"));
        } else if (SuperUtility::getIgnoreCaseOrNull($data, "userLogin") != null) {
            //todo get by loginname
        } else {
            $this->missingArgs("userLogin", "userName", "userSurname", "userId", "userType");
        }

        return null;
    }


    /**
     * Checks if a specific FCM token is already registered in the database
     *
     * @param fcm_token string the FireBaseCloudMessaging token generated by client
     *
     * @return array whether or not the token is registered
     */
    protected function checkRegistered()
    {
        $params = $this->handleParameters("fcm_token");
        $token = $params['fcm_token'];
        $persData = $this->model->getDataByFCMToken($token);

        $data = array("token" => $token, "isExisting" => $persData != null, "data" => $persData);

        return $data;
    }

    /**
     * Requests registration of FireBaseCloudMessaging token in database
     * -> Will send verification to specific token
     *
     * @param fcm_token string the FireBaseCloudMessaging token to be registered
     * @param userId    int
     * @param userType  int
     *
     * @return string token
     */
    protected function requestRegistration()
    {

        $params = $this->handleParameters("fcm_token", "userId", "userType");

        $token = $params['fcm_token'];
        $userId = $params['userId'];
        $userType = $params['userType'];

        if ($token == null || $token == "") {
            die();
        }

        $verification = $this->model->requestTokenRegistration($token, $userId, $userType);

        $verification1 = $verification[0];
        $verification2 = $verification[1];

        (new FireBaseMessage($token))->addData("event", "verify")->addData("type", "register")->addData("verification", $verification2)->send();

        return array("token" => $token, "userId" => $userId, "userType" => $userType, "verification" => $verification1, "success" => true);
    }

    /**
     *
     * Requests registration of FireBaseCloudMessaging token in database
     * -> Will send verification to specific token
     *
     * @param fcm_token string the FireBaseCloudMessaging token to be registered
     * @param userId    int
     * @param userType  int
     *
     * @return array
     */
    protected function verifyRegistration()
    {

        $params = $this->handleParameters("fcm_token", "userId", "userType", "verification1", "verification2");

        $token = $params['fcm_token'];
        $userId = $params['userId'];
        $userType = $params['userType'];
        $verification1 = $params['verification1'];
        $verification2 = $params['verification2'];

        $message = $this->model->verifyRegistration($token, $userId, $userType, $verification1, $verification2);

        if ($message != "OK") {
            $this->message = $message;
            $this->code = 400;
        }

        return array("token" => $token, "userId" => $userId, "userType" => $userType, "verification1" => $verification1, "verification2" => $verification2, "success" => $message === "OK", "message" => $message);

    }

    /**
     * Requests deletion of FireBaseCloudMessaging token from database
     * -> Will send verification to specific token
     *
     * @param fcm_token string the FireBaseCloudMessaging token to be deleted
     *
     * @return string token
     */
    protected function requestDelete()
    {

        $params = $this->handleParameters("fcm_token");

        $token = $params['fcm_token'];


        $verification = $this->model->requestTokenDeletion($token);

        $verification1 = $verification[0];
        $verification2 = $verification[1];

        (new FireBaseMessage($token))->addData("event", "verify")->addData("type", "delete")->addData("verification", $verification2)->send();

        return array("token" => $token, "verification" => $verification1, "success" => true);
    }

    /**
     *
     * Requests deletion of FireBaseCloudMessaging token from database
     * -> Will send verification to specific token
     *
     * @param fcm_token     string the FireBaseCloudMessaging token to be deleted
     * @param verification1 string
     * @param verification2 string
     *
     * @return array
     */
    protected function verifyDelete()
    {

        $params = $this->handleParameters("fcm_token", "verification1", "verification2");

        $token = $params['fcm_token'];
        $verification1 = $params['verification1'];
        $verification2 = $params['verification2'];

        $message = $this->model->verifyDeletion($token, $verification1, $verification2);

        if ($message != "OK") {
            $this->message = $message;
            $this->code = 400;
        }

        return array("token" => $token, "verification1" => $verification1, "verification2" => $verification2, "success" => $message === "OK", "message" => $message);

    }

}