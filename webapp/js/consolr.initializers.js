(function($) {
    $.fn.initTooltipPhotoPost = function(settings) {
        var config = {datePropName: 'publish-unix-timestamp',
                        captionMaxChars : 60,
                        tagsMaxChars : 60,
                        dateFormat: "HH:mm:ss"};

        if (settings) {
            $.extend(config, settings);
        }
        this.tooltip({
            bodyHandler: function() {
                var post = consolr.findPost(this.id);
                var caption = $(post['photo-caption']).text();
                // If text() returns an empty string uses the caption
                caption = $.cropText(caption || post['photo-caption'], config.captionMaxChars);

                var tags = post['tags'] ? $.cropText(post['tags'].join(", "), config.tagsMaxChars) : "";
                var time = new Date(post[config.datePropName]).format(config.dateFormat);

                return $("<div>"
                         + "<span class='tooltip-caption'>" + caption + "</span>"
                         + "<span class='tooltip-tags'>" + tags + "</span>"
                         + "<span class='tooltip-time'>" + time + "</span>"
                         + "</div>").html();
            },
            showURL: false
        });
    };

    $.fn.initDraggableImage = function(settings) {
        var config = {datePropName: 'publish-unix-timestamp',
                        connectWith: '.date-image-container',
                        placeholder: 'date-image-drop-placeholder ui-state-highlight',
                        minutesAmount : 10};

        if (settings) {
            $.extend(config, settings);
        }

        this.sortable({
            connectWith: config.connectWith,
            placeholder: config.placeholder,
            forcePlaceholderSize : true,
            stop: function(event, ui) {
                var prevTime = null;
                var nextTime = null;
                var post = consolr.findPost(ui.item.get(0).id);
                var currTime = new Date(post[config.datePropName]);

                if (ui.item.prev().length) {
                    prevTime = new Date(consolr.findPost(ui.item.prev()
                                            .get(0).id)[config.datePropName]);
                }
                if (ui.item.next().length) {
                    nextTime = new Date(consolr.findPost(ui.item.next()
                                            .get(0).id)[config.datePropName]);
                }

                var newDate = consolr.adjustTime(currTime,
                                        prevTime,
                                        nextTime,
                                        config.minutesAmount);
                post[config.datePropName] = newDate.getTime();

                var params = {
                    postId : post['id'],
                    publishDate : newDate.format("dd NNN yyyy HH:mm:ss"),
                    caption : post['photo-caption'],
                    tags : post['tags'] ? post['tags'].join(", ") : ""
                };
                consolr.updateQueuedPost(params);
            }
        }).disableSelection();
    };

    $.extend({
        initEditor : function() {
            tinyMCE.init({
                mode : "none",
                theme : "advanced",
                convert_newlines_to_brs : false,
                remove_trailing_nbsp : true,
                theme_advanced_toolbar_location : "top",
                theme_advanced_buttons1 :
                    "bold,italic,separator,bullist,numlist,separator,link,unlink,code",
                theme_advanced_buttons2 : "",
                theme_advanced_buttons3 : "",
                width: '100%'
            });
        }
    });
})(jQuery);