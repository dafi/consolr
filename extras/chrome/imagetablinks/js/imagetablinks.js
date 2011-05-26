/**
 * Author: davide ficano
 */

if  (typeof (itl) == 'undefined') {
    var itl = {};
}

(function() {
    this.getImageUrl = function(doc, hostname, imgRules) {
        // little hack to get image content document
        if (doc.body && doc.body.childElementCount == 1 && doc.body.firstChild.localName == 'img') {
            return doc.body.firstChild.src;
        }
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
        var url = localStorage['imgRulesUrl'];
        if (url) {
            var httpReq = new XMLHttpRequest();
            httpReq.open("GET", url, false);
            httpReq.send(null);
            return JSON.parse(httpReq.responseText);
        } else {
            return [];
        }
    }

    this.copyLinks = function(onlyImages) {
        chrome.tabs.getAllInWindow(null, function (tabs) {
            var links = [];

            var imgRules = loadImgRules();
            var pendingRequests = tabs.length;
            for (var i = 0; i < tabs.length; i++) {
                var tab = tabs[i];
                if (tab.url.indexOf('chrome://') == 0) {
                    --pendingRequests;
                    continue;
                }
                chrome.tabs.sendRequest(tab.id, {method: "imageInfo", imgRules: imgRules}, function(response) {
                    --pendingRequests;
                    if (response.imgUrl) {
                        links.push(response.imgUrl);
                    }

                    // Wait all requests are processed before send message
                    if (!pendingRequests) {
                        var textLinks = links.join('\n');
                        sendMessageLinks(textLinks + '\n');
                        showNotifications(links);
                    }
                });
            }
        });
    }

    sendMessageLinks = function(textLinks) {
        chrome.windows.getAll({populate: true}, function(windows) {
            for (var i = 0; i < windows.length; i++) {
                var tabs = windows[i].tabs;

                for (var j = 0; j < tabs.length; j++) {
                    var tab = tabs[j];
                    chrome.tabs.sendRequest(tab.id, {method: "postMessage", textLinks: textLinks});
                }
            }
        });
    }

    showNotifications = function(links) {
        var notification = webkitNotifications.createNotification(
            'images/tb32.png',
            'Image Tab Links',
            'Copied ' + links.length + ' links');
        notification.ondisplay = function(event) {
            setTimeout(function() {event.target.cancel();}, 5000);
        };
        notification.show();
    }
}).apply(itl);
