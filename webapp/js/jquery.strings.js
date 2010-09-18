(function($) {
    var defaultEllipsis = "...";

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

    $.fn.showMessageOnBlur = function(settings) {
        var config = {blurMessage: 'Type something',
                        blurClass: '',
                        focusClass: ''};

        if (settings) {
            $.extend(config, settings);
        }
        var onBlur = function() {
            // when page is reloaded the input field maintains its value so we
            // check it with blurMessage to apply blur style
            if ($.trim(this.value) == ''
                || this.value == config.blurMessage) {
                this.value = config.blurMessage;
                $(this).addClass(config.blurClass).removeClass(config.focusClass);
            }
        };
        this.blur($.proxy(onBlur, this[0]))
        .focus(function() {
            $(this).addClass(config.focusClass).removeClass(config.blurClass);
            if (this.value == config.blurMessage) {
                this.value = '';
            }
        });
        // don't trigger a real blur event but simply call initialization code
        $.proxy(onBlur, this[0])();

        var coreValFunction = this.val;
        this.val = function(value) {
            var text = $.proxy(coreValFunction, this)(value);
            if (text == config.blurMessage) {
                this.addClass(config.blurClass).removeClass(config.focusClass);
                return '';
            }
            this.addClass(config.focusClass).removeClass(config.blurClass);
            return text;
        }
        return this;
    }
})(jQuery);
