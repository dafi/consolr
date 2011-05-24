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

    if ($result['status'] == "201") {
        // id changes after publication
        $new_post_id = $result['result'];
        $arr = tumblr_utils::get_json_map($tumblr->get_post_by_id($new_post_id, true));
        $post = $arr['posts'][0];

        tumblr_utils::save_tags_by_post($tumblr, $post);
        publish_to_facebook($tumblr, $post, $caption);
    } else {
        header("HTTP/1.x 400 " . $result['result']);
    }
} else {
    header("HTTP/1.x 400 Invalid Parameters");
}

function add_see_more_html($tumblr, $post_id, $caption) {
    $title = '<p>&nbsp;</p><p><strong>See More</strong></p>';
    $pos = strpos($caption, "See More");

    if ($pos === false) {
        $arr = tumblr_utils::get_json_map($tumblr->get_post_by_id($post_id, true));
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
            //fb_utils::simple_publish_photo('221887754490283',
            //                               '104849156271907|39b17ead514e1c4c7f009e94.1-100002406331668|221887754490283|9YJjowac3LlmKenbzErFB-bTJeU',
            //                               $post,
            //                               strip_tags($caption));
        }            
    }
}
?>