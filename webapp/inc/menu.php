<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

?>
        <div class="common-menu">
            <?php if (login_utils::is_logged()) { ?>
            <a href="home.php">Home</a>
            |
            <a href="multiq.php">Multiple Upload</a>
            |
            <a href="queue.php">Queue</a>
            |
            <a href="http://www.tumblr.com/tumblelog/<?php echo login_utils::get_tumblr()->get_tumblr_name() ?>" target="_blank"><img width="15" height="13" src="images/external_link.png" alt="" style="vertical-align: middle"/> Dashboard</a>
            |
            <a href="logout.php">Logout [<?php echo login_utils::get_tumblr()->get_tumblr_name() ?>]</a>
            <?php } else { ?>
            <a href="login.php">Login</a>
            <?php } ?>
        </div>
