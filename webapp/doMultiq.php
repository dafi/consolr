<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

$tumblr = login_utils::get_tumblr();

echo $tumblr->post_photo_to_queue($_POST['url1'], $_POST['caption1'], $_POST['date1'], explode(",", $_POST['tags1']));

echo "<br/>";

echo $tumblr->post_photo_to_queue($_POST['url2'], $_POST['caption2'], $_POST['date2'], explode(",", $_POST['tags2']));

echo "<br/>";

?>
    <a href="multiq.php">Multiple Queue</a>
    |
    <a href="queue.php">Queue</a>
    |
    <a href="logout.php">[<?php echo $tumblr->get_tumblr_name() ?>] Logout</a>
