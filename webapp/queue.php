<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

$tumblr = login_utils::get_tumblr();
$result = $tumblr->get_queue(true);
if ($result['status'] == '200') {
    $tumblr_queue = tumblr_utils::get_json_map($result['result']);
} else {
    $tumblr_queue = array('posts' => array());
    $error = 'Error while reading queue. ' . $result['result'];
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr Queue List</title>

        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"/>

        <link type="text/css" href="css/consolr/jquery-ui.css" rel="stylesheet" />

        <link type="text/css" href="css/consolr.css" rel="stylesheet"/>
        <link type="text/css" href="css/dialogs.css" rel="stylesheet"/>
        <link type="text/css" href="css/contextMenus.css" rel="stylesheet"/>

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/jquery.tooltip.min.js"></script>
        <script type="text/javascript" src="js/jquery.strings.js"></script>
        <script type="text/javascript" src="js/jquery.contextMenu.js"></script>

        <script type="text/javascript" src="js/date.js"></script>
        <script type="text/javascript" src="js/consolr.groupDate.js"></script>
        <script type="text/javascript" src="js/consolr.js"></script>
        <script type="text/javascript" src="js/consolr.tags.js"></script>
        <script type="text/javascript" src="js/consolr.dialogs.js"></script>
        <script type="text/javascript" src="js/consolr.initializers.js"></script>

        <script type="text/javascript" src="http://www.google.com/jsapi"></script>
        <script type="text/javascript" src="js/tiny_mce/tiny_mce.js"></script>

        <script type="text/javascript">
        <!--//
            var consolrPosts = {posts: <?php echo json_encode($tumblr_queue['posts']); ?>};

            $(function() {
                $.initEditor();
                consolr.initTimeline('publish-on-time', true);
                consolr.updateMessagePanel();

                $(".date-image")
                    .initTooltipPhotoPost()
                    .dblclick(function() {
                        $('#dialog-form').dialog('option', 'postInfo', $(this));
                        $('#dialog-form').dialog('open');
                    })
                    .initImageMenu({});

                $(".date-image-container").initDraggableImage();

                $("#dialog-form").initDialogModifyQueuePost();

                $("#dialog-tags").initDialogTagsChart();

                $("#toolbar button").button();
                $("#show-tags-chart").click(consolr.tags.commands.showTagChart);

                $("#dialog-filter-tags").initDialogFilterTags();
                $('#filter-tags').click(consolr.tags.commands.filterTags);

                $('#dialog-time-distribution').initDialogTimeDistribution();
            });
        -->
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
            <button id="show-tags-chart">Tags Chart</button>
            <button id="filter-tags">Filter Tags</button>
        </div>

        <?php if (isset($error)) { ?>
        <h2><?php echo $error; ?></h2>
        <?php } else if (count($tumblr_queue['posts']) == 0) { ?>
        <h2>No post scheduled, use <a href="multiq.php">Photo Uploader</a> or <a href="http://www.tumblr.com/tumblelog/<?php echo $tumblr->get_tumblr_name() ?>/new/photo">Dashboard</a> to add post to queue</h2>
        <?php } ?>

        <div id="date-container">
        </div>

<div id="dialogs-container" style="display:none">
    <div id="dialog-form" title="Modify Post">
        <form action="">
        <fieldset>
            <div id="dialog-modify-controls">
                <label for="dialog-modify-caption">Caption</label>
                <textarea name="dialog-modify-caption" id="dialog-modify-caption" rows="4"></textarea>

                <label for="dialog-modify-tags">Tags</label>
                <input type="text" name="dialog-modify-tags" id="dialog-modify-tags"/>

                <label for="dialog-modify-publish-date">Publish Date</label>
                <input type="text" name="dialog-modify-publish-date" id="dialog-modify-publish-date"/>

                <label for="dialog-modify-click-through-link">Click Through Link</label>
                <input type="text" name="dialog-modify-click-through-link" id="dialog-modify-click-through-link"/>
            </div>
        </fieldset>
        </form>
    </div>

    <div id="dialog-tags" title="Tags Chart">
        <div id="tags-chart"></div>
    </div>

    <div id="dialog-filter-tags" title="Filter Tags">
        <label for="search">Search</label>
        <input id="search" type="text" autocomplete="off"/>
        <div id="tagList" style="height: 100%; overflow-y: visible"></div>
    </div>

    <div id="dialog-time-distribution" title="Time Range Distribution">
        <p>
            <label for="time-value">Hours Range</label>
            <span id="time-value"></span>
        </p>
        <div id="time-range"></div>
    </div>
</div>

    <ul id="imageMenu" class="contextMenu">
        <li><a href="#edit">Edit...</a></li>
        <li><a href="#delete">Delete...</a></li>
        <li><a href="#publish">Publish...</a></li>
        <li><a href="#showImage">Show Image</a></li>
    </ul>

    <?php include('inc/footer.php'); ?>
    </body>
</html>
