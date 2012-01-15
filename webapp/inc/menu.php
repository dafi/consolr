<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

?>
        <div class="common-menu">
            <?php if (login_utils::is_logged()) { ?>
            <a href="http://<?php echo login_utils::get_tumblr()->get_tumblr_name() ?>.tumblr.com" target="_blank" class="tumblr-name"><?php echo login_utils::get_tumblr()->get_tumblr_name() ?></a>
            |
            <a href="home.php">Home</a>
            |
            <a href="upload.php" target="_blank">Photo Uploader</a>
            |
            <a href="queue.php" target="_blank">Queue</a>
            |
            <a href="queue.php?state=d" target="_blank">Draft</a>
            |
            <a href="http://www.tumblr.com/tumblelog/<?php echo login_utils::get_tumblr()->get_tumblr_name() ?>" target="_blank"><img width="15" height="13" src="images/external_link.png" alt="" style="vertical-align: middle"/> Dashboard</a>
            |
            <a href="logout.php">Logout</a>
            <?php } else { ?>
            <a href="login.php">Login</a>
            <?php } ?>
        </div>
