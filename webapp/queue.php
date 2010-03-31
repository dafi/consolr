<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

$tumblr = login_utils::get_tumblr();
$tumblr_queue = tumblr_utils::get_json_map($tumblr->get_queue(true));
$tumblr_posts = array(
    "tumblog" => $tumblr_queue['tumblelog'],
    "group-date" => tumblr_utils::group_posts_by_date($tumblr_queue['posts']),
    "posts" => $tumblr_queue['posts']);

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr Queue List</title>

        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">

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
            var consolrPosts = <?php echo json_encode($tumblr_posts); ?>;

            $(function() {
                consolr.updatePostsCount();
                // This ensure dates are normalized with client side timezone
                consolrPosts['posts'].forEach(function(el) {
                    el['publish-unix-timestamp'] = new Date(el['publish-on-time']).getTime();
                });

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
        <div style="text-align: right">
            <a href="multiq.php">Multiple Queue</a>
            |
            <a href="queue.php">Queue</a>
            |
            <a href="logout.php">[<?php echo $tumblr->get_tumblr_name() ?>] Logout</a>
        </div>

        <h3>Queue - <span id="count"></span></h3>
        <input type="button" id="show-tags-chart" value="Show Tags"/>

        <div id="date-container">
<?php
    foreach($tumblr_posts['group-date'] as $date => $group_posts) {
        $posts = array();
        foreach($group_posts as $gp) {
            foreach($tumblr_posts['posts'] as $p) {
                if ($p['id'] == $gp) {
                    array_push($posts, $p);
                }
            }
        }
?>
            <h3 class="date-header ui-corner-top"><span><?php echo strftime("%Y, %A %e %B", strtotime($posts[0]['publish-on-time']));?></span></h3>
            <ul id="gd<?php echo $date ?>" class="date-image-container">
    <?php foreach($posts as $p) { ?>
                <li id="i<?php echo $p['id'] ?>">
                    <img src="<?php echo $p['photo-url-75']?>" alt="<?php echo $p['slug']?>"/>
                </li>
    <?php } ?>
            </ul>
<?php } ?>
        </div>

<div id="dialog-form" title="Modify Post">
    <form action="">
    <fieldset>
        <img id="dialog-modify-thumb" src="" alt="" width="75" height="75"/>

            <label for="dialog-modify-caption">Caption</label>
            <input type="text" name="dialog-modify-caption" id="dialog-modify-caption"/>
            <br/>

            <label for="dialog-modify-tags">Tags</label>
            <input type="text" name="dialog-modify-tags" id="dialog-modify-tags"/>
            <br/>

            <label for="dialog-modify-publish-date">Publish Date</label>
            <input type="text" name="dialog-modify-publish-date" id="dialog-modify-publish-date"/>
    </fieldset>
    </form>
</div>

<div id="dialog-tags" title="Tags Chart">
    <div id="tags-chart"></div>
</div>

    </body>
</html>
