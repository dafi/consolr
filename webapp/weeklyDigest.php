<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

define('MAX_THUMBS_PER_DIGEST', 20);
define('MAX_THUMBS_PER_ROW', 5);

function get_title($from, $to) {
    $from_info = getdate($from);
    $to_info = getdate($to);
    $from_fmt = "%d - ";
    if ($from_info['month'] != $to_info['month']) {
        $from_fmt = "%d %B - ";
    }
    return "Maybe you missed last week ("
        . strftime($from_fmt, $from)
        . strftime("%d %B %Y", $to)
        . ")";
}

function get_thumbs_html($tumblr, $list, $thumbs_count, $images_per_row) {
    shuffle($list);
    $count = min($thumbs_count, count($list));
    $html = '<p>A little selection of the ' . count($list) . ' photos published last week</p>';
    $html .= '<p>';
    for ($i = 0; $i < $count; $i++) {
        $l = $list[$i];
        $result = $tumblr->get_post_by_id($l['post_id'], true);
        $map = tumblr_utils::get_json_map($result);
        $post = $map['posts'][0];
    
        $html .= '<a href="' . $post['url'] . '">';
        $html .= '<img border="0" src="' . $post['photo-url-75'] . '"></img>';
        $html .= '</a>&nbsp;&nbsp;';
        if ((($i + 1) % $images_per_row) == 0) {
            $html .= '</p><p>';
        }
    }
    $html .= '</p>';
    
    return $html;
}

if (login_utils::is_logged()) {
    //echo preg_replace("/.*(\\.)+(.*)/", "en_US$1$2", "it_IT");
    //echo  "<br/>";
    setlocale(LC_TIME, 'en_US.utf8');
    $tumblr = login_utils::get_tumblr();

    $last_sunday = strtotime('Monday this week -1 seconds', time());
    $last_monday = strtotime('-1 Monday', $last_sunday);

    $list = consolr_db::get_posts_by_publish_range($tumblr->get_tumblr_name(),
                               $last_monday, $last_sunday);
    $title = get_title($last_monday, $last_sunday);
    $body = get_thumbs_html($tumblr, $list, MAX_THUMBS_PER_DIGEST, MAX_THUMBS_PER_ROW);
    $tags = "Weekly Digest";

    echo "Count " . count($list);
    echo  "<br/>";
    echo "id " . $list[0]['post_id'] . " " . strftime("%A %d %b %H:%M:%S", $list[0]['publish_timestamp'])
         . "id " . $list[count($list) - 1]['post_id'] . " " . strftime(" - %A %d %b %H:%M:%S", $list[count($list) - 1]['publish_timestamp']);
    echo  "<br/>";
    echo "Range $last_monday - $last_sunday";
    echo  "<br/>";
    echo strftime("%A %d %b %H:%M:%S", $last_monday) . strftime(" - %A %d %b %H:%M:%S", $last_sunday);
    echo  "<br/>";
    echo $title;
    echo  "<br/>";
    echo $body;

    $params = array('state' => 'draft',
                    'type' => 'regular',
                    'title' => $title,
                    'body' => $body,
                    'tags' => $tags);
    //$tumblr->create_post($params);
} else {
    header("HTTP/1.x 400 Login required");
}
?>
