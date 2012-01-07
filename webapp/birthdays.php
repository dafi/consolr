<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
require 'inc/dbconfig.php';
require 'lib/db.php';

define('MAX_THUMBS_PER_DIGEST', 1);
define('MAX_THUMBS_PER_ROW', 1);

$tumblr_name = login_utils::get_tumblr()->get_tumblr_name();
$tumblr = login_utils::get_tumblr();

$time = time();
//mktime(0, 0, 0, 4, 9, 2011);

$birth_days = consolr_db::get_birth_days($tumblr->get_tumblr_name(), $time);
$photo_url_width = '250';
$data = array();

if (count($birth_days)) {
    foreach ($birth_days as $b) {
        $list = consolr_db::get_posts_by_tags($tumblr->get_tumblr_name(), array($b['name']));
        if (count($list)) {
            shuffle($list);
            $l = $list[0];

            $result = $tumblr->get_post_by_id($l['post_id']);
            $map = tumblr_utils::get_json_map($result);
            $response = $map['response'];
            if (!empty($response)) {
                $post = $response['posts'][0];
                $published_posts = array();
                
                foreach ($list as $l) {
                    array_push($published_posts, $l['post_id']);
                }
                $alt_size = tumblr_utils::get_photo_by_width($post['photos'], $photo_url_width);
                array_push($data, array('name' => $b['name'],
                                        'published_posts' => $published_posts,
                                        'post_id' => $post['id'],
                                        'post_url' => $post['post_url'],
                                        'image_url' => $alt_size['url']));
            }
        }
    }
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

        <style>
        #thumb-container {
            text-align: center;
        }

        .thumb {
            display: inline-block;
            margin-right: 10px;
        }

        .thumb-title {
            display: block;
            text-align: center;
        }
        #thumb-info {
            position: absolute;
            text-align: center;
            background-color: #AAA;
            color: #FFF;
            left: -9999px;
        }
        
        .date-image {
            cursor: pointer;
        }
        </style>

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/jquery.strings.js"></script>

        <script type="text/javascript" src="js/date.js"></script>
        <script type="text/javascript" src="js/consolr.js"></script>
        <script type="text/javascript" src="js/consolr.birthday.js"></script>

        <script type="text/javascript">
        var tumblrName = "<?php echo $tumblr_name ?>.tumblr.com";
        var apiUrl = 'http://api.tumblr.com/v2/blog/' + tumblrName;
        var birthInfo = <?php echo json_encode($data) ?>;
        var consolrPosts = {};

        $(function() {
            $("#toolbar button, input[type=submit]").button();
            $("#publish").click(function() {
                consolr.birthday.publish(birthInfo);
            });

            $('#thumb-container').append(consolr.birthday.createThumbHTML(birthInfo));
            $('.thumb-title').click(function() {
                var thumbTitleEl = $(this);
                var thumb = thumbTitleEl.parent('.thumb');
                var link = $(thumbTitleEl.next());
                var img = $('img', link);
                var index = thumb.attr('data-index');
                var selectImageInfo = birthInfo[index];

                $("#dialog-thumbnails").initSelectImageDialog();
                $('#dialog-thumbnails').dialog('option', 'selectImageInfo', selectImageInfo);
                $('#dialog-thumbnails').dialog('option', 'link', link);
                $('#dialog-thumbnails').dialog('option', 'img', img);
                $('#dialog-thumbnails').dialog('open');
            });
            $('.thumb img').hoverOnImage();
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

    <div id="toolbar" class="toolbar ui-widget-header ui-corner-all">
        <button id="publish">Publish</button>
    </div>

    <div id="thumb-container">
    </div>

    <div id="thumb-info"">
        <span>Name</span>
    </div>

    <div id="dialogs-container" style="display:none">
        <div id="dialog-thumbnails" title="Select Image">
            <div id="image-progress"></div>
            <div id="date-container">
            </div>
        </div>
    </div>

    <?php include('inc/footer.php'); ?>
    </body>
</html>

