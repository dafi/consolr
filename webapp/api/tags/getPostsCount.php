<?php
require '../../inc/dbconfig.php';
require '../../lib/db.php';

if (isset($_GET['tumblrName'])) {
    $data = array('count' => consolr_db::get_posts_count($_GET['tumblrName']));
    echo json_encode($data);
} else {
    header("HTTP/1.x 400 Invalid tumblr name");
}
?>