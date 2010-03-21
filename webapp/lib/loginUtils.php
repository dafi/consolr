<?php

require_once 'lib/tumblr/tumblr.php';

// create a session only after classes inclusion
session_start();

class login_utils {
    static function login($email, $password, $tumblr_name) {
        $tumblr = new tumblr($email, $password, $tumblr_name);
        
        $_SESSION['tumblr'] = $tumblr;
        
        return true;
    }

    static function logout() {
        unset($_SESSION['tumblr']);
    }

    static function is_logged() {
        return isset($_SESSION['tumblr']);
    }

    static function get_tumblr($redirect_if_not_logged = true) {
        $t = $_SESSION['tumblr'];
        if (isset($t)) {
            return $t;
        }
        header("location: " . "login.php");
    }
}
?>