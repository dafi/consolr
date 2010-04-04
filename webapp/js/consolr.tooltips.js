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
                var time = formatDate(new Date(post[config.datePropName]), config.dateFormat);

                return $("<div>"
                         + "<span class='tooltip-caption'>" + caption + "</span>"
                         + "<span class='tooltip-tags'>" + tags + "</span>"
                         + "<span class='tooltip-time'>" + time + "</span>"
                         + "</div>").html();
            },
            showURL: false
        });
    };
})(jQuery);
