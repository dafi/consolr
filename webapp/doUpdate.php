<?php
require_once 'lib/loginUtils.php';

$tumblr = login_utils::get_tumblr();
if (isset($_POST['postId']) && isset($_POST['publishDate'])) {
    $result = $tumblr->edit_post_queue_publish_date($_POST['postId'], $_POST['publishDate'], true);
    if ($result['status'] != "201") {
        header("HTTP/1.x 400 " . $result['result']);
    }
} else {
    header("HTTP/1.x 400 Invalid Parameters");
}
?>