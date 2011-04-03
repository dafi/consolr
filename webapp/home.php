<?php
require 'lib/loginUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

$tumblr = login_utils::get_tumblr();
$birth_days = consolr_db::get_birth_days($tumblr->get_tumblr_name(), time());

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr - Home</title>

        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"/>

        <link type="text/css" href="css/consolr.css" rel="stylesheet"/>
        <link type="text/css" href="css/dialogs.css" rel="stylesheet"/>
        <link type="text/css" href="css/consolr/jquery-ui.css" rel="stylesheet" />
        <link type="text/css" href="css/contextMenus.css" rel="stylesheet"/>
        <style>
        .toolbar {
            width: 500px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 40px;
        }

        .toolbar li {
            margin: 22px 0 15px 40px;
        }

        /**
         * height must be specified otherwise the elements
         * moving to new line are shown on the right
         **/
        .toolbar .label {
            text-align:center;
            height: 20px;
        }

        .logo-container {
          width: 35em;
          margin: 0 auto;
        }
        </style>
    </head>
    <body>
        <noscript>
            <div class="ui-state-error">
                <a href="https://www.google.com/adsense/support/bin/answer.py?hl=en&amp;answer=12654">Javascript</a> is required to view this site.
            </div>
        </noscript>
        <?php include('inc/menu.php') ?>
        <div class="logo-container ui-corner-all ui-state-highlight">
            <h1 style="padding: 0pt 0.7em; margin-top: 20px;">Consolr</h1>
        </div>
        <div class="toolbar">
            <div id="cButtons" class="date-container ui-helper-clearfix ui-corner-top ui-corner-bottom ui-widget-content">
                <ul class="date-image-container">
                    <li class="date-image"><a href="upload.php"><img src="images/tb_upload.png" width="75" height="75"/><div class="date-image-time label">Photo Urls Uploader</div></a></li>
                    <li class="date-image"><a href="queue.php"><img src="images/tb_queue.png" width="75" height="75"/><div class="date-image-time label">Scheduled Posts</div></a></li>
                    <li class="date-image"><a href="queue.php?state=d"><img src="images/tb_draft.png" width="75" height="75"/><div class="date-image-time label">Draft Posts</div></a></li>
                    <li class="date-image"><a href="published.php"><img src="images/tb_published.png" width="75" height="75"/><div class="date-image-time label">Published Posts</div></a></li>
                    <li class="date-image"><a href="tags.php"><img src="images/tb_tags.png" width="75" height="75"/><div class="date-image-time label">Tags</div></a></li>
                    <li class="date-image"><a href="weeklyDigest.php"><img src="images/tb_weekly.png" width="75" height="75"/><div class="date-image-time label">Weekly Digest</div></a></li>
                    <?php if (count($birth_days)) {
                        $birth_menu_label = 'Today ' . count($birth_days) . ' birthday(s)';
                    ?>
                    <li class="date-image"><a href="birthdays.php"><img src="images/tb_birthday.png" width="75" height="75"/><div class="date-image-time label"><?php echo $birth_menu_label ?></div></a></li>
                    <?php } ?>
                    <li class="date-image"><a href="settings.php"><img src="images/tb_settings.png" width="75" height="75"/><div class="date-image-time label">Settings</div></a></li>
                </ul>
            </div>
        </div>
    <?php include('inc/footer.php'); ?>
    </body>
</html>
