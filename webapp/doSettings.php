<?php
require 'lib/loginUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

// should get from a well known table
function is_valid_key($k) {
    return $k == 'defaultTumblr';
}

function refresh_session_data($k, $v) {
    if ($k == 'defaultTumblr') {
        $tumblr = login_utils::get_tumblr();
        $tumblr->set_tumblr_name($v);
    }
}

$errorMsg = null;

if (isset($_POST['op']) && isset($_POST['k']) && isset($_POST['v'])) {
    $op = $_POST['op'];
    $k = $_POST['k'];
    $v = $_POST['v'];

    if (is_valid_key($k)) {
        $tumblr = login_utils::get_tumblr();

        if ($op == 's') {
            if (consolr_db::set_single_setting($tumblr->get_userid(), $k, $v)) {
                refresh_session_data($k, $v);
            } else {
                $errorMsg = 'Error while setting property';
            }
        } else {
            $errorMsg = 'Invalid operation';
        }
    } else {
        $errorMsg = 'Unknown setting key';
    }
} else {
    $errorMsg = 'Invalid parameters';
}
if ($errorMsg) {
    header("HTTP/1.x 400 $errorMsg");
}
?>