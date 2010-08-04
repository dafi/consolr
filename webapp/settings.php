<?php
require 'lib/loginUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

$tumblr = login_utils::get_tumblr();
$tumblr_name = consolr_db::get_single_setting($tumblr->get_userid(), 'defaultTumblr');
$list = $tumblr->get_tumblr_list();

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr - Settings</title>

        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"/>

        <link type="text/css" href="css/consolr.css" rel="stylesheet"/>
        <link type="text/css" href="css/consolr/jquery-ui.css" rel="stylesheet" />

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/consolr.js"></script>
        <script>
      $(function() {
        $('#tumblr-list').change(function() {
            consolr.showOperationProgressMessageText("Changing default tumblr, refreshing...");
            $.ajax({url: "doSettings.php",
                    type: 'post',
                    async: false,
                    data: {op: 's', k: 'defaultTumblr', v: $(this).val()},
                    success: function(data, status) {
                        location.reload(true);
                    },
                    error: function(xhr, status) {
                        consolr.showOperationProgressMessageText(xhr.statusText, true);
                    }
                });
        });
      });
    </script>
    </head>
    <body>
        <noscript>
            <div class="ui-state-error">
                <a href="https://www.google.com/adsense/support/bin/answer.py?hl=en&amp;answer=12654">Javascript</a> is required to view this site.
            </div>
        </noscript>
        <?php include('inc/menu.php') ?>

        <div id="message-panel">
            <span id="message-progress-container" style="display: none">
                <img class="message-progress-indicator" src="images/progress.gif" alt="..."/>
            </span>
            <span id="message-text" class="message-text"></span>

            <div id="operation-in-progress-panel" style="display: none" class="center-top operation-in-progress ui-corner-all">
                <span id="operation-in-progress-icon-error" style="display: none; cursor:pointer; float: left; margin-right: 0.3em;" class="ui-icon ui-icon-circle-close"></span><span id="operation-in-progress-text"></span>
            </div>
        </div>

        <h1>Settings</h1>
        <form id="settingsForm" method="post" action="settings.php">
            <fieldset>
                <legend>Preferences</legend>
                <label for="defaultTumblr">Default tumblr</label>
                <select id="tumblr-list">
                <?php
                    foreach ($list as $l) {
                        $attrs = "";
                        if ($l['name'] == $tumblr_name) {
                            $attrs = "selected='true'";
                        }
                        echo sprintf("<option value='%s' %s>%s</option>", htmlspecialchars($l['name']), $attrs, $l['title']);
                    }
                ?>
                </select>
            </fieldset>
        </form>

    <?php include('inc/footer.php'); ?>
    </body>
</html>
