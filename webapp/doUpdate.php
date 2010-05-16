<?php
require_once 'lib/loginUtils.php';

$tumblr = login_utils::get_tumblr();
if (isset($_POST['postId']) && isset($_POST['publishDate'])) {
    $post_params = array(
        'photo-caption' => $_POST['caption'],
        'click-through-url' => $_POST['clickThroughLink'],
        'tags' => explode(",", preg_replace('/,\s*/', ',', $_POST['tags']))
                        );
    $result = $tumblr->edit_post_queue_publish_date($_POST['postId'],
                                                    $_POST['publishDate'],
                                                    false,
                                                    $post_params);
    if ($result['status'] != "201") {
        header("HTTP/1.x 400 " . $result['result']);
    }
} else {
    header("HTTP/1.x 400 Invalid Parameters");
}
?>