<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

$tumblr = login_utils::get_tumblr();

$info = array();
$errors = array();

$urls = array('');
$captions = array('');
$dates = array('');
$tags = array('');
$timespan = array('2');
$next_span_time = '';

if (isset($_POST['url'])) {
    $urls = $_POST['url'];
    $captions = $_POST['caption'];
    $dates = $_POST['date'];
    $tags = $_POST['tags'];
    $timespan = $_POST['timespan'];

    $count = count($urls);
    for ($i = 0; $i < $count; $i++) {
        if ($urls[$i]) {
            // remove all \r and any \r or \n at end of string then split string
            $arr_urls = preg_split("/\n+/", preg_replace("/\r|(\r|\n)+$/", "", $urls[$i]));
            $invalid_urls = array();
            $int_timespan = intval($timespan[$i]);
            if ($int_timespan <= 0) {
                $int_timespan = 2;
            }
            $int_timespan *= 60;
            $timespan_seconds = 0;

            foreach ($arr_urls as $u) {
                $time = strtotime($dates[$i]);

                if ($time === false) {
                    $results = array('status' => 400, 'result' => 'Invalid date format: ' . $dates[$i]);
                } else {
                    $time += $timespan_seconds;
                    $span_date = strftime("%d %b %y %H:%M:%S", $time);

                    //$results = $tumblr->post_photo_to_queue($u,
                    //                                        $captions[$i],
                    //                                        $span_date,
                    //                                        explode(",", $tags[$i]));
                    $results = array('status' => '201', 'result' => 'TEST WITHOUT REAL POST');
                }
                if ($results['status'] == 201) {
                    array_push($info, $results['result']);
                } else {
                    array_push($invalid_urls, $u);
                }
                $timespan_seconds += $int_timespan;
            }
            // contain the time for next images
            $next_span_time = strftime("%d %b %Y %H:%M:%S", $time + $int_timespan);

            if (count($invalid_urls)) {
                array_push($errors,
                            array("url" => implode("\n", $invalid_urls),
                                  "caption" => $captions[$i],
                                  "date" => $dates[$i],
                                  "tags" => $tags[$i],
                                  "timespan" => $timespan[$i],
                                  "error_info" => $results['result']));
            }
        } else {
            // add error only if at least one field isn't empty
            if ($captions[$i] || $dates[$i] || $tags[$i]) {
                array_push($errors, array("url" => "",
                                          "caption" => $captions[$i],
                                          "date" => $dates[$i],
                                          "tags" => $tags[$i],
                                          "timespan" => $timespan[$i],
                                          "error_info" => "Url is mandatory"));
            }
        }
    }
}

if (count($errors) == 0) {
    array_push($errors, array("url" => "",
                              "caption" => $captions[0],
                              "date" => $next_span_time,
                              "tags" => $tags[0],
                              "timespan" => $timespan[0]));
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr - Queue Multiple Photos At Once</title>

        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"/>

        <link href="css/consolr.css" type="text/css" rel="stylesheet"/>
        <link type="text/css" href="css/consolr/jquery-ui.css" rel="stylesheet" />

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
                
                $('#clear-fields').click(function() {
                    $('#url\\[\\]').val('').focus();
                    $('#caption\\[\\]').val('');
                    $('#date\\[\\]').val('');
                    $('#tags\\[\\]').val('');
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
        <h1>Upload multiple files to queue at once</h1>
        <?php if (count($info) > 0) { ?>
        <div class="ui-state-highlight ui-corner-all" style="padding: 0pt 0.7em; margin-top: 20px;"> 
            <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: 0.3em;"></span>
	    <?php echo count($info) . " post(s) inserted with success" ?></p>
	</div>
        <?php } ?>
        <form id="multiqForm" method="post" action="multiq.php">
<?php
    $currPhoto = 0;
    foreach ($errors as $error) {
        ++$currPhoto;
?>
            <fieldset id="photo-fields<?php echo $currPhoto ?>">
                <legend>Photo <?php echo $currPhoto;?></legend>

                <?php if (isset($error['error_info'])) { ?>
                <div class="ui-corner-all ui-state-error" style="height: 1.4em">
                    <span style="margin: 0.3em;"><?php echo $error['error_info']; ?></span>
                </div>
                <?php } ?>

                <label for="url[]">Urls (specify an url per line)</label>
                <br/>
                <textarea name="url[]" id="url[]" cols="100" rows="4"><?php echo htmlspecialchars($error['url']) ?></textarea>
                <br/>

                <label for="caption[]">Caption</label>
                <br/>
                <input type="text" name="caption[]" id="caption[]" value="<?php echo htmlspecialchars($error['caption']) ?>"/>
                <br/>
                <br/>

                <div style="overflow: hidden">
                    <div style="float:left; margin-right: 1em;">
                        <label for="date[]">Publish Date</label>
                        <br/>
                        <input type="text" name="date[]" id="date[]" value="<?php echo htmlspecialchars($error['date']) ?>" style="width:40em"/>
                    </div>

                    <div style="float:left">
                        <label for="timespan[]">Photo Time Span (in minutes)</label>
                        <br/>
                        <input type="text" name="timespan[]" id="timespan[]"  value="<?php echo htmlspecialchars($error['timespan']) ?>" style="width: 4em"/>
                    </div>
                </div>

                <label for="tags[]">Tags</label>
                <br/>
                <input type="text" name="tags[]" id="tags[]"  value="<?php echo htmlspecialchars($error['tags']) ?>"/>
                <br/>
            </fieldset>
<?php } ?>
            <div class="ui-dialog-buttonpane ui-helper-clearfix button-box">
                <input id="clear-fields" type="button" value="Clear fields" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"/>
                <!--<input id="add-photo" type="button" value="Add another photo" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"/>-->
                <input type="submit" value="Insert Photos" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"/>
            </div>

        </form>

    <?php include('inc/footer.php'); ?>
    </body>
</html>
