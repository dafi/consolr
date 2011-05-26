chrome.extension.onRequest.addListener(function(request, sender, sendResponse) {
    if (request.method == "imageInfo") {
        var imgUrl = itl.getImageUrl(document, window.location.hostname, request.imgRules);
        sendResponse({imgUrl: imgUrl});
    } else if (request.method == "postMessage") {
        window.postMessage(request.textLinks, '*');
    } else {
        sendResponse({});
    }
});
