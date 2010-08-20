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
        },

        formatString : function(str, patterns) {
            var reStr = [];
            for (p in patterns) {
                reStr.push(p);
            };
            return str.replace(new RegExp("\\$(" + reStr.join("|") + ")", "g"), function(str, p1) {
                    return patterns[p1];
                });
        },

        stripEmptyLines : function(str) {
            var trimmed = str
                .replace(/^\s*$/mg, '') // remove empty lines
                .replace(/[\r\n]+/g, '\n') // all carriage return/newline
                .replace(/\n$/, ''); // remove newline at end

            return trimmed.length ? trimmed.split('\n') : [];
        }
    });
})(jQuery);
