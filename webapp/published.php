<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
require_once 'inc/dbconfig.php';
require_once 'lib/db.php';

$tumblr_name = login_utils::is_logged()
    ? login_utils::get_tumblr()->get_tumblr_name()
    : (isset($_GET['tumblrName']) ? $_GET['tumblrName'] : "");
$tags = consolr_db::get_tags($tumblr_name);
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr - Published Posts</title>

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
            var tumblrName = "<?php echo $tumblr_name ?>";
            var apiUrl = 'http://' + tumblrName + '.tumblr.com/api/read/json';
            var consolrPosts = {};
                <?php
                $tag_names = array();
                foreach($tags as $tag) {
                    array_push($tag_names, $tag['tag']);
                }
                ?>
            var availableTags = <?php echo json_encode($tag_names) ?>;
            var msgInputTag = 'Type a tag to filter';
            var tagList;

            $(function() {
                $.initEditor();
                if (!tumblrName) {
                    consolr.setMessageText("Invalid tumblr name");
                    return;
                }
                $('#search-form').submit(function() {
                    consolr.setMessageText("Reading...");
                    $("#message-progress-container").show();
                    var tags = tagList.val();
                    var postsToGet = parseInt($('#postsToGet').val(), 10);
                    if (isNaN(postsToGet) || postsToGet <= 0) {
                        postsToGet = null;
                    }
                
                    consolr.readPublicPhotoPosts(apiUrl, {
                        start : 0,
                        posts : [],
                        tags : tags,
                        postsToGet: postsToGet,
                        progress : function(data, posts) {
                            consolr.setMessageText("Read posts " + posts.length + "/" + data['posts-total']);
                        },
                        complete : function(posts) {
                            consolrPosts.posts = posts;
                            consolr.initTimeline('date', false);
                
                            consolr.updateMessagePanel();
                            // add tooltip when date container is filled
                            $(".date-image")
                                .initTooltipPhotoPost()
                                .dblclick(function() {
                                    $('#dialog-form').dialog('option', 'postInfo', $(this));
                                    $('#dialog-form').dialog('option', 'consolrState', 'p');
                                    $('#dialog-form').dialog('open');
                                })
                            .initImageMenu({});
                            $("#dialog-form").initDialogModifyQueuePost({isPublishDateEditAllowed: false});
                
                            $("#message-progress-container").hide();
                        }
                    });
                    return false;
                });

                var tagList = $("#tagList");
                tagList.autocomplete({
                        source: availableTags,
                        select: function(event, ui) {
                            tagList.autocomplete('option', 'isSelected', true);
                        }
                    })
                    .showMessageOnBlur({blurMessage: msgInputTag,
                        blurClass: 'text-disabled'});

                $("#dialog-tags").initDialogTagsChart();

                $("#toolbar button, input[type=submit]").button();
                $("#show-tags-chart").click(consolr.tags.commands.showTagChart);

                $("#dialog-filter-tags").initDialogFilterTags();
                $('#filter-tags').click(consolr.tags.commands.filterTags);
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
        <?php if (login_utils::is_logged()) include('inc/menu.php') ?>

        <div id="message-panel">
            <span id="message-progress-container" style="display: none">
                <img class="message-progress-indicator" src="images/progress.gif" alt="..."/>
            </span>
            <span id="message-text" class="message-text"></span>

            <div id="operation-in-progress-panel" style="display: none" class="center-top operation-in-progress ui-corner-all ui-state-highlight">
                <span id="operation-in-progress-text"></span>
            </div>
        </div>

        <div id="toolbar" class="toolbar ui-widget-header ui-corner-all">
            <button id="show-tags-chart">Show Tags</button>
            <button id="filter-tags">Filter Tags</button>
            <form id="search-form" style="display: inline">
                <input type="text" id="tagList" size="30"/>
                <label for="postsToGet">Max posts to read</label>
                <input type="text" id="postsToGet" size="6"/>
                <input type="submit" value="Start"/>
            </form>
        </div>

        <div id="date-container">
        </div>

        <div style="display:none">
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
        </div>

    <ul id="imageMenu" class="contextMenu" style="display:none">
        <li><a href="#edit">Edit...</a></li>
        <li><a href="#showImage">Show Image</a></li>
    </ul>
    <?php include('inc/footer.php'); ?>

    </body>
</html>
