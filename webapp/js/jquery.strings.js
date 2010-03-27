(function($) {
    defaultEllipsis = "...";

    $.extend({
        cropText : function(str, maxLen, ellipsis) {
            if (str.length > maxLen) {
                if (typeof(ellipsis) == "undefined") {
                    ellipsis = defaultEllipsis;
                }
                var subLen = (maxLen - ellipsis.length) / 2;
                return str.substring(0, subLen) + ellipsis + str.substring(str.length - subLen);
            }
            return str;
        }
    });
})(jQuery);
