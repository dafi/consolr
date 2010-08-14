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
        <style>
        #progress-panel {
            height: 7em;
            overflow-y: auto;
        }

        .url-list {
            list-style-image: url('images/progress-w.gif');
        }

        .error-upload {
            cursor: pointer;
            list-style-image: none;
            list-style-type: none;
            list-style-position: inside;
        }

        .error-container ol li {
            list-style-type: disc;
            margin-left: 20px;
        }
        .error-container {
            height: 4em !important;
            display: none;
        }
        </style>
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/jquery.validate.js"></script>
        <script type="text/javascript" src="js/date.js"></script>
        <script type="text/javascript" src="js/jquery.strings.js"></script>
        <script type="text/javascript">
            var urlsNotYetUploaded = 0;
            var urlsTotal = 0;
            var uploadSuccess = 0;
            var uploadFail = 0;
            var lastDate;

            $(function() {
                $("#url").focus();
                $('.button').button();
                $("#tabs").tabs();

                var container = $('.error-container');

                $('#multiqForm').validate({
                    errorContainer: container,
                    errorLabelContainer: $("ol", container),
                    wrapper: 'li',
                    rules: {
                      url: "required",
                      date: "required"
                    },
                    submitHandler: function(form) {
                        try {
                            if (isNaN(Date.parse($('#date').val()))) {
                                alert('Invalid date format');
                            } else {
                                startUpload();
                            }
                        } catch (e) {
                            alert(e);
                        }
                    }
                  });

                $('#clear-fields').click(function() {
                    $('#url').val('').focus();
                    $('#caption').val('');
                    $('#date').val('');
                    $('#tags').val('');
                });

                $('#clear-errors').click(function() {
                    $('#error-panel ul').empty();
                });

                $('#progress-panel, #error-panel').click(function(event) {
                    if (event.target.hasAttribute('crdate')) {
                        $('#url').val(event.target.getAttribute('crurl'));
                        $('#date').val(event.target.getAttribute('crdate'));
                    }
                });
            });

            function startUpload() {
                var urls = $('#url').val()
                    .replace(/^\s*$/mg, '') // remove empty lines
                    .replace(/[\r\n]+/g, '\n') // all carriage return/newline
                    .replace(/\n$/, ''); // remove newline at end
                if (!urls.length) {
                    return;
                }
                urls = urls.split('\n');
                urlsNotYetUploaded = urls.length;
                urlsTotal = urls.length;
                uploadSuccess = 0;
                uploadFail = 0;

                // ensure tab status is visible
                $('#tabs').tabs('select', 0);

                $('#upload-button').button('disable');
                $('#clear-errors').button('disable');
                $('#progress-panel ul').empty();
                var progressPanel = $('#progress-panel ul');

                var params = {
                    caption: $('#caption').val(),
                    tags: $('#tags').val(),
                };

                var timespanMS = parseInt($('#timespan').val(), 10);
                var timespanMS = (isNaN(timespanMS) ? 2 : Math.max(2, timespanMS)) * 1000 * 60;
                var millisecs = new Date($('#date').val()).getTime();
                for (var i in urls) {
                    params.url = urls[i];
                    params.date = new Date(millisecs).format("dd NNN yyyy HH:mm:ss");
                    var el = $('<li>Uploading photo from ' + params.url + '</li>');
                    el.attr('crurl', params.url);
                    el.attr('crdate', params.date);

                    doUpload(progressPanel, el, params);
                    millisecs += timespanMS;
                }
                // prepare with next date
                lastDate = new Date(millisecs).format("dd NNN yyyy HH:mm:ss");
            }

            function doUpload(progressPanel, el, params) {
                progressPanel.append(el);
                $.ajax({url: 'doUpload.php',
                        type: 'post',
                        async: true,
                        data: params,
                        success: function(data, status) {
                            el.fadeOut('slow', function() {
                                el.remove();
                            });
                            ++uploadSuccess;
                            uploadFinished();
                        },
                        error: function(xhr, status) {
                            el.addClass('ui-corner-all ui-state-error error-upload')
                                .html(el.attr('crdate')
                                      + " : "
                                      + xhr.statusText
                                      + " : "
                                      + el.attr('crurl'));
                            ++uploadFail;
                            uploadFinished();
                        }
                });
            }

            function uploadFinished() {
                var msgArgs = {s: uploadSuccess,
                        f: uploadFail,
                        t: urlsTotal};

                if (--urlsNotYetUploaded <= 0) {
                    var urlList = [];
                    var errorList = $('#error-panel ul');
                    $('#progress-panel .error-upload').each(function() {
                        urlList.push($(this).attr('crurl'));
                        // move element to error panel list
                        $(this).appendTo(errorList);
                    });
                    $('#url').val(urlList.join('\n'));
                    if (lastDate) {
                        $('#date').val(lastDate);
                    }
                    $('#upload-button').button('enable');
                    $('#clear-errors').button('enable');

                    $('#upload-status').html(
                        $.formatString('Upload of $t url(s) completed: $s success, $f failed',
                                       msgArgs));
                    if (uploadFail) {
                        $('#upload-error-status').html(
                            $.formatString('Last upload failed to transfer $f of $t url(s)',
                                           msgArgs));
                        $('#tabs').tabs('select', 1);
                    }
                } else {
                    $('#upload-status').html(
                        $.formatString('Uploading $t url(s): $s success, $f failed',
                                       msgArgs));
                }
            }

        </script>
    </head>
    <body>
        <noscript>
            <div class="ui-state-error">
                <a href="https://www.google.com/adsense/support/bin/answer.py?hl=en&amp;answer=12654">Javascript</a> is required to view this site.
            </div>
        </noscript>
        <?php include('inc/menu.php') ?>
        <h1>Upload multiple photo urls to queue at once</h1>

        <div class="ui-corner-all ui-state-error error-container" style="height: 1.4em">
            <ol>
                <li><label for="url" class="error">Please specify at least one url</label></li>
                <li><label for="date" class="error">Please specify the publish date</label></li>
            </ol>
        </div>

        <form id="multiqForm" method="post" action="">
            <fieldset id="photo-fields">
                <legend>Photo</legend>

                <label for="url">Urls (specify an url per line)</label>
                <br/>
                <textarea name="url" id="url" cols="100" rows="4"></textarea>
                <br/>

                <label for="caption">Caption</label>
                <br/>
                <input type="text" name="caption" id="caption" value=""/>
                <br/>
                <br/>

                <div style="overflow: hidden">
                    <div style="float:left; margin-right: 1em;">
                        <label for="date">Publish Date</label>
                        <br/>
                        <input type="text" name="date" id="date" value="" style="width:40em"/>
                    </div>

                    <div style="float:left">
                        <label for="timespan">Photo Time Span (in minutes)</label>
                        <br/>
                        <input type="text" name="timespan" id="timespan" value="2" style="width: 4em"/>
                    </div>
                </div>

                <label for="tags">Tags</label>
                <br/>
                <input type="text" name="tags" id="tags" value=""/>
                <br/>
            </fieldset>
            <div class="ui-dialog-buttonpane ui-helper-clearfix button-box">
                <input class="button" type="button" id="clear-fields" value="Clear Fields"/>
                <input class="button" type="submit" id="upload-button" value="Insert Photos"/>
            </div>
        </form>

        <br style="clear: both"/><br/>
        <div id="tabs">
            <ul>
                <li><a href="#tabs-1">Upload status</a></li>
                <li><a href="#tabs-2">Errors</a></li>
            </ul>
            <div id="tabs-1">
                <p id="upload-status">&nbsp;</p>
                <div id="progress-panel"><ul class="url-list"></ul></div>
            </div>
            <div id="tabs-2">
                <p id="upload-error-status">&nbsp;</p>
                <div id="error-panel"><ul class="url-list"></ul></div>
                <div class="ui-dialog-buttonpane ui-helper-clearfix button-box">
                    <input class="button" type="button" id="clear-errors" value="Clear Errors"/>
                </div>
                <br style="clear: both"/>
            </div>
        </div>

    <?php include('inc/footer.php'); ?>
    </body>
</html>
