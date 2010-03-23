<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

$tumblr = login_utils::get_tumblr();
$tumblr_queue = tumblr_utils::get_json_map($tumblr->get_queue(true));
$tumblr_posts = array(
    "group-date" => tumblr_utils::group_posts_by_date($tumblr_queue['posts']),
    "posts" => $tumblr_queue['posts']);
?>
<html>
    <head>
        <title>Consolr Queue List</title>

        <link href="css/consolr.css" type="text/css" rel="stylesheet"/>
        <link href="css/dialogs.css" type="text/css" rel="stylesheet"/>
        <link type="text/css" href="css/consolr/jquery-ui-1.8rc3.custom.css" rel="stylesheet" />	

        <script src="js/jquery.js"></script>
        <script src="js/jquery-ui-1.8rc3.custom.min.js"></script>
        <script src="js/jquery.tooltip.min.js"></script>

        <script src="js/date.js"></script>
        <script src="js/consolr.js"></script>

        <script type="text/javascript">
            var consolrPosts = <?php echo json_encode($tumblr_posts); ?>;

            $(function() {
                // This ensure dates are normalized with client side timezone
                consolrPosts['posts'].forEach(function(el) {
                    el['publish-unix-timestamp'] = new Date(el['publish-on-time']).getTime();
                });

                $("li").dblclick(function() {
                    $('#dialog-form').dialog('option', 'postInfo', this);
                    $('#dialog-form').dialog('open');
                });

                $("li").tooltip({
                    bodyHandler: function() {
                        var post = consolr.findPost(this.id);
                        var caption = $(post['photo-caption']).text();
                        var tags = post['tags'].join(',');
                        var time = formatDate(new Date(post['publish-unix-timestamp']), "HH:mm:ss");

                        return $("<root>"
                                 + "<span id='caption'>" + caption + "</span>"
                                 + "<span id='tags'>" + tags + "</span>"
                                 + "<span id='time'>" + time + "</span>"
                                 + "</root>").html();
                    },
                    showURL: false
                });

                $("#dialog-form").dialog({
                    autoOpen: false,
                    height: 200,
                    width: 350,
                    modal: true,
                    buttons: {
                        'Save': function() {
                            var params = {
                                postId : $(this).dialog('option', 'postInfo').id,
                                publishDate : $('#dialog-modify-publish-date').val()
                            };
                            consolr.updateImagePost(params);
                            $(this).dialog('close');
                        },
                        Cancel: function() {
                            $(this).dialog('close');
                        }
                    },
                    open: function() {
                        var postInfo = $($(this).dialog('option', 'postInfo'));
                        var post = consolr.findPost(postInfo.attr('id'));

                        $('#dialog-modify-caption').val(post['photo-caption']);
                        $('#dialog-modify-publish-date').val(formatDate(new Date(post['publish-unix-timestamp']), "dd NNN yyyy HH:mm:ss"));
                        $('#dialog-modify-tags').val(post['tags'].join(", "));
                        $('#dialog-modify-thumb').attr("src", post['photo-url-75']);
                    },
                    close: function() {
                        //allFields.val('').removeClass('ui-state-error');
                    }
                });
            });

        </script>
    </head>
    <body>
        <div style="text-align: right">
            <a href="multiq.php">Multiple Queue</a>
            |
            <a href="queue.php">Queue</a>
            |
            <a href="logout.php">[<?php echo $tumblr->get_tumblr_name() ?>] Logout</a>
        </div>

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
            <ul id="<?php echo $date ?>" class="date-image-container">
    <?php foreach($posts as $p) { ?>
                <li id="<?php echo $p['id'] ?>"">
                    <img src="<?php echo $p['photo-url-75']?>"/>
                </li>
    <?php } ?>
            </ul>
<?php } ?>
        </div>

<div id="dialog-form" title="Modify Post">
    <form>
    <fieldset>
        <img id="dialog-modify-thumb"/>
        <div>
            <label for="dialog-modify-caption">Caption</label>
            <br/>
            <input type="text" name="dialog-modify-caption" id="dialog-modify-caption"/>
            <br/>
    
            <label for="dialog-modify-publish-date">Publish Date</label>
            <br/>
            <input type="text" name="dialog-modify-publish-date" id="dialog-modify-publish-date"/>
            <br/>
    
            <label for="dialog-modify-tags">Tags</label>
            <br/>
            <input type="text" name="dialog-modify-tags" id="dialog-modify-tags"/>
        </div>
    </fieldset>
    </form>
</div>

    </body>
</html>
