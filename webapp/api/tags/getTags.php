<?php
require '../../inc/dbconfig.php';
require '../../lib/db.php';

$tumblr_name = isset($_GET['tumblrName']) ? $_GET['tumblrName'] : "";

if ($tumblr_name) {
    $map = array();
    if ($tumblr_name) {
        $tags = consolr_db::get_tags($tumblr_name);
        foreach ($tags as $tag) {
            $ch = strtoupper(substr($tag['tag'], 0, 1));
            if (!isset($map[$ch])) {
                $map[$ch] = array();
            }
            array_push($map[$ch], $tag);
        }
    }

    $content = json_encode($map);
    if (isset($_GET['callback'])) {
        $content = $_GET['callback'] . '(' . $content . ');';
    }

    header('Content-Type: text/javascript');
    header('Content-Length: ' . strlen($content));

    echo $content;
} else {
    header("HTTP/1.x 400 Invalid tumblr name");
}
?>