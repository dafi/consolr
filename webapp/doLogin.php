<?php
require 'lib/loginUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

$return_url = "location: login.php";

if (isset($_POST['email']) && isset($_POST['password'])) {
    if (login_utils::login($_POST['email'], $_POST['password'], null)) {
        if (login_utils::after_login()) {
            $return_url = "location: queue.php";
        } else {
            die("Unable to find a tumblr");
        }
    }
}
header($return_url);
?>
