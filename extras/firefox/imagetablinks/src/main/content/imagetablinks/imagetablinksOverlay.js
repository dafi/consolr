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
                    } else if (location.hostname.indexOf('turboimagehost.com') >= 0) {
                        links.push(doc.getElementById('imageid').src);
                    } else if (location.hostname.indexOf('picfoco.com') >= 0) {
                        links.push(doc.getElementById('img').src);
                    } else if (/u-.*\.com|net$/.test(location.hostname)) {
                        location.href = doc.getElementById('redirectframe').src;
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