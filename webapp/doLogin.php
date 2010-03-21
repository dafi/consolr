<?php
require 'lib/loginUtils.php';

$return_url = "location: login.php";

if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['tumblrName'])) {
    if (login_utils::login($_POST['email'], $_POST['password'], $_POST['tumblrName'])) {
        $return_url = "location: queue.php";
    }
}
header($return_url);
?>
