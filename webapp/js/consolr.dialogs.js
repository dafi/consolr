(function($) {
    var INVALID_DATE = 'Date format is invalid';

    $.fn.initDialogModifyQueuePost = function() {
        this.dialog({
            autoOpen: false,
            width: 450,
            height: 330,
            modal: true,
            resize: "auto",
            buttons: {
                'Save': function() {
                    var params = {
                        postId : parseInt($(this).dialog('option', 'postInfo').id.replace(/^[a-z]/i, ''), 10),
                        publishDate : $('#dialog-modify-publish-date').val(),
                        caption : tinyMCE.get('dialog-modify-caption').getContent(),
                        clickThroughLink : $('#dialog-modify-click-through-link').val(),
                        tags : $('#dialog-modify-tags').val()
                    };
                    if (isNaN(Date.parse(params.publishDate))) {
                        alert(INVALID_DATE);
                        return;
                    }
                    consolr.updateQueuedPost(params, {
                            success: function(params) {
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

                var postInfo = $($(this).dialog('option', 'postInfo'));
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
})(jQuery);
