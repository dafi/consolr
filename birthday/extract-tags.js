// to use with
// http://www.consolr.netsons.org/api/tags/getTags.php
var s = "";
for (var i in tags) {
    var t = tags[i];
    for (j in t) {
        s += t[j].tag + "<br/>";
    }
}
$('body').append("<pre>" + s + "</pre>");