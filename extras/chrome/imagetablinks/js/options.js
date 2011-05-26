if (typeof(options) == 'undefined') {
    var options = {};
}

(function() {
    var imgRulesUrlWidget;

    this.init = function() {
        imgRulesUrlWidget = document.getElementById('imgRulesUrl');
        var imgRulesUrl = window.localStorage['imgRulesUrl'];
        imgRulesUrlWidget.value = imgRulesUrl ? imgRulesUrl : '';
        imgRulesUrlWidget.focus();
    }

    this.save = function() {
        var imgRulesUrl = imgRulesUrlWidget.value.replace(/^\s+/, '').replace(/\s+$/, '');

        if (imgRulesUrl) {
            window.localStorage['imgRulesUrl'] = imgRulesUrl;

            window.close();
        }
    }
}).apply(options);