/**
 * Author: davide ficano
 */

if  (typeof (itl) == 'undefined') {
    var itl = {};
}

(function() {
    var defaultImgRules = [
        ['imagevenue\\.com', '#thepic'],
        ['imagebam\\.com', 'img[src*="dl.php"]'],
        ['imagebam\\.com', 'img[src*="download.php"]'],
        ['kosty555\\.pp\\.ru', '#img_obj'],
        ['turboimagehost\\.com', '#imageid'],
        ['picfoco\\.com', '#img'],
        ['u-.*\\.com|net$', '#redirectframe'],
        ['bruce-juice\\.com', '#thepic'],
        ['bruce-juice\\.com', '#main > div[align=center] img']
    ];

    function getImageUrl(doc, hostname, imgRules) {
        for (var i in imgRules) {
            var imgRule = imgRules[i];
            var reHostname = new RegExp(imgRule[0]);
            var img;

            if (reHostname.test(hostname) && (img = doc.querySelector(imgRule[1]))) {
                return img.src;
            }
        }
        return null;
    }

    function loadImgRules() {
        var prefBranch = Components.classes["@mozilla.org/preferences-service;1"]
                .getService(Components.interfaces.nsIPrefService)
                .getBranch("imagetablinks.");
        if (prefBranch.prefHasUserValue('imgRulesUrl')) {
            var url = prefBranch.getCharPref('imgRulesUrl');
            var httpReq = new XMLHttpRequest();
            httpReq.open("GET", url, false);
            httpReq.send(null);
            return JSON.parse(httpReq.responseText);
        } else {
            return defaultImgRules;
        }
    }

    this.copyLinks = function(onlyImages) {
        var windowManager = Components.classes['@mozilla.org/appshell/window-mediator;1']
            .getService(Components.interfaces.nsIWindowMediator);
        var links = [];
        var openWindow = windowManager.getMostRecentWindow("navigator:browser");

        try {
            var imgRules = loadImgRules();

            var tc = openWindow.getBrowser().tabContainer.childNodes;
            for (var i = 0; i < tc.length; i++) {
                var tab = tc[i];
                var location = tab.linkedBrowser.contentWindow.location;

                if (onlyImages) {
                    var win = tab.linkedBrowser.contentWindow;
                    var doc = tab.linkedBrowser.contentDocument;

                    if (doc.contentType.indexOf("image/") == 0) {
                        links.push(location.href);
                    } else {
                        var imgUrl = getImageUrl(doc, location.hostname, imgRules);
                        if (imgUrl) {
                            links.push(imgUrl);
                        }
                    }
                } else {
                    links.push(location.href);
                }
            }
            var textLinks = links.join('\n');
            //Components.classes["@mozilla.org/widget/clipboardhelper;1"]
            //    .getService(Components.interfaces.nsIClipboardHelper)
            //    .copyString(textLinks);
            sendMessageLinks(textLinks + '\n');
            Components.classes["@mozilla.org/alerts-service;1"]
                .getService(Components.interfaces.nsIAlertsService)
                .showAlertNotification(null, 'Image Tab Links', 'Copied ' + links.length + ' links');
        } catch (ex) {
            alert(ex);
        }
    }
    
    sendMessageLinks = function(textLinks) {
        var windowManager = Components.classes['@mozilla.org/appshell/window-mediator;1']
            .getService(Components.interfaces.nsIWindowMediator);
        var e = windowManager.getEnumerator("navigator:browser");
        while (e.hasMoreElements()) {
            var win = e.getNext().QueryInterface(Components.interfaces.nsIDOMWindow);
            var tc = win.getBrowser().tabContainer.childNodes;

            for (var i = 0; i < tc.length; i++) {
                var tab = tc[i];
                var contentDoc = tab.linkedBrowser.contentDocument;
                var contentWin = tab.linkedBrowser.contentWindow;
                
                contentWin.postMessage(textLinks, '*');
            }
        }
    }
}).apply(itl);