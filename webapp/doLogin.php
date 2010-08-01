<?php
require 'lib/loginUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

$return_url = "location: login.php";

if (isset($_POST['email']) && isset($_POST['password'])) {
    if (login_utils::login($_POST['email'], $_POST['password'], null)) {
        $tumblr = login_utils::get_tumblr();
        $tumblr_name = consolr_db::get_single_setting($tumblr->get_userid(), 'defaultTumblr');
        if ($tumblr_name == null) {
            $list = $tumblr->get_tumblr_list();
            foreach ($list as $l) {
                if (isset($l['is-primary']) && ($l['is-primary'] == 'yes')) {
                    $tumblr_name = $l['name'];
                    break;
                }
            }
            consolr_db::set_single_setting($tumblr->get_userid(), 'defaultTumblr', $tumblr_name);
        }
        if ($tumblr_name == null) {
            die("Unable to find a tumblr");
        }
        $tumblr->set_tumblr_name($tumblr_name);
        consolr_db::update_login_info($tumblr_name);
        $return_url = "location: queue.php";
    }
}
header($return_url);
?>
