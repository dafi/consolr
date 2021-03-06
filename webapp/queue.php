<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

if (isset($_GET['state'])) {
    $state = $_GET['state'];
    if ($state != 'q' && $state != 'd') {
        $state = 'q';
    }
} else {
    $state = 'q';
}

$tumblr = login_utils::get_tumblr();

switch ($state) {
    case 'q':
        $stateDesc = 'queue';
        $stateDescCapitalized = 'Queue';
        $result = $tumblr->get_queue();
        break;
    case 'd':
        $stateDesc = 'draft';
        $stateDescCapitalized = 'Draft';
        $result = $tumblr->get_draft();
        break;
}
if ($result['status'] == '200') {
    $tumblr_posts = tumblr_utils::get_json_map($result['result']);
    $tumblr_posts = $tumblr_posts['response'];
} else {
    $tumblr_posts = array('posts' => array());
    $error = 'Error while reading ' . $stateDesc . '. ' . $result['result'];
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr <?php echo $stateDescCapitalized ?> List</title>

        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"/>

        <link type="text/css" href="css/consolr.css" rel="stylesheet"/>
        <link type="text/css" href="css/dialogs.css" rel="stylesheet"/>
        <link type="text/css" href="css/consolr/jquery-ui.css" rel="stylesheet" />
        <link type="text/css" href="css/contextMenus.css" rel="stylesheet"/>

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/jquery.tooltip.min.js"></script>
        <script type="text/javascript" src="js/jquery.strings.js"></script>
        <script type="text/javascript" src="js/jquery.contextMenu.js"></script>

        <script type="text/javascript" src="js/detectmobilebrowser.js"></script>

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
            var tumblrName = "<?php echo $tumblr->get_tumblr_name() ?>";
            var consolrPosts = {posts: <?php echo json_encode($tumblr_posts['posts']); ?>};
            var consolrState = '<?php echo $state?>';

            $(function() {
                consolr.dateProperty = '<?php echo ($state == 'd' ? 'date' : 'publish-on-time') ?>';
                $.initEditor();

                consolr.sortType = localStorage.getItem('sortType') || 0;
                $('#sort-type').val(consolr.sortType);

                var isAscending = localStorage.getItem('sortAsc');
                // getItem returns string
                isAscending = isAscending ? isAscending == "true" : true;

                consolr.initTimeline(consolr.dateProperty, isAscending);
                consolr.updateMessagePanel();
                var isMobile = $.browser.mobile;

                $(".date-image")
                    .initTooltipPhotoPost()
                    .live('dblclick', function() {
                        $('#dialog-form').dialog('option', 'postInfo', $(this));
                        $('#dialog-form').dialog('option', 'consolrState', '<?php echo $state?>');
                        $('#dialog-form').dialog('open');
                    })
                    .initImageMenu({showOnClick: isMobile,
                                   showOnHover: !isMobile});

<?php if ($state == 'd') { ?>
                $("#dialog-form").initDialogModifyQueuePost({isPublishDateEditAllowed: false});
<?php } else { ?>
                $(".date-image-container").initDraggableImage();
                $('#dialog-time-distribution').initDialogTimeDistribution();
                $("#dialog-form").initDialogModifyQueuePost();
<?php } ?>

                $("#dialog-tags").initDialogTagsChart();

                $("#toolbar button").button();
                $("#show-tags-chart").click(consolr.tags.commands.showTagChart);

                $("#dialog-filter-tags").initDialogFilterTags();
                $('#filter-tags').click(consolr.tags.commands.filterTags);

                $('#sort-direction').attr('checked', consolr.isAscending);
                $('#sort-type').change(function() {
                    consolr.sortType = parseInt($(this).val(), 10);
                    consolr.initTimeline(consolr.dateProperty, $('#sort-direction').attr('checked'));

                    localStorage.setItem('sortType', consolr.sortType);
                    localStorage.setItem('sortAsc', consolr.isAscending);

                    // how to use tooltip() with live()????
                    $(".date-image")
                        .initTooltipPhotoPost()
                        .initImageMenu({showOnClick: isMobile,
                                       showOnHover: !isMobile});
                });
                
                $('#sort-direction').click(function() {
                    $('#sort-type').change();
                });
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
        <div class="fixed-menu-bar">
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
                <select id="sort-type">
                    <option value="0">Sort by Upload Time</option>
                    <option value="1">Sort By Last Publish Time</option>
                </select>
                <label for="sort-direction">Sort Ascending <input type="checkbox" id="sort-direction"></label>
            </div>
        </div>
        <div id="fixed-menu-bar-separator"></div>

        <?php if (isset($error)) { ?>
        <h2><?php echo $error; ?></h2>
        <?php } else if (count($tumblr_posts['posts']) == 0) { ?>
        <h2>No post found on <?php echo $stateDesc ?>, use <a href="upload.php">Photo Uploader</a> or <a href="http://www.tumblr.com/tumblelog/<?php echo $tumblr->get_tumblr_name() ?>/new/photo">Dashboard</a> to add post to <?php echo $stateDesc ?></h2>
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
        <div id="time-progress-container">
            <label>Save Progress</label>
            <div id="time-progressbar"></div>
        </div>
    </div>
</div>

    <ul id="imageMenu" class="contextMenu">
        <li><a href="#edit">Edit...</a></li>
        <li><a href="#delete">Delete...</a></li>
        <li><a href="#publish">Publish...</a></li>
        <li><a href="#showImage">Show Image</a></li>
        <li><a href="#showInfo">Show Info...</a></li>
    </ul>

    <?php include('inc/footer.php'); ?>
    </body>
</html>
