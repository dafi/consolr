<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

?>
        <div style="text-align: right">
            <a href="multiq.php">Multiple Queue</a>
            |
            <a href="queue.php">Queue</a>
            |
            <a href="http://www.tumblr.com/tumblelog/<?php echo login_utils::get_tumblr()->get_tumblr_name() ?>" target="_blank"><img width="15" height="13" src="images/external_link.png" style="vertical-align: middle"/> Dashboard</a>
            |
            <a href="published.php">Published</a>
            |
            <a href="logout.php">Logout [<?php echo login_utils::get_tumblr()->get_tumblr_name() ?>]</a>
        </div>
