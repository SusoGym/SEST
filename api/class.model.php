<?php

namespace api;

use base\SuperModel;

class Model extends SuperModel {
    
    public function __construct() {
        parent::__construct("../cfg.ini");
    }
    
    /**
     * Fetches the data connected to an FCM token
     *
     * @param $token string the fcm token
     *
     * @return FCMConnection
     */
    public function getDataByFCMToken($token) {
        $token = $this->connection->escape_string($token);
        $query = "SELECT * FROM fcm_token WHERE token='$token'";
        
        $result = $this->connection->selectAssociativeValues($query);
        
        if ($result != null) {
            $result = $result[0];
            $cnt = new FCMConnection($result['id'], $result['userId'], $result['userType'], $result['token']);
        } else {
            $cnt = null;
        }
        
        return $cnt;
        
    }
    
    /**
     * @param $token    string the fcm token
     * @param $userId   int
     * @param $userType int
     *
     * @return array the two verification strings
     */
    public function requestTokenRegistration($token, $userId, $userType) {
        
        $this->connection->escape_stringDirect($token);
        
        $tkn1 = uniqid() . uniqid();
        $tkn2 = uniqid() . uniqid();
        
        $this->connection->straightQuery("DELETE FROM fcm_registration WHERE fcm_token='$token'");
        
        $query = "REPLACE INTO fcm_registration(userId, userType, fcm_token, verification_client, verification_server) VALUES ('$userId', '$userType', '$token', '$tkn1', '$tkn2')";
        
        $this->connection->insertValues($query);
        
        return array($tkn1, $tkn2);
    }
    
    /**
     * @param $token         string
     * @param $userId        int
     * @param $userType      int
     * @param $verification1 string
     * @param $verification2 string
     *
     * @return boolean success
     */
    public function verifyRegistration($token, $userId, $userType, $verification1, $verification2) {
        
        $this->connection->escape_stringDirect($token);
        $this->connection->escape_stringDirect($verification1);
        $this->connection->escape_stringDirect($verification2);
        
        
        $query = "SELECT * FROM fcm_registration WHERE fcm_token='$token' AND userId='$userId' AND userType='$userType' AND verification_client='$verification1' AND verification_server='$verification2'";
        
        $request = $this->connection->selectAssociativeValues($query);
        if ($request == null) {
            return "Invalid verification";
        }
        
        $insertQuery = "REPLACE INTO fcm_token(token, userId, userType) VALUES ('$token', $userId, $userType)";
        
        $this->connection->straightMultiQuery("DELETE FROM fcm_registration WHERE fcm_token='$token'; DELETE FROM fcm_token WHERE token='$token'");
        $this->connection->insertValues($insertQuery);
        
        return "OK";
    }
    
    /**
     * @param $token string the fcm token
     *
     * @return string the two verification strings
     */
    public function requestTokenDeletion($token) {
        
        $this->connection->escape_stringDirect($token);
        
        $tkn1 = uniqid() . uniqid();
        $tkn2 = uniqid() . uniqid();
        
        $this->connection->straightQuery("DELETE FROM fcm_registration WHERE fcm_token='$token'");
        
        $query = "REPLACE INTO fcm_deletion(fcm_token, verification_client, verification_server) VALUES ('$token', '$tkn1', '$tkn2')";
        
        $this->connection->insertValues($query);
        
        return array($tkn1, $tkn2);
    }
    
    /**
     * @param $token         string
     * @param $verification1 string
     * @param $verification2 string
     *
     * @return boolean success
     */
    public function verifyDeletion($token, $verification1, $verification2) {
        
        $token = self::getConnection()->escape_string($token);
        $verification1 = self::getConnection()->escape_string($verification1);
        $verification2 = self::getConnection()->escape_string($verification2);
        
        $query = "SELECT * FROM fcm_deletion WHERE fcm_token='$token' AND verification_client='$verification1' AND verification_server='$verification2'";
        
        $request = $this->connection->selectAssociativeValues($query);
        if ($request == null) {
            return "Invalid verification";
        }
        
        $this->connection->straightMultiQuery("DELETE FROM fcm_deletion WHERE fcm_token='$token'; DELETE FROM fcm_token WHERE token='$token'");
        
        return "OK";
    }
    
    /**
     * Fetches the data for a user with specific userId and userType
     *
     * @param $userId   int
     * @param $userType int (3-Student, 2-Teacher, 1-Parent)
     *
     * @return User
     */
    public function getUserByIdAndType($userId, $userType) {
        
        $table = "";
        $req = "id='$userId'";
        
        switch ($userType) {
            case 3:
                $table = "schueler";
                break;
            case 2:
                $table = "lehrer";
                break;
            default:
                $table = "eltern";
                $req = "userid='$userId'";
                break;
        }
        
        $query = "SELECT * FROM $table WHERE $req";
        
        $data = $this->connection->selectAssociativeValues($query);
        
        if ($data == null)
            return null;
        
        $data = $data[0];
        $loginName = null;
        
        switch ($userType) {
            case 1:
                $loginName = $this->connection->selectAssociativeValues("SELECT * FROM user WHERE id='$userId'")[0]["email"];
                break;
            case 2:
                $loginName = $data['ldapname'];
                break;
            case 3:
                //todo can we get the ldap name of students? do we need it?
                break;
        }
        
        
        return new User($userId, $userType, $data['name'], $data['vorname'], $loginName);
        
    }
    
}