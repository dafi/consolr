<?php
require "inc/oauthConfig.php";
require_once 'lib/OAuth.php';
require_once 'lib/tumblr/tumblr.php';

// create a session only after classes inclusion
session_start();

class login_utils {
    static function login($email, $password, $tumblr_name) {
        $tumblr = new tumblr($email, $password, $tumblr_name);

        $_SESSION['tumblr'] = $tumblr;

        return true;
    }

    static function login_oauth($oauth_token, $oauth_token_secret, $tumblr_name) {
        $tumblr = new tumblr_oauth($oauth_token, $oauth_token_secret, $tumblr_name);

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

    static function after_login() {
        $tumblr = login_utils::get_tumblr();
        $tumblr_name = consolr_db::get_single_setting($tumblr->get_userid(), 'defaultTumblr');
        if ($tumblr_name == null) {
            $list = $tumblr->get_tumblr_list();
            foreach ($list as $l) {
                if (isset($l['is-primary']) && ($l['is-primary'] == 'yes')) {
                    $tumblr_name = $l['name'];
                    break;
                }
            }
            consolr_db::set_single_setting($tumblr->get_userid(), 'defaultTumblr', $tumblr_name);
        }
        if ($tumblr_name == null) {
            return false;
        }
        $tumblr->set_tumblr_name($tumblr_name);
        consolr_db::update_login_info($tumblr_name);
        return true;
    }
}
?>