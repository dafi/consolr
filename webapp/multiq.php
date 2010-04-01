<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

$tumblr = login_utils::get_tumblr();

$info = array();
$errors = array();

if (isset($_POST['url'])) {
    $urls = $_POST['url'];
    $captions = $_POST['caption'];
    $dates = $_POST['date'];
    $tags = $_POST['tags'];

    $count = count($urls);
    for ($i = 0; $i < $count; $i++) {
        if ($urls[$i]) {
            $results = $tumblr->post_photo_to_queue($urls[$i],
                                                    $captions[$i],
                                                    $dates[$i],
                                                    explode(",", $tags[$i]));
            if ($results['status'] == 201) {
                array_push($info, $results['result']);
            } else {
                array_push($errors,
                            array("url" => $urls[$i],
                                  "caption" => $captions[$i],
                                  "date" => $dates[$i],
                                  "tags" => $tags[$i],
                                  "error_info" => $results['result']));
            }
        } else {
            // add error only if at least one field isn't empty
            if ($captions[$i] || $dates[$i] || $tags[$i]) {
                array_push($errors, array("url" => "",
                                          "caption" => $captions[$i],
                                          "date" => $dates[$i],
                                          "tags" => $tags[$i],
                                          "error_info" => "Url is mandatory"));
            }
        }
    }
}

if (count($errors) == 0) {
    array_push($errors, array("url" => "", "caption" => "", "date" => "", "tags" => ""));
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr - Queue Multiple Photos At Once</title>

        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">

        <link href="css/consolr.css" type="text/css" rel="stylesheet"/>
        <style type="text/css">
            .buttons {
                margin-top: 10px;
            }
            .error {
                color: red;
                background-color: white;
            }
        </style>

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript">
            var currPhoto = <?php echo count($errors) ?>;

            $(function() {
                $("#photo-fields1 #url\\[\\]").focus();

                $("#add-photo").click(function() {
                    var newPhotoFields = $("#photo-fields" + currPhoto).clone();
                    newPhotoFields.insertAfter("#photo-fields" + currPhoto);
                    
                    ++currPhoto;
                    newPhotoFields.attr("id", "photo-fields" + currPhoto);
                    newPhotoFields.children("legend").text("Photo " + currPhoto);

                    // clear the url and leave other fields filled
                    newPhotoFields.children("#url\\[\\]").val("").focus();
                });
            });
        </script>
    </head>
    <body>
        <noscript>
            <div>
                <a href="https://www.google.com/adsense/support/bin/answer.py?hl=en&amp;answer=12654">Javascript</a> is required to view this site.
            </div>
        </noscript>
        <?php include('inc/menu.php') ?>
        <h1>Upload multiple files to queue at once</h1>
        <?php if (count($info) > 0) { ?>
        <h3><?php echo count($info) . " post(s) inserted with success" ?></h3>
        <?php } ?>
        <form id="postForm" method="post" action="multiq.php">
<?php
    $currPhoto = 0;
    foreach ($errors as $error) {
        ++$currPhoto;
?>
            <fieldset id="photo-fields<?php echo $currPhoto ?>">
                <legend <?php if (isset($error['error_info'])) echo 'class="error"'?>>Photo <?php echo $currPhoto; if (isset($error['error_info'])) echo " contains error " . $error['error_info']; ?></legend>
                <label for="url[]">Url</label>
                <br/>
                <input type="text" name="url[]" id="url[]" value="<?php echo $error['url'] ?>" size="100"/>
                <br/>

                <label for="caption[]">Caption</label>
                <br/>
                <input type="text" name="caption[]" id="caption[]" value="<?php echo $error['caption'] ?>" size="100"/>
                <br/>
                <br/>

                <label for="date[]">Puslish Date</label>
                <br/>
                <input type="text" name="date[]" id="date[]" value="<?php echo $error['date'] ?>" size="50"/>
                <br/>
                
                <label for="tags[]">Tags</label>
                <br/>
                <input type="text" name="tags[]" id="tags[]"  value="<?php echo $error['tags'] ?>" size="50"/>
                <br/>
            </fieldset>
<?php } ?>
            <div class="buttons">
                <input id="add-photo" type="button" value="Add another photo" />
                <input type="submit" value="Insert Photos"/>
            </div>
        </form>
    </body>
</html>
