<?php
require 'lib/loginUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

$return_url = "location: login.php";

if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['tumblrName'])) {
    if (login_utils::login($_POST['email'], $_POST['password'], $_POST['tumblrName'])) {
        consolr_db::update_login_info($_POST['tumblrName']);
        $return_url = "location: queue.php";
    }
}
header($return_url);
?>
