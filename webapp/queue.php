<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

$tumblr = login_utils::get_tumblr();
$tumblr_queue = tumblr_utils::get_json_map($tumblr->get_queue(true));
$group_date = tumblr_utils::group_posts_by_date($tumblr_queue['posts']);
?>
<html>
    <head>
        <title>Consolr Queue List</title>

        <link href="css/consolr.css" type="text/css" rel="stylesheet"/>
        <link type="text/css" href="css/consolr/jquery-ui-1.8rc3.custom.css" rel="stylesheet" />	

        <script src="js/jquery.js"></script>
        <script src="js/jquery-ui-1.8rc3.custom.min.js"></script>
        <script src="js/consolr.js"></script>

        <script type="text/javascript">
            $(function() {
                $("li").dblclick(function() {
                    $('#dialog-form').dialog('option', 'postInfo', this);
                    $('#dialog-form').dialog('open');
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
                                publishDate : $(publishDate).val()
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
                        $(publishDate).val(postInfo.attr('publish'));
                        $(thumb).attr("src", postInfo.children('img').attr('src'));
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
<?php foreach($group_date as $key => $group) { ?>
            <h3 class="date-header ui-corner-top"><span><?php echo strftime("%Y, %A %e %B", $group[0]['unix-timestamp']);?></span></h3>
            <ul id="<?php echo $key ?>" class="date-image-container">
    <?php foreach($group as $g) { ?>
                <li id="<?php echo $g['id'] ?>" publish="<?php echo strftime("%e %b %Y %H:%M:%S", $g['unix-timestamp']); ?>">
                    <img src="<?php echo $g['photo-url-75']?>"/>
                </li>
    <?php } ?>
            </ul>
<?php } ?>
        </div>

<div id="dialog-form" title="Modify Post">
    <form>
    <fieldset>
        <img id="thumb" style="margin-right: 3px; border: 1px solid; float: left"/>
        <label for="publishDate">Publish Date</label>
        <br/>
        <input type="text" name="publishDate" id="publishDate" style="width: 180px;"/>
    </fieldset>
    </form>
</div>

    </body>
</html>
