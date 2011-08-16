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
$photo_url_size = 'photo-url-250';
$data = array();

if (count($birth_days)) {
    foreach ($birth_days as $b) {
        $list = consolr_db::get_posts_by_tags($tumblr->get_tumblr_name(), array($b['name']));
        if (count($list)) {
            shuffle($list);
            $l = $list[0];

            $result = $tumblr->get_post_by_id($l['post_id'], true);
            $map = tumblr_utils::get_json_map($result);
            if (!empty($map)) {
                $post = $map['posts'][0];

                $published_posts = array();
                
                foreach ($list as $l) {
                    array_push($published_posts, $l['post_id']);
                }
                array_push($data, array('name' => $b['name'],
                                        'published_posts' => $published_posts,
                                        'post_id' => $post['id'],
                                        'post_url' => $post['url'],
                                        'image_url' => $post[$photo_url_size]));
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
        </style>

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/jquery.strings.js"></script>

        <script type="text/javascript" src="js/date.js"></script>
        <script type="text/javascript" src="js/consolr.js"></script>

        <script type="text/javascript">
        var tumblrName = "<?php echo $tumblr_name ?>";
        var apiUrl = 'http://' + tumblrName + '.tumblr.com/api/read/json';
        var birthInfo = <?php echo json_encode($data) ?>;

        $(function() {
            $("#toolbar button, input[type=submit]").button();
            $("#publish").click(function() {
                var names = [];
                var postIds = [];
                
                for (var i = 0; i < birthInfo.length; i++) {
                    var bi = birthInfo[i];
                    names.push(bi.name);
                    postIds.push(bi.post_id);
                }
                $.ajax({
                    url: "doPublishBirth.php",
                    type: 'post',
                    data: {
                        names: names.join(','),
                        post_ids: postIds.join(',')
                    },
                    success: function(timestamps) {
                        alert('done');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('error');
                    },
                    dataType: 'json',
                    async: false
                });
            });

            var html = '';
            var thumbPerLine = 2;
            
            for (var i = 0; i < birthInfo.length; i++) {
                var bi = birthInfo[i];
                html += '<div class="thumb">';
                html += '<a class="thumb-title" href="javascript:void(0);" data-index="' + i + '">Change Image (' + bi.published_posts.length + ')</a>';
                html += '<a target="_blank" href="' + bi.post_url + '"><img width="250" height="250" src="' + bi.image_url + '"></img></a>';
                html += '</div>';
                if (((i + 1) % thumbPerLine) == 0) {
                    html += "<br/>";
                }
            }
            $('#thumb-container').append(html);
            $('.thumb-title').click(function() {
                var thumbTitleEl = $(this);
                var index = thumbTitleEl.attr('data-index');
                var publishedPosts = birthInfo[index].published_posts;
                var postId = publishedPosts[Math.floor(Math.random() * publishedPosts.length)];

                consolr.readPublicPhotoPosts(apiUrl, {
                    id: postId,
                    complete : function(posts) {
                        var post = posts[0];
                        birthInfo[index].post_url = post.url;
                        birthInfo[index].post_id = postId;
                        birthInfo[index].image_url = post['photo-url-250'];
                        var link = $(thumbTitleEl.next());
                        var img = $('img', link);
                        link.attr('href', birthInfo[index].post_url);
                        img.attr('src', birthInfo[index].image_url);
                    }
                });
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

    <div id="thumb-container">
    </div>
    
    <?php include('inc/footer.php'); ?>
    </body>
</html>

