<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

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
    if ($result['status'] == "201") {
        // id changes after publication
        $new_post_id = $result['result'];
        save_tags($tumblr, $new_post_id);
    } else {
        header("HTTP/1.x 400 " . $result['result']);
    }
} else {
    header("HTTP/1.x 400 Invalid Parameters");
}

function save_tags($tumblr, $post_id) {
    $arr = tumblr_utils::get_json_map($tumblr->get_post_by_id($post_id, true));
    $post = $arr['posts'][0];
    
    $ts = $post['unix-timestamp'];
    $tags_map = array();
    foreach ($post['tags'] as $tag) {
        $tags_map[$tag] = array(array('id' => $post_id, 'ts' => $ts));
    }
    consolr_db::save_tags_list($tumblr->get_tumblr_name(),
                               $tags_map,
                               false,
                               true);
}
?>