<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

$tumblr = login_utils::get_tumblr();
$tumblr_queue = tumblr_utils::get_json_map($tumblr->get_queue(true));

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr Queue List</title>

        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"/>

        <link href="css/consolr.css" type="text/css" rel="stylesheet"/>
        <link href="css/dialogs.css" type="text/css" rel="stylesheet"/>
        <link type="text/css" href="css/consolr/jquery-ui-1.8rc3.custom.css" rel="stylesheet" />

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8rc3.custom.min.js"></script>
        <script type="text/javascript" src="js/jquery.tooltip.min.js"></script>
        <script type="text/javascript" src="js/jquery.strings.js"></script>

        <script type="text/javascript" src="js/date.js"></script>
        <script type="text/javascript" src="js/consolr.js"></script>
        <script type="text/javascript" src="js/consolr.dialogs.js"></script>
        <script type="text/javascript" src="js/consolr.tooltips.js"></script>

        <script type="text/javascript" src="http://www.google.com/jsapi"></script>

        <script type="text/javascript">
        <!--//
            var consolrPosts = {posts: <?php echo json_encode($tumblr_queue['posts']); ?>};

            $(function() {
                // This ensure dates are normalized with client side timezone
                $(consolrPosts['posts']).each(function(i, el) {
                    el['publish-unix-timestamp'] = new Date(el['publish-on-time']).getTime();
                });
                consolrPosts["group-date"] = consolr.groupPostsByDate(consolrPosts.posts, 'publish-on-time');
                $("#date-container").html(consolr.getDateContainerHTML({
                        dateProperty : 'publish-on-time',
                        sortByDateAsc : false}));

                consolr.updatePostsCount();

                $("li").initTooltipPhotoPost();

                $("li").dblclick(function() {
                    $('#dialog-form').dialog('option', 'postInfo', this);
                    $('#dialog-form').dialog('open');
                });

                $("#dialog-form").initDialogModifyQueuePost();

                $("#dialog-tags").initDialogTagsChart();
                $("#show-tags-chart").click(function() {
                    $('#dialog-tags').dialog('open');
                });
            });
        -->
        </script>
    </head>
    <body>
        <noscript>
            <div>
                <a href="https://www.google.com/adsense/support/bin/answer.py?hl=en&amp;answer=12654">Javascript</a> is required to view this site.
            </div>
        </noscript>
        <?php include('inc/menu.php') ?>

        <div id="message-panel">
            <span id="message-progress-container" style="display: none">
                <img class="message-progress-indicator" src="images/progress.gif" alt="..."/>
            </span>
            <span id="message-text" class="message-text"></span>
        </div>

        <input type="button" id="show-tags-chart" value="Show Tags"/>

        <div id="date-container">
        </div>

<div style="display:none">
    <div id="dialog-form" title="Modify Post">
        <form action="">
        <fieldset>
            <div id="dialog-modify-controls">
                <label for="dialog-modify-caption">Caption</label>
                <input type="text" name="dialog-modify-caption" id="dialog-modify-caption"/>
    
                <label for="dialog-modify-tags">Tags</label>
                <input type="text" name="dialog-modify-tags" id="dialog-modify-tags"/>
    
                <label for="dialog-modify-publish-date">Publish Date</label>
                <input type="text" name="dialog-modify-publish-date" id="dialog-modify-publish-date"/>
            </div>
        </fieldset>
        </form>
    </div>

    <div id="dialog-tags" title="Tags Chart">
        <div id="tags-chart"></div>
    </div>
</div>
    </body>
</html>
