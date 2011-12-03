<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

define('MAX_THUMBS_PER_DIGEST', 1);
define('MAX_THUMBS_PER_ROW', 1);

$tumblr_name = login_utils::get_tumblr()->get_tumblr_name();
$cake_image_url = 'http://27.media.tumblr.com/tumblr_lj0pzqS8xX1qa5bxzo1_250.png';

function get_title($b) {
    $birth_arr = explode('-', $b['birth_date']);
    $now_arr = getdate();
    $years = $now_arr['year'] - $birth_arr[0];
    return " Happy " . $years . "th Birthday, " . $b['name'] . "!!";
}

if (isset($_POST['names']) && isset($_POST['post_ids'])) {
    $names = explode(',', $_POST['names']);
    $post_ids = explode(',', $_POST['post_ids']);

    if (count($names) == count($post_ids)) {
        setlocale(LC_TIME, 'en_US.utf8');
        $tumblr = login_utils::get_tumblr();
        $birth_days = consolr_db::get_birth_days($tumblr->get_tumblr_name(), null, $names);

        $posts = array_combine($names, $post_ids);
        foreach ($birth_days as $b) {
            $list = array(array('post_id' => $posts[$b['name']]));
            $title = get_title($b);
            $body = '<p style="font-size:18pt;">' . $b['name'] . ' we wish you a cheerful birthday</p>';
            $body .= '<img src="' . $cake_image_url . '"></img>';
            $body .= tumblr_utils::get_thumbs_html($tumblr, $list, MAX_THUMBS_PER_DIGEST, MAX_THUMBS_PER_ROW, false, '400');
            $tags = "Birthday, " . $b['name'];

            $params = array('state' => 'draft',
                            'type' => 'text',
                            'title' => $title,
                            'body' => $body,
                            'tags' => $tags);

            $tumblr->create_post($params);
        }
    }
} else {
    header("HTTP/1.x 400 Invalid arguments");
}
?>