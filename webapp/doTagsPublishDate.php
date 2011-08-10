<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
require_once 'inc/dbconfig.php';
require_once 'lib/db.php';

$tumblr = login_utils::get_tumblr();
if (isset($_GET['tags']) && isset($_GET['tumblrName'])) {
    $tags = explode(",", $_GET['tags']);
    $posts = consolr_db::get_last_published_posts_by_tag($_GET['tumblrName'], $tags);
    $arr = array();

    foreach ($tags as $tag) {
        $post = null;

        foreach ($posts as $p) {
            if (strcasecmp($p['tag'], $tag) == 0) {
                $post = $p;
                break;
            }
        }
        if ($post) {
            array_push($arr, array('tag' => $tag, 'timestamp' => $post['publish_timestamp']));
        } else {
            array_push($arr, array('tag' => $tag, 'timestamp' => -1));
        }
    }
    $content = json_encode($arr);

    echo $content;
} else {
    header("HTTP/1.x 400 Invalid Parameters");
}

?>