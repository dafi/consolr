if (typeof(consolr) == "undefined") {
    var consolr = {};
}

if (typeof(consolr.upload) == "undefined") {
    consolr.upload = {};
}

(function() {
    var urlsNotYetUploaded = 0;
    var urlsTotal = 0;
    var uploadSuccess = 0;
    var uploadFail = 0;
    var lastDate;

    var dateFormat = 'dd NNN yyyy HH:mm:ss';
    var tabErrorsLabel = "Errors";
    var tabErrorsWithCountLabel = "Errors ($c)";

    var msgUploadUrlsCompleted = 'Upload of $t url(s) completed: $s success, $f failed';
    var msgLastUploadFailed = 'Last upload failed to transfer $f of $t url(s)';
    var msgUploadingUrls = 'Uploading $t url(s): $s success, $f failed';
    var msgInvalidDateFormat = 'Invalid date format';
    var msgUploadingPhotoFrom = 'Uploading photo from $u';
    var msgNoErrors = "No errors";
    var msgTitle = 'Consolr - Photos Uploader';
    var msgTitleUploadProgress = 'Upload $c/$t';

    this.initUI = function() {
        $("#url").focus();
        $('.button').button();
        $("#tabs").tabs();

        initUpdateDateCheckbox();

        var container = $('.error-container');

        $('#uploadForm').validate({
            errorContainer: container,
            errorLabelContainer: $("ol", container),
            wrapper: 'li',
            rules: {
              url: "required",
              date: "required"
            },
            submitHandler: function(form) {
                try {
                    if ($('#state').val() == 'queue'
                        && isNaN(Date.parse($('#date').val()))) {
                        alert(msgInvalidDateFormat);
                    } else {
                        startUpload();
                    }
                } catch (e) {
                    alert(e);
                }
            }
        });

        $('#state').change(function() {
            if (this.value == 'queue') {
                $('#publish-date-box').show();
                $('#date').removeAttr('disabled');
            } else {
                $('#publish-date-box').hide();
                // disable so validator ignores it
                $('#date').attr('disabled', 'true');
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
            $('#tabs a[href="#tabs-2"] span').text(tabErrorsLabel);
            $('#upload-error-status').html(msgNoErrors);
        });

        $('#progress-panel, #error-panel').click(function(event) {
            if (event.target.hasAttribute('crdate')) {
                $('#url').val(event.target.getAttribute('crurl'));
                $('#date').val(event.target.getAttribute('crdate'));
            }
        });
    };

    function startUpload() {
        var urls = $.stripEmptyLines($('#url').val());
        if (!urls.length) {
            return;
        }
        urlsNotYetUploaded = urls.length;
        urlsTotal = urls.length;
        uploadSuccess = 0;
        uploadFail = 0;

        var msgArgs = {s: uploadSuccess,
                f: uploadFail,
                t: urlsTotal};
        $('#upload-status').html(
            $.formatString(msgUploadingUrls, msgArgs));

        // ensure tab status is visible
        $('#tabs').tabs('select', 0);

        $('#upload-button').button('disable');
        $('#clear-errors').button('disable');
        $('#progress-panel ul').empty();
        var progressPanel = $('#progress-panel ul');

        var params = {
            caption: $('#caption').val(),
            tags: $('#tags').val(),
            state: $('#state').val()
        };

        var timespanMS = parseInt($('#timespan').val(), 10);
        var timespanMS = (isNaN(timespanMS) ? 2 : Math.max(2, timespanMS)) * 1000 * 60;
        var millisecs = new Date($('#date').val()).getTime();
        for (var i in urls) {
            params.url = urls[i];
            params.date = new Date(millisecs).format(dateFormat);

            var el = $('<li>' + $.formatString(msgUploadingPhotoFrom, {u: params.url}) + '</li>');
            el.attr('crurl', params.url);
            el.attr('crdate', params.date);

            doUpload(progressPanel, el, params);
            millisecs += timespanMS;
        }
        // prepare with next date
        lastDate = new Date(millisecs).format(dateFormat);
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
            var errorsCount = errorList.children().length;
            var errorsLabel = tabErrorsLabel;
            if (errorsCount > 0) {
                errorsLabel = $.formatString(tabErrorsWithCountLabel,
                                                 {c: errorsCount});
            }
            $('#tabs a[href="#tabs-2"] span').text(errorsLabel);

            $('#url').val(urlList.join('\n'));
            var canUpdateDate = $('#updateDate').attr('checked');
            if (canUpdateDate && lastDate) {
                $('#date').val(lastDate);
            }
            $('#upload-button').button('enable');
            $('#clear-errors').button('enable');

            $('#upload-status').html(
                $.formatString(msgUploadUrlsCompleted, msgArgs));
            if (uploadFail) {
                $('#upload-error-status').html(
                    $.formatString(msgLastUploadFailed, msgArgs));
                $('#tabs').tabs('select', 1);
            }
            window.document.title = msgTitle;
        } else {
            $('#upload-status').html(
                $.formatString(msgUploadingUrls, msgArgs));
            window.document.title = $.formatString(msgTitleUploadProgress,
                        {c: uploadSuccess + uploadFail,
                        t: urlsTotal}) + " " + msgTitle;
        }
    }

    function initUpdateDateCheckbox() {
        $('#updateDate').click(function() {
            var value = $(this).attr('checked') ? 'y' : 'n';
            $.cookie('updateDate', value, {
                expires: 365}
            );
        });
        var updateDate = $.cookie('updateDate');
        // the default is checked
        if (!updateDate) {
            $('#updateDate').click();
        }
    }
}).apply(consolr.upload);
