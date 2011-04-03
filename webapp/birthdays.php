<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

define('MAX_THUMBS_PER_DIGEST', 1);
define('MAX_THUMBS_PER_ROW', 1);

function get_title($name) {
    return $name . " Happy Birthday!!";
}

if (login_utils::is_logged()) {
    setlocale(LC_TIME, 'en_US.utf8');
    $tumblr = login_utils::get_tumblr();

    $time = time(); //mktime(0, 0, 0, 4, 3, 2011);

    $birth_days = consolr_db::get_birth_days($time);
    $html = '';

    $create_post = isset($_GET['create']);
    if (count($birth_days)) {
        foreach ($birth_days as $b) {
            $title = get_title($b['name']);
            $list = consolr_db::get_posts_by_tags($tumblr->get_tumblr_name(), array($b['name']));
            $body = '<p style="font-size:18pt;">' . $b['name'] . ' we wish you a cheerful birthday</p>';
            $body .= '<img src="http://27.media.tumblr.com/tumblr_lj0pzqS8xX1qa5bxzo1_250.png"></img>';
            $body .= tumblr_utils::get_thumbs_html($tumblr, $list, MAX_THUMBS_PER_DIGEST, MAX_THUMBS_PER_ROW, true, 'photo-url-400');
            $tags = "Birthday, " . $b['name'];
    
            $html .= $title;
            $html .= "<br/>";
            $html .= $body;
    
            $params = array('state' => 'draft',
                            'type' => 'regular',
                            'title' => $title,
                            'body' => $body,
                            'tags' => $tags);
            
            if ($create_post) {
                $tumblr->create_post($params);
            }
        }
    } else {
        $html = "<p>No items found</p>";
    }
} else {
    header("HTTP/1.x 400 Login required");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr - BirthDays</title>

        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"/>

        <link type="text/css" href="css/consolr.css" rel="stylesheet"/>
        <link type="text/css" href="css/dialogs.css" rel="stylesheet"/>
        <link type="text/css" href="css/consolr/jquery-ui.css" rel="stylesheet" />
        <link type="text/css" href="css/contextMenus.css" rel="stylesheet"/>

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>

        <script type="text/javascript">
        $(function() {
            $("#toolbar button, input[type=submit]").button();
            $("#publish").click(function() {
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

    <div id="toolbar" class="toolbar ui-widget-header ui-corner-all">
        <button id="publish">Publish</button>
    </div>
    <div style="text-align: center;">
    <?php
        echo $html;
    ?>
    </div>
    
    <?php include('inc/footer.php'); ?>
    </body>
</html>

