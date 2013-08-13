<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

$tumblr_name = login_utils::get_tumblr()->get_tumblr_name();
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr - Tags</title>

        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"/>

        <link type="text/css" href="css/consolr.css" rel="stylesheet"/>
        <link type="text/css" href="css/dialogs.css" rel="stylesheet"/>
        <link type="text/css" href="css/consolr/jquery-ui.css" rel="stylesheet" />

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/jquery.strings.js"></script>

        <script type="text/javascript" src="js/consolr.js"></script>

        <script type="text/javascript">
        <!--//
            var tumblrName = "<?php echo $tumblr_name ?>";
            var apiUrl = 'http://api.tumblr.com/v2/blog/' + tumblrName + '.tumblr.com';
            var msgTitle = 'Consolr - Tags';
            var msgTitleProgress = 'Read posts $c/$t';

            $(function() {
                $('#update-tags').click(function() {
                    var deleteTags = $('#delete-existing-tags').attr('checked');
                    var settings = {
                        start : 0,
                        //type: '',
                        posts : [],
                        //postsToGet: 200,
                        progress : function(data, posts) {
                            var msg = $.formatString(msgTitleProgress, {
                                c: posts.length,
                                t: data['posts-total']});
                            consolr.setMessageText(msg);
                            window.document.title = msg + " " + msgTitle;
                        },
                        complete : function(posts) {
                            window.document.title = msgTitle;
                            saveTagsMap(posts, deleteTags);
                        }
                    };

                    var postsCount = null;

                    if (deleteTags) {
                        consolr.readPublicPhotoPosts(apiUrl, settings);
                    } else {
                        // this call is really sync
                        $.ajax({url: 'api/tags/getPostsCount.php?tumblrName=' + tumblrName,
                                dataType: 'json',
                                async: false,
                                success: function(data, status) {
                                    postsCount = data['count'];
                                },
                                error: function(xhr, status) {
                                    alert("Error while getting post count " + xhr.statusText);
                                }
                            });
                        if (postsCount !== null) {
                            // this call isn't sync because jsonp calls can't be sync
                            consolr.readPublicPhotoPosts(apiUrl, {
                                start : 0,
                                //type: '',
                                postsToGet: 1,
                                posts : [],
                                complete : function(posts, data) {
                                    settings.postsToGet = data['posts-total'] - postsCount;
                                    consolr.readPublicPhotoPosts(apiUrl, settings);
                                }
                            });
                        }
                    }
                });
            });

            function saveTagsMap(posts, deleteTags) {
                if (posts.length == 0) {
                    consolr.showOperationProgressMessageText("No tags to add");
                    window.setTimeout(function() {
                        consolr.hideOperationProgressMessageText();
                    }, 4000);
                    return;
                }

                var tagsMap = {};

                for (var i in posts) {
                    var post = posts[i];
                    var tags = post.tags;

                    var showOrder = 1;
                    for (var j in tags) {
                        var tag = tags[j];

                        if (typeof (tagsMap[tag]) == 'undefined') {
                            tagsMap[tag] = [{id: post.id, ts: post['timestamp'], so: showOrder++}];
                        } else {
                            if (tagsMap[tag].indexOf(post.id) >= 0) {
                                if (window.console) console.error('Found duplicated tag "' + tag + '" for post ' + post.id);
                            } else {
                                tagsMap[tag].push({id: post.id, ts: post['timestamp'], so: showOrder++});
                            }
                        }
                    }
                }
                var size = 0;
                var delFlag = deleteTags;
                destMap = {};

                for (var i in tagsMap) {
                    if (size > 800) {
                        consolr.saveTagsList({tagsMap: destMap, deleteTags: delFlag});
                        delFlag = false;
                        destMap = {};
                        size = 0;
                    }
                    destMap[i] = tagsMap[i];
                    size += tagsMap[i].length;
                }
                consolr.saveTagsList({tagsMap: destMap, deleteTags: delFlag});
            }
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
            <input id="delete-existing-tags" type="checkbox">Delete existing tags
            <button id="update-tags" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-focus">Update tag list</button>
        </div>

    <?php include('inc/footer.php'); ?>

    </body>
</html>
