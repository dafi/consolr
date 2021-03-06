<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
require_once 'inc/dbconfig.php';
require_once 'lib/db.php';
require_once 'lib/fb/fbUtils.php';

$add_see_more = true;

$tumblr = login_utils::get_tumblr();
if (isset($_POST['postId'])) {
    $params = array();
    $caption = '';
    if (isset($_POST['caption'])) {
        $caption = $_POST['caption'];
        if ($add_see_more) {
            $params['caption'] = add_see_more_html($tumblr, $_POST['postId'], $_POST['caption']);
        } else {
            $params['caption'] = $_POST['caption'];
        } 
    }
    if (isset($_POST['clickThroughLink'])) {
        $params['click-through-url'] = $_POST['clickThroughLink'];
    }

    $result = $tumblr->publish_post($_POST['postId'], $params);
    $published_post = tumblr_utils::get_json_map($result['result']);

    if ($result['status'] == "200") {
        // id changes after publication
        $new_post_id = $published_post['response']['id'];

        $arr = tumblr_utils::get_json_map($tumblr->get_post_by_id($new_post_id));
        $post = $arr['response']['posts'][0];

        tumblr_utils::save_tags_by_post($tumblr, $post);
        publish_to_facebook($tumblr, $post, $caption);
    } else {
        header("HTTP/1.x 400 " . $published_post['meta']['msg']);
    }
} else {
    header("HTTP/1.x 400 Invalid Parameters");
}

function add_see_more_html($tumblr, $post_id, $caption) {
    $title = '<p>&nbsp;</p><p><strong>See More</strong></p>';
    $pos = strpos($caption, "See More");

    if ($pos === false) {
        $arr = tumblr_utils::get_json_map($tumblr->get_draft_post_by_id($post_id));
        $post = $arr['posts'][0];
        $see_more_tags = $post['tags'];
        // use only the first tag
        $see_more_tags = array($see_more_tags[0]);
        $caption .= tumblr_utils::get_see_more_html($tumblr, $title, $see_more_tags, 3, 3);
    }

    return $caption;
}

function publish_to_facebook($tumblr, $post, $caption) {
    $fb_user = consolr_db::get_single_setting($tumblr->get_tumblr_name(), 'fb_user');
    if ($fb_user) {
        $fb_access_token = consolr_db::get_single_setting($tumblr->get_tumblr_name(), 'fb_access_token');
        if ($fb_access_token) {
            fb_utils::simple_publish_photo($fb_user, $fb_access_token, $post, strip_tags($caption));
        }            
    }
}
?>