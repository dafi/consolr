(function($) {
    $.fn.initDialogModifyQueuePost = function() {
        this.dialog({
            autoOpen: false,
            width: 450,
            height: 330,
            modal: true,
            buttons: {
                'Save': function() {
                    var params = {
                        postId : parseInt($(this).dialog('option', 'postInfo').id.replace(/^[a-z]/i, ''), 10),
                        publishDate : $('#dialog-modify-publish-date').val(),
                        caption : $('#dialog-modify-caption').val(),
                        tags : $('#dialog-modify-tags').val()
                    };
                    consolr.updateImagePost(params);
                    consolr.updatePostsCount();
                    $(this).dialog('close');
                },
                Cancel: function() {
                    $(this).dialog('close');
                }
            },
            open: function() {
                var postInfo = $($(this).dialog('option', 'postInfo'));
                var post = consolr.findPost(postInfo.attr('id'));
                var tags = post['tags'] ? post['tags'].join(", ") : "";
                var date = formatDate(new Date(post['publish-unix-timestamp']), "dd NNN yyyy HH:mm:ss");

                $('#dialog-modify-caption').val(post['photo-caption']);
                $('#dialog-modify-publish-date').val(date);
                $('#dialog-modify-tags').val(tags);
                $('#dialog-modify-thumb').attr("src", post['photo-url-75']);

                $('#dialog-modify-caption').focus().select();
            },
            close: function() {
            }
        });
    };

    $.fn.initDialogTagsChart = function(settings) {
        var config = {drawCallback: consolr.drawTagsChart};

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
})(jQuery);
