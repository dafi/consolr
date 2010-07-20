/**
 * Author: davide ficano
 */

if  (typeof (itl) == 'undefined') {
    var itl = {};
}

(function() {
    this.copyLinks = function(onlyImages) {
        var windowManager = Components.classes['@mozilla.org/appshell/window-mediator;1']
            .getService(Components.interfaces.nsIWindowMediator);
        var links = [];
        var openWindow = windowManager.getMostRecentWindow("navigator:browser");
        try {
            var tc = openWindow.getBrowser().tabContainer.childNodes;
            for (var i = 0; i < tc.length; i++) {
                var tab = tc[i];
                var location = tab.linkedBrowser.contentWindow.location;

                if (onlyImages) {
                    var win = tab.linkedBrowser.contentWindow;
                    var doc = tab.linkedBrowser.contentDocument;

                    if (doc.contentType.indexOf("image/") == 0) {
                        links.push(location.href);
                    } else if (location.hostname.indexOf('imagevenue.com') >= 0) {
                        //location.reload(true);
                        links.push(doc.getElementById('thepic').src);
                    } else if (location.hostname.indexOf('imagebam.com') >= 0) {
                        var imgs = doc.getElementsByTagName('img');
                        for (var j = 0; j < imgs.length; j++) {
                            if (imgs[j].getAttribute('src').indexOf('dl.php') > 0) {
                                links.push(imgs[j].getAttribute('src'));
                                // get only the first
                                break;
                            }
                        }
                    } else if (location.hostname.indexOf('kosty555.pp.ru') >= 0) {
                        links.push(doc.getElementById('img_obj').src);
                    }                    
                } else {
                    links.push(location.href);
                }
            }
            Components.classes["@mozilla.org/widget/clipboardhelper;1"]
                .getService(Components.interfaces.nsIClipboardHelper)
                .copyString(links.join('\n'));
            Components.classes["@mozilla.org/alerts-service;1"]
                .getService(Components.interfaces.nsIAlertsService)
                .showAlertNotification(null, 'Image Tab Links', 'Copied ' + links.length + ' links');
        } catch (ex) {
            alert(ex);
        }
    }
}).apply(itl);