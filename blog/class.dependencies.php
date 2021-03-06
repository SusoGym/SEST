<?php

namespace blog;

use base\SuperUtility;

class Utility {
    /** Generates Auth-Token for specified login-data
     *
     * @param $username
     * @param $pwd
     *
     * @return string|null auth-token | null if invalid login-data
     */
    static function generateAuthToken($username, $pwd) {
        
        if (!SuperUtility::verifyLogin($username, $pwd))
            return null;
        
        $user = Model::getInstance()->getUserByName($username);
        
        \ChromePhp::info("Generating AuthToken for " . $user);
        
        if ($user == null) {
            \ChromePhp::info("Creating new user as user object was null");
            $user = Model::getInstance()->createUserByName($username);
        }
        
        $authToken = Model::getInstance()->getToken($user->getId());
        
        return $authToken;
    }
}