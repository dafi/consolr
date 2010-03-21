<?php
require 'lib/loginUtils.php';

login_utils::logout();
header("location: login.php");

?>
