<?php
require_once 'lib/loginUtils.php';

$tumblr = login_utils::get_tumblr();
if (isset($_POST['postId'])) {
    if (isset($_POST['state'])) {
        $state = $_POST['state'];
    } else {
        $state = 'q';
    }
    switch ($state) {
        case 'd':
        case 'p':
            $post_params = array(
                'photo-caption' => $_POST['caption'],
                'click-through-url' => $_POST['clickThroughLink'],
                'tags' => explode(",", preg_replace('/,\s*/', ',', $_POST['tags']))
                                );
            $result = $tumblr->update_photo_post($_POST['postId'],
                                                    $state == 'd' ? 'draft' : 'published',
                                                    false,
                                                    $post_params);
            break;
        case 'q':
            if (isset($_POST['publishDate'])) {
                $post_params = array(
                    'photo-caption' => $_POST['caption'],
                    'click-through-url' => $_POST['clickThroughLink'],
                    'tags' => explode(",", preg_replace('/,\s*/', ',', $_POST['tags']))
                                    );
                $result = $tumblr->edit_post_queue_publish_date($_POST['postId'],
                                                                $_POST['publishDate'],
                                                                false,
                                                                $post_params);
            } else {
                $result = array('status' => '400', 'result' => 'Publish date is mandatory');
            }
            break;
        default:
            $result = array('status' => '400', 'result' => "Invalid state '" . $state . "'");
            break;
    }
    if ($result['status'] != "201") {
        header("HTTP/1.x 400 " . $result['result']);
    }
} else {
    header("HTTP/1.x 400 Invalid Parameters");
}
?>