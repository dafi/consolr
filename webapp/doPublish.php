<?php
require_once 'lib/loginUtils.php';

$tumblr = login_utils::get_tumblr();
if (isset($_POST['postId'])) {
    $params = array();
    if (isset($_POST['caption'])) {
        $params['caption'] = $_POST['caption'];
    }
    if (isset($_POST['clickThroughLink'])) {
        $params['click-through-url'] = $_POST['clickThroughLink'];
    }

    $result = $tumblr->publish_post($_POST['postId'], $params);
    if ($result['status'] != "201") {
        header("HTTP/1.x 400 " . $result['result']);
    }
} else {
    header("HTTP/1.x 400 Invalid Parameters");
}
?>