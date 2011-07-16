<?php
require_once 'lib/loginUtils.php';
require_once 'lib/tumblr/tumblrUtils.php';
require_once 'inc/dbconfig.php';
require_once 'lib/db.php';

$tumblr = login_utils::get_tumblr();

function read_starred($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    $res = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $res;
}

function extract_info($tumblr) {
    $url = consolr_db::get_single_setting($tumblr->get_tumblr_name(), 'greader');
    if (!$url) {
        exit(1);
    }
    $res = read_starred($url);
    $doc = new DOMDocument('1.0', 'UTF-8');
    $arr = array();
    
    if ($doc->loadXML($res)) {
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('f', "http://www.w3.org/2005/Atom");
        
        $query = '//f:entry';
        $entries = $xpath->query($query);

        foreach ($entries as $e) {
            $ts = $e->attributes->getNamedItem('crawl-timestamp-msec')->nodeValue;
            $title = $xpath->query('f:title', $e)->item(0)->nodeValue;
            $link = $xpath->query('f:link', $e)->item(0)->attributes->getNamedItem('href')->nodeValue;
            array_push($arr,  array('title' => $title, 'link' => $link, 'ts' => strftime('%Y-%m-%d', $ts / 1000)));
        }
    }
    return $arr;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
        <title>Consolr - Google Reader Starred Items</title>

        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico"/>

        <link type="text/css" href="css/consolr.css" rel="stylesheet"/>
        <link type="text/css" href="css/dialogs.css" rel="stylesheet"/>
        <link type="text/css" href="css/consolr/jquery-ui.css" rel="stylesheet" />
        <link type="text/css" href="css/contextMenus.css" rel="stylesheet"/>
        
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui.js"></script>
        <script type="text/javascript" src="js/jquery.tooltip.min.js"></script>
        <script type="text/javascript" src="js/jquery.strings.js"></script>
        <script type="text/javascript" src="js/jquery.contextMenu.js"></script>

        <script type="text/javascript" src="js/date.js"></script>
        <script type="text/javascript" src="js/consolr.groupDate.js"></script>
        <script type="text/javascript" src="js/consolr.js"></script>
        <script type="text/javascript" src="js/consolr.tags.js"></script>
        <script type="text/javascript" src="js/consolr.dialogs.js"></script>
        <script type="text/javascript" src="js/consolr.initializers.js"></script>

    <style>
    p {
        font-size: 16px;
        font-weight: bold;
    }
    .main-container a {
        font-size: 14px;
    }
    
    .links-container {
        color: #000;
        background-color: #fff;
        display: none;
    }
    
    .links-container a {
        color: #000;
        margin-left: 5em;
        text-decoration: underline;
    }

    .separator-container {
        margin-top: 20px;
    }
    
    .separator-container span {
        font-size: 1.4em;
        font-weight: bold;
    }
    </style>
    <script type="text/javascript">
    <?php
        echo 'var starred = ' . json_encode(extract_info($tumblr)) . ';';
    ?>
    /**
     * @param sortFlag Title, Time or Count
     * @returns number of items
     */
    function appendItems(sortFlag) {
        var sortByTitle = sortFlag == 'Title' || sortFlag == 'Count';
        if (sortByTitle) {
            starred.sort(function(a, b) {
                return a.title.toLowerCase().localeCompare(b.title.toLowerCase());
            });
        } else if (sortFlag == 'Time') {
            starred.sort(function(a, b) {
                return -a.ts.localeCompare(b.ts);
            });
        }

        var map = createTagMapFromTitles(starred, sortByTitle);

        var info = sortFlag == 'Count'
            ? buildHTMLByCount(getInfoPerCount(map))
            : buildHTMLByTitle(map, sortByTitle);

        $('.main-container')
            .empty()
            .append(info.html);
        $('.item-links').click(function() {
           $(this).next().toggle();
        });
        return info.tagsCount;
    }
    $(function() {
        $("#toolbar button, input[type=submit]").button();
        $("#sort-by-title").click(function() {
            appendItems('Title');
        });
        $("#sort-by-time").click(function() {
            appendItems('Time');
        });
        $("#sort-by-count").click(function() {
            appendItems('Count');
        });

        var tagsCount = appendItems('Title');
        $('.items-count').append("<p>Items found: " + starred.length + " (tags " + tagsCount + ")</p>");
    });

    function createTagMapFromTitles(starred, sortByTitle) {
        var titleRE = /^(.*?)\s([-\u2013|~@]|attends|arrives|signs)/;
        var map = {};

        for (var i in starred) {
            var item = starred[i];
            var title = sortByTitle ? item.title : item.ts;
            var m = title.match(titleRE);
            if (m && m[1]) {
                title = m[1];
            }
            var tagArr = map[title];
            if (tagArr == undefined) {
                tagArr = [];
                map[title] = tagArr;
            }
            tagArr.push(item);
        }
        
        return map;
    }

    /**
     * returns {sortedKeys, values} sortedKeys is an array containing
     * the keys e.g. the posts count per single tag. values contains the tags
     */
    function getInfoPerCount(map) {
        var mapCount = {};
        var sortedKeys = [];
        for (var i in map) {
            var c = map[i].length;
            var tagArr = mapCount[c];
            if (tagArr == undefined) {
                tagArr = [];
                mapCount[c] = tagArr;
                sortedKeys.push(c);
            }
            tagArr.push(i);
        }
        sortedKeys.sort(function(a,b) {
            return b - a; // desc order
        });
        
        return {sortedKeys: sortedKeys, values: mapCount};
    }

    function buildHTMLByCount(map) {
        var arr = [];
        var tagsCount = 0;

        for (var i = 0; i < map.sortedKeys.length; i++) {
            var key = map.sortedKeys[i];
            var tagNames = map.values[key];
            var title = key;

            var div = '<div class="links-container">';
            for (var l = 0; l < tagNames.length; l++) {
                var item = tagNames[l];
                var tagUrl = "http://www.google.it/reader/view/user%2F-%2Flabel%2F" + encodeURIComponent(item);
                div += '<a target="_blank" href="' + tagUrl + '">' + item + '</a>';
                div += "<br/>";
            }
            div += "</div>";
            
            var html = '';
            arr.push('<div class="separator-container"><span>' + title + '</span><hr/>');
            arr.push('<a class="item-links" href="javascript:void(0)">Item count &nbsp(' + tagNames.length + ')</a>'
                     + div
                     + '<br/>');
            ++tagsCount;
            arr.push('</div>');
        }
        
        return {html: arr.join(''), tagsCount: tagsCount};
    }
    
    function buildHTMLByTitle(map, sortByTitle) {
        var arr = [];
        var tagsCount = 0;
        var separator = '';
        for (var i in map) {
            var items = map[i];
            var title = i;
            var tagUrl = "http://www.google.it/reader/view/user%2F-%2Flabel%2F" + encodeURIComponent(title);

            var div = '<div class="links-container">';
            for (var l in items) {
                div += items[l].ts + '&nbsp;';
                div += '<a target="_blank" href="' + items[l].link + '">' + items[l].title + '</a>';
                div += "<br/>";
            }
            div += "</div>";
            
            var firstLetter = sortByTitle ? title[0].toUpperCase() : title;
            var html = '';
            if (separator != firstLetter) {
                arr.push('<div class="separator-container"><span>' + firstLetter + '</span><hr/>');
                separator = firstLetter;
            }
            arr.push('<a target="_blank" href="' + tagUrl + '">' + title + "</a>"
                     + '<a class="item-links" href="javascript:void(0)">&nbsp(' + items.length + ')</a>'
                     + div
                     + '<br/>');
            ++tagsCount;
            arr.push('</div>');
        }
        
        return {html: arr.join(''), tagsCount: tagsCount};
    }
    </script>
</head>
    <body>
        <noscript>
            <div class="ui-state-error">
                <a href="https://www.google.com/adsense/support/bin/answer.py?hl=en&amp;answer=12654">Javascript</a> is required to view this site.
            </div>
        </noscript>
        <?php if (login_utils::is_logged()) include('inc/menu.php') ?>

        <div id="toolbar" class="toolbar ui-widget-header ui-corner-all">
            <button id="sort-by-title">Sort by Title</button>
            <button id="sort-by-time">Sort by Time</button>
            <button id="sort-by-count">Sort by Count</button>
        </div>
        <div class="items-count">
        </div>
        <div class="main-container">
        </div>
    <?php include('inc/footer.php'); ?>
    </body>
</html>