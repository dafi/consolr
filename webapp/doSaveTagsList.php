<?php
require_once 'lib/loginUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

if (login_utils::is_logged()) {
    if (isset($_POST['tagsMap'])) {
        $deleteTags = isset($_POST['deleteTags'])
            ? ($_POST['deleteTags'] == "true")
            : false;
        $size = 0;
        foreach ($_POST['tagsMap'] as $k => $v) {
            $size += count($v);
        }
        consolr_db::save_tags_list(login_utils::get_tumblr()->get_tumblr_name(),
                                   $_POST['tagsMap'],
                                   $deleteTags);
    } else {
        header("HTTP/1.x 400 Tags map is mandatory");
    }
} else {
    header("HTTP/1.x 400 Login required");
}
?>