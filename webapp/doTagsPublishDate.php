<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
require_once 'inc/dbconfig.php';
require_once 'lib/db.php';

$tumblr = login_utils::get_tumblr();
if (isset($_GET['tags']) && isset($_GET['tumblrName'])) {
    $arr_date = getdate();
    $now_time = mktime (0, 0, 0, $arr_date['mon'], $arr_date['mday'], $arr_date['year']);
    $day_time = 24 * 60 * 60;
    $tags = explode(",", $_GET['tags']);
    $posts = consolr_db::get_last_published_posts_by_tag($_GET['tumblrName'], $tags);
    $arr = array();

    foreach ($tags as $tag) {
        $post = null;

        foreach ($posts as $p) {
            if ($p['tag'] == $tag) {
                $post = $p;
                break;
            }
        }
        if ($post) {
            $arr_date = getdate($post['publish_timestamp']);
            $post_time = mktime (0, 0, 0, $arr_date['mon'], $arr_date['mday'], $arr_date['year']);
            $span_time = $now_time - $post_time;
            $days = floor($span_time / $day_time);

            $day_string;
            if ($days <= 0) {
                $day_string = "today";
            } else if ($days == 1) {
                $day_string = "yesterday";
            } else {
                $day_string = $days . " days ago";
            }
            array_push($arr, $post['tag'] . " <b>(" . $day_string . ")</b>");
        } else {
            array_push($arr, $tag . " <b>(new)</b>");
        }
    }
    $content = implode(", ", $arr);

    echo $content;
} else {
    header("HTTP/1.x 400 Invalid Parameters");
}

?>