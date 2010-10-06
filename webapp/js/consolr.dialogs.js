(function($) {
    var INVALID_DATE = 'Date format is invalid';

    $.fn.initDialogModifyQueuePost = function(settings) {
        var config = {isPublishDateEditAllowed: true};

        if (settings) {
            $.extend(config, settings);
        }
        if (config.isPublishDateEditAllowed) {
            $('#dialog-modify-publish-date').show();
            $('label[for="dialog-modify-publish-date"]').show();
        } else {
            $('#dialog-modify-publish-date').hide();
            $('label[for="dialog-modify-publish-date"]').hide();
        }
        this.dialog({
            autoOpen: false,
            width: 450,
            height: 330,
            modal: true,
            resize: "auto",
            buttons: {
                'Save': function() {
                    var params = {
                        postId : parseInt($(this).dialog('option', 'postInfo').attr('id').replace(/^[a-z]/i, ''), 10),
                        caption : tinyMCE.get('dialog-modify-caption').getContent(),
                        clickThroughLink : $('#dialog-modify-click-through-link').val(),
                        tags : $('#dialog-modify-tags').val()
                    };
                    if (config.isPublishDateEditAllowed) {
                        params.publishDate = $('#dialog-modify-publish-date').val();
                        if (isNaN(Date.parse(params.publishDate))) {
                            alert(INVALID_DATE);
                            return;
                        }
                    } else {
                        // the post to update is in publish state
                        params.state = 'p';
                    }
                    consolr.updateQueuedPost(params, {
                            success: function(params) {
                                // publishDate can be not set if isPublishDateEditAllowed is false
                                params.publishDate = $('#dialog-modify-publish-date').val();
                                consolr.refreshImagePosition(params, true);
                                }
                            });
                    consolr.updateMessagePanel();
                    $(this).dialog('close');
                },
                Cancel: function() {
                    $(this).dialog('close');
                }
            },
            open: function() {
                tinyMCE.execCommand('mceAddControl', false, 'dialog-modify-caption');

                var postInfo = $(this).dialog('option', 'postInfo');
                var post = consolr.findPost(postInfo.attr('id'));
                var tags = post['tags'] ? post['tags'].join(", ") : "";
                var clickThroughLink = post['photo-link-url'] ? post['photo-link-url'] : "";
                var date = post['consolr-date'].format("dd NNN yyyy HH:mm:ss");

                $('#dialog-modify-caption').val(post['photo-caption']);
                $('#dialog-modify-publish-date').val(date);
                $('#dialog-modify-click-through-link').val(clickThroughLink);
                $('#dialog-modify-tags').val(tags);
                $('#dialog-form fieldset').css('background-image', 'url("' + post['photo-url-75'] + '")');

                $('#dialog-modify-caption').focus().select();
            },
            close: function() {
                tinyMCE.execCommand('mceRemoveControl', false, 'dialog-modify-caption');
            }
        });
    };

    $.fn.initDialogTagsChart = function(settings) {
        var config = {drawCallback: consolr.tags.drawTagsChart};

        if (settings) {
            $.extend(config, settings);
        }
        this.dialog({
            autoOpen: false,
            width: 500,
            height: 450,
            modal: true,
            resizable: false,
            buttons: {
                'Close': function() {
                    $(this).dialog('close');
                }
            },
            open: function() {
                google.load("visualization", "1", {
                    packages:["barchart"],
                    callback: config.drawCallback});
            }
        });
    };

    $.fn.initDialogFilterTags = function() {
        var TEMPL_CHECKBOX_TAG = "<div id='tag$index'><input type='checkbox' value='$index'>$tag</div>";

        this.dialog({
            autoOpen: false,
            width: 300,
            height: 250,
            modal: true,
            resizable: true,
            buttons: {
                'Filter': function() {
                    var selectedTags = [];
                    var tags = $(this).dialog('option', 'tags');

                    $("#tagList input:checked").each(function(i, tag) {
                        var tagIndex = parseInt($(tag).val(), 10);
                        selectedTags.push(tags[tagIndex]);
                    })
                    $(this).dialog('close');

                    var onOkCallback = $(this).dialog('option', 'onOk');
                    if (typeof(onOkCallback) == "function") {
                        onOkCallback(selectedTags);
                    }
                },
                Cancel : function() {
                    $(this).dialog('close');
                }
            },
            open: function() {
                var html = "";
                var tags = $(this).dialog('option', 'tags');
                $(tags).each(function(i, tag) {
                    html += $.formatString(TEMPL_CHECKBOX_TAG, {
                        index : i,
                        tag: tag.name + ' (' + tag.count + ')'});
                });
                // before remove all children that append new content
                $('#tagList').empty().append(html);
                $('#search').val('').focus();
            }
        });
        var dialog = this;
        $('#search').keyup(function() {
            var pattern = $(this).val().toLowerCase();
            var tags = $(dialog).dialog('option', 'tags');
            $(tags).each(function(i, tag) {
                if (tag.name.toLowerCase().indexOf(pattern) >= 0) {
                    $("div#tag" + i).show();
                } else {
                    $("div#tag" + i).hide();
                    //$("div#tag" + i + " input").attr('checked', false);
                }
            });
        })
    };

    $.fn.initDialogTimeDistribution = function(settings) {
        var config = {};

        if (settings) {
            $.extend(config, settings);
        }

        // Initialized out of open method to preserve values
        // between successive open dialog calls
        $('#time-range').slider({
            range: true,
            min: 0,
            max: 24,
            values: [0, 24],
            slide: function(event, ui) {
                $('#time-value').html('From ' + ui.values[0] + ' to ' + ui.values[1]);
            }
        });

        this.dialog({
            autoOpen: false,
            width: 400,
            height: 250,
            modal: true,
            resizable: true,
            buttons: {
                'Save': function() {
                    // compute the distribution
                    var groupDate = $(this).dialog('option', 'groupDate');
                    var fromMinutes = $("#time-range").slider("values", 0) * 60;
                    var toMinutes = $("#time-range").slider("values", 1);
                    var dateProperty = 'consolr-date';
                    var posts = consolrPosts['group-date'][groupDate];
                    posts = posts.slice(0, posts.length);
                    var startDate = posts[0][dateProperty];

                    // 24h means 23h59m
                    if (toMinutes == 24) {
                        toMinutes = --toMinutes * 60 + 59;
                    } else {
                        toMinutes *= 60;
                    }
                    consolr.groupDate.timeDistribution(posts, startDate, fromMinutes, toMinutes, dateProperty);
                    consolr.groupDate.replaceGroupDateItems(groupDate, posts, dateProperty);

                    // disable buttons while the update is in progress
                    $(this).dialog('option', 'closeOnEscape', false);
                    $('.ui-dialog-buttonpane button', $(this).dialog('widget'))
                        .attr('disabled', 'true')
                        .addClass('ui-state-disabled');

                    // start update on server
                    $('#time-progress-container').show();
                    var completed = 0;
                    var onComplete = function(params) {
                        if (++completed == posts.length) {
                            $('#dialog-time-distribution').dialog('close');
                        }
                        $('#time-progressbar').progressbar({
                            value: completed * 100 / posts.length
                        });
                    };

                    for (var i = 0; i < posts.length; i++) {
                        var post = posts[i];
                        var params = {
                            postId : post.id,
                            caption : post['photo-caption'],
                            clickThroughLink : post['photo-link-url'] ? post['photo-link-url'] : '',
                            tags : post['tags'] ? post['tags'].join(', ') : '',
                            publishDate : post[dateProperty].format('dd NNN yyyy HH:mm:ss')
                        };
                        consolr.updateQueuedPost(params, {
                                success: onComplete,
                                async: true
                        });
                    }
                },
                'Cancel': function() {
                    $(this).dialog('close');
                }
            },
            open: function() {
                var values = $('#time-range').slider('values');
                $('#time-value').html('From ' + values[0] + ' to ' + values[1]);
                $('#time-progress-container').hide();
                $(this).dialog('option', 'closeOnEscape', true);
                $('.ui-dialog-buttonpane button', $(this).dialog('widget'))
                    .removeAttr('disabled')
                    .removeClass('ui-state-disabled');
            }
        });
        return this;
    };
})(jQuery);
