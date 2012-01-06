<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';

$tumblr = login_utils::get_tumblr();

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr - Photos Uploader</title>

        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"/>

        <link href="css/consolr.css" type="text/css" rel="stylesheet"/>
        <link type="text/css" href="css/consolr/jquery-ui.css" rel="stylesheet" />
        <link href="css/consolr.upload.css" type="text/css" rel="stylesheet"/>

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/jquery.validate.js"></script>
        <script type="text/javascript" src="js/date.js"></script>
        <script type="text/javascript" src="js/jquery.strings.js"></script>
        <script type="text/javascript" src="js/consolr.upload.js"></script>
        <script type="text/javascript">
            $(function() {
                consolr.upload.initUI();
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
        <div id="main-container">
        <h1>Upload multiple photo urls at once</h1>

        <div class="ui-corner-all ui-state-error error-container" style="height: 1.4em">
            <ol>
                <li><label for="url" class="error">Please specify at least one url</label></li>
                <li><label for="date" class="error">Please specify the publish date</label></li>
            </ol>
        </div>

        <form id="uploadForm" method="post" action="">
            <fieldset id="photo-fields" class="ui-widget ui-widget-content">
                <legend class="ui-widget-header ui-corner-all">Photo</legend>

                <select id="state">
                    <option value="queue">Upload on Queue</option>
                    <option value="draft">Upload on Draft</option>
                </select>
                <br/>

                <label for="url">Urls (specify an url per line)</label>
                <br/>
                <textarea name="url" id="url" cols="100" rows="4"></textarea>
                <br/>

                <label for="caption">Caption</label>
                <br/>
                <input type="text" name="caption" id="caption" value=""/>
                <br/>

                <div id="publish-date-box" style="overflow: hidden">
                    <br/>
                    <div style="float:left; margin-right: 1em;">
                        <label for="date">Publish Date</label>
                        <br/>
                        <input type="text" name="date" id="date" value="" style="width:40em"/>
                    </div>

                    <div style="float:left">
                        <label for="timespan">Photo Time Span (in minutes)</label>
                        <br/>
                        <input type="text" name="timespan" id="timespan" value="2" style="width: 4em"/>
                        <input class="button" type="checkbox" id="updateDate"/><label for="updateDate">Update Publish Date when upload finish</label>
                    </div>
                </div>

                <label for="tags">Tags</label>
                <br/>
                <input type="text" name="tags" id="tags" value=""/>
                <br/>
            </fieldset>
            <div class="ui-dialog-buttonpane ui-helper-clearfix button-box">
                <input class="button" type="button" id="clear-fields" value="Clear Fields"/>
                <input class="button" type="submit" id="upload-button" value="Upload"/>
            </div>
        </form>

        <br style="clear: both"/><br/>
        <div id="tabs">
            <ul>
                <li><a href="#tabs-1"><span>Upload status</span></a></li>
                <li><a href="#tabs-2"><span>Errors</span></a></li>
            </ul>
            <div id="tabs-1">
                <p id="upload-status" class="upload-status-message">&nbsp;</p>
                <div id="progress-panel" class="panel-list"><ul class="url-list"></ul></div>
            </div>
            <div id="tabs-2">
                <p id="upload-error-status" class="upload-status-message">&nbsp;</p>
                <div id="error-panel" class="panel-list"><ul class="url-list"></ul></div>
                <div class="ui-dialog-buttonpane ui-helper-clearfix button-box">
                    <input class="button" type="button" id="clear-errors" value="Clear Errors"/>
                </div>
                <br style="clear: both"/>
            </div>
        </div>
        </div>

    <?php include('inc/footer.php'); ?>
    </body>
</html>
