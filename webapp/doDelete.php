<?php
require_once 'lib/loginUtils.php';

$tumblr = login_utils::get_tumblr();
if (isset($_POST['postId'])) {
    $result = $tumblr->delete_post($_POST['postId']);
    if ($result['status'] != "200") {
        header("HTTP/1.x 400 " . $result['result']);
    }
} else {
    header("HTTP/1.x 400 Invalid Parameters");
}
?>