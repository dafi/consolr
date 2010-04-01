<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
?>
        <div style="text-align: right">
            <a href="multiq.php">Multiple Queue</a>
            |
            <a href="queue.php">Queue</a>
            |
            <a href="http://www.tumblr.com/tumblelog/<?php echo $tumblr->get_tumblr_name() ?>" target="_blank">Dashboard</a>
            |
            <a href="logout.php">Logout [<?php echo $tumblr->get_tumblr_name() ?>]</a>
        </div>
