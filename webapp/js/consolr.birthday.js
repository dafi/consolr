if (typeof(consolr.birthday) == "undefined") {
    consolr.birthday = {};
}

(function() {
    var TEMPL_DATE_CONTAINER = '<div id="c$dateId" class="date-container ui-helper-clearfix ui-corner-all ui-widget-content"><h3 class="date-header ui-widget"><span id="t$dateId" class="title-group-date">$dateTitle</span></h3>'
                + '<ul id="$dateId" class="date-image-container">$items</ul></div>';
    var TEMPL_DATE_IMAGE_ITEM = '<li id="i$postId" class="date-image"><img src="$imgSrc" alt="$imgAlt"/></li>';

    this.createHTML = function(name, posts) {
        var itemsHtml = "";
        var itemPatterns = {};

        for (var i in posts) {
            var post = posts[i];

            itemPatterns["postId"] = post.id;
            itemPatterns["imgSrc"] = consolr.getPhotoByWidth(post.photos, 75).url;
            itemPatterns["imgAlt"] = '';
            itemsHtml += $.formatString(TEMPL_DATE_IMAGE_ITEM, itemPatterns);
        }

        var html = $.formatString(TEMPL_DATE_CONTAINER, {
                        "dateTitle" : '',
                        "dateId" : 'birthday-' + name,
                        "items" : itemsHtml});
        return html;
    }
    
    this.createThumbHTML = function(birthInfo, settings) {
        var config = {thumbPerLine:2};

        if (settings) {
            $.extend(config, settings);
        }

        var html = '';

        for (var i = 0; i < birthInfo.length; i++) {
            var bi = birthInfo[i];
            html += '<div class="thumb" data-index="' + i + '">';
            html += '<a class="thumb-title" href="javascript:void(0);">Change Image (' + bi.published_posts.length + ')</a>';
            html += '<a target="_blank" href="' + bi.post_url + '"><img width="250" height="250" src="' + bi.image_url + '"></img></a>';
            html += '</div>';
            if (((i + 1) % config.thumbPerLine) == 0) {
                html += "<br/>";
            }
        }
        return html;
    }
    
    /**
     * @param birthinfo {array} object containing name, post_id
     */
    this.publish = function(birthInfo) {
        var names = [];
        var postIds = [];
        
        for (var i = 0; i < birthInfo.length; i++) {
            var bi = birthInfo[i];
            names.push(bi.name);
            postIds.push(bi.post_id);
        }
        consolr.showOperationProgressMessageText('Publishing birthday(s)...');
        $.ajax({
            url: "doPublishBirth.php",
            type: 'post',
            data: {
                names: names.join(','),
                post_ids: postIds.join(',')
            },
            success: function(timestamps) {
                consolr.hideOperationProgressMessageText();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                consolr.showOperationProgressMessageText('Error ' + textStatus, true);
            },
            dataType: 'json',
            async: true
        });
    }
}).apply(consolr.birthday);

(function($) {
    $.fn.initSelectImageDialog = function(settings) {
        this.dialog({
            autoOpen: false,
            width: $(window).width() / 3,
            height: 360,
            modal: true,
            resize: "auto",
            buttons: {
                Cancel: function() {
                    $(this).dialog('close');
                }
            },
            open: function() {
                $('#image-progress').progressbar({value:0}).show();

                var selectImageInfo = $(this).dialog('option', 'selectImageInfo');
                var link = $(this).dialog('option', 'link');
                var img = $(this).dialog('option', 'img');
                var dialog = $(this);
                dialog.dialog('option', 'title', 'Select Image: ' + selectImageInfo.name);

                $('#date-container').empty();
                consolr.readPublicPhotoPosts(apiUrl, {
                    start : 0,
                    posts : [],
                    tags : selectImageInfo.name,
                    postsToGet: null,
                    progress : function(data, posts) {
                        var perc = posts.length * 100 / data.total_posts;
                        $('#image-progress').progressbar({value:perc});
                    },
                    complete : function(posts) {
                        $('#image-progress').hide();

                        consolrPosts.posts = posts;
                        $('#date-container').html(consolr.birthday.createHTML(selectImageInfo.name, posts));
                        $('.date-image').click(function() {
                            var index = consolr.findPostIndex(this.id);
                            var post = consolrPosts.posts[index];

                            selectImageInfo.post_url = post.url;
                            selectImageInfo.post_id = post.id;
                            selectImageInfo.image_url = consolr.getPhotoByWidth(post.photos, 250).url;

                            link.attr('href', selectImageInfo.post_url);
                            img.attr('src', selectImageInfo.image_url);

                            dialog.dialog('close');
                        });
                    }
                });
            },
            close: function() {
            }
        });
        return this;
    };

    $.fn.hoverOnImage = function(settings) {
        this.hover(function() {
                var thumbInfo = $('#thumb-info');
                var el = $(this);
                var pos = el.offset();

                pos.top += el.height() - thumbInfo.height();
                thumbInfo.css('left', pos.left + 'px');
                thumbInfo.css('top', pos.top + 'px');
                thumbInfo.width(el.width());

                var thumb = el.parents('.thumb');
                var index = thumb.attr('data-index');
                var name = birthInfo[index].name;

                thumbInfo.children('span').html(name);
            },
            function() {
                var thumbInfo = $('#thumb-info');
                thumbInfo.offset({left:-9999});
            });
        return this;
    };

})(jQuery);
