if (typeof(consolr) == "undefined") {
    var consolr = {};
}

if (typeof(consolr.tags) == "undefined") {
    consolr.groupDate = {};
}

(function() {
    var GROUP_DATE_FORMAT_STRING = "yyyy, EE dd MMM";
    var GROUP_DATE_TITLE = "$date ($postsCount posts)";

    var TEMPL_DATE_CONTAINER = '<div id="c$dateId" class="date-container ui-helper-clearfix ui-corner-all ui-widget-content"><h3 class="date-header ui-widget"><span id="t$dateId" class="title-group-date">$dateTitle</span></h3>'
                + '<ul id="$dateId" class="date-image-container">$items</ul></div>';
    var TEMPL_DATE_IMAGE_ITEM = '<li id="i$postId" class="date-image">'
                + '<img src="images/image_placeholder.gif" asrc="$imgSrc" alt="$imgAlt"/><div class="date-image-time">$time</div>'
                + '</li>';

    this.updatePostGroupDate = function(post, fromDate, destDate) {
        var groupDate = consolrPosts['group-date'];
        var fromGroupId = consolr.groupDate.createGroupDateId(fromDate);
        var fromGroupDate = groupDate[fromGroupId];

        // remove the post from group
        for (var i in fromGroupDate) {
            if (fromGroupDate[i].id === post.id) {
                fromGroupDate.splice(i, 1);
                break;
            }
        }

        var destGroupId = consolr.groupDate.createGroupDateId(destDate);
        var destGroupDate = groupDate[destGroupId];
        if (!destGroupDate) {
            destGroupDate = groupDate[destGroupId] = [];
        }
        // add into dest group, order is not important
        destGroupDate.push(post);
        post['group-date'] = destGroupId;
    },

    /**
     * Create the groupDateId used by DOM elements
     * @param {date} date the date to use to build the id
     * @returns {string} the id
     */
    this.createGroupDateId = function(date) {
        return "gd" + date.format('yyyyMMdd');
    },

    /**
     * Get the group date widget corresponding to passed date.
     * If the widget doesn't exist it is created and inserted at correct position
     * @param groupDateId the groupDateId
     * @param {Date} groupDate the date from which determine the widget
     * @returns the JQuery object
     */
    this.getGroupDateWidget = function(groupDateId, newDate) {
        var groupContainerId = "c" + groupDateId; // use the date container (ie div)
        var groupDateWidget = $("#" + groupContainerId);

        // this date group doesn't exists create it and insert at correct position
        if (groupDateWidget.length == 0) {
            var position;
            $('.date-container').each(function() {
                    if (this.id > groupContainerId) {
                        return false;
                    }
                    position = this;
                    return true;
                });

            var el = $($.formatString(TEMPL_DATE_CONTAINER, {
                        "dateTitle" : consolr.groupDate.formatGroupDateTitle(newDate),
                        "dateId" : groupDateId,
                        "items" : ""}));
            if (position) {
                el.insertAfter($(position));
            } else {
                // the new date group is the first
                el.insertBefore($('#date-container').children()[0]);
            }
            groupDateWidget = $("#" + groupDateId);
            groupDateWidget.initDraggableImage();
        }

        return groupDateWidget;
    },

    /**
     * Create a map(string, array) where key is the string date yyyyMMdd and
     * value is the array with posts.
     * Every post will contain a new property group-date
     * @param posts the posts array
     * @param dateProperty the date property to use to group dates
     * @returns the map(string, array)
     */
    this.groupPostsByDate = function(posts, dateProperty) {
        if (typeof(dateProperty) == "undefined") {
            dateProperty = 'consolr-date';
        }
        var grouped = {};

        $(posts).each(function(index, post) {
            // ignore hours, minutes and seconds
            var strTime = consolr.groupDate.createGroupDateId(post[dateProperty]);
            var g = grouped[strTime];

            if (!g) {
                g = [];
                grouped[strTime] = g;
            }
            post['group-date'] = strTime;
            g.push(post);
        });

        return grouped;
    },

    this.setGroupDateTitle = function(groupDate) {
        var groupDateId = consolr.groupDate.createGroupDateId(groupDate);
        var title = consolr.groupDate.formatGroupDateTitle(groupDate);

        $('#t' + groupDateId).html(title);
    }

    this.formatGroupDateTitle = function(groupDate) {
        var groupDateId = consolr.groupDate.createGroupDateId(groupDate);
        var posts = consolrPosts['group-date'][groupDateId];
        var postsCount = posts ? posts.length : 0;

        return $.formatString(GROUP_DATE_TITLE, {
                date: groupDate.format(GROUP_DATE_FORMAT_STRING),
                postsCount: postsCount
                });
    }

    this.getDateContainerHTML = function(settings) {
        var config = {
            dateProperty : 'consolr-date',
            sortByDateAsc : true
        }
        if (settings) {
            $.extend(config, settings);
        }

        var itemPatterns = {};
        var html = "";

        // ensure group dates are sorted in reverse order (from more recent to older)
        var sortedGroups = [];
        for (g in consolrPosts['group-date']) {
            sortedGroups.push(g);
        }
        var direction = config.sortByDateAsc ? 1 : -1;
        sortedGroups.sort(function(a, b) {
            return a === b ? 0 : a < b ? -direction : direction;
        });

        for (g in sortedGroups) {
            var dateId = sortedGroups[g];
            var posts = consolrPosts['group-date'][dateId];
            var itemsHtml = "";
            var date = posts[0][config.dateProperty];

            for (var i in posts) {
                var post = posts[i];
                var smallImgURL;
                if (post.type == 'photo') {
                    itemPatterns["imgSrc"] = consolr.getPhotoByWidth(post.photos, 75).url;
                } else {
                    itemPatterns["imgSrc"] = 'images/' + post.type + '.png';
                }

                itemPatterns["postId"] = post.id;
                itemPatterns["imgAlt"] = '';//$(post.caption).text(); // TODO post['slug']
                itemPatterns["time"] = post['consolr-date'].format('HH:mm:ss');
                itemsHtml += $.formatString(TEMPL_DATE_IMAGE_ITEM, itemPatterns);
            }
            html += $.formatString(TEMPL_DATE_CONTAINER, {
                            "dateTitle" : consolr.groupDate.formatGroupDateTitle(date),
                            "dateId" : dateId,
                            "items" : itemsHtml});
        };
        return html;
    }

    this.findPostIndex = function(group, postId) {
        var index = -1;
        $(group).each(function(i, post) {
            if (post.id == postId) {
                index = i;
                return false;
            }
            return true;
        });
        return index;
    }

    /**
     * replace all items on passed groupDate graphical widget with newPosts.
     * The image menu handle and tooltip are attached, too
     * @param groupDate the groupDate
     * @param newPosts the new posts
     */
    this.replaceGroupDateItems = function(groupDate, newPosts, dateProperty) {
        // images are visible so remove the asrc attribute
        var templ = TEMPL_DATE_IMAGE_ITEM
                        .replace(/src="(.*?)"/, '')
                        .replace('asrc', 'src');
        var list = $('#' + groupDate);

        var itemsHtml = "";
        var itemPatterns = {};
        var posts = consolrPosts['group-date'][groupDate];
        for (var i = 0; i < newPosts.length; i++) {
            var newPost = newPosts[i];

            posts[i] = newPost;
            itemPatterns["postId"] = newPost.id;
            itemPatterns["imgSrc"] = newPost['photo-url-75'];
            itemPatterns["imgAlt"] = newPost['slug'];
            itemPatterns["time"] = newPost[dateProperty].format('HH:mm:ss');
            itemsHtml += $.formatString(templ, itemPatterns);
        }

        list.empty().html(itemsHtml);
        $('.date-image', list)
            .initTooltipPhotoPost()
            .dblclick(function() {
                $('#dialog-form').dialog('option', 'postInfo', $(this));
                $('#dialog-form').dialog('open');
            })
            .initImageMenu({});
    }

    /**
     * Distribute the time on passed posts
     * @param posts the posts to adjust
     * @param startDate the date to set (hours, minutes and seconds are cleared)
     * @param fromMinutes the from time expressed in minutes
     * @param toMinutes the to time expressed in minutes
     * @param dateProperty the property to set on posts
     */
    this.timeDistribution = function(posts, startDate, fromMinutes, toMinutes, dateProperty) {
        startDate.setHours(0, 0, 0, 0);
        var len = posts.length;
        var startMS = startDate.getTime();
        var fromMS = fromMinutes * 60 * 1000;
        var deltaMS = ((toMinutes * 60 * 1000) - fromMS) / (len - 1);

        for (var i = 0; i < len; i++) {
            var newDate = new Date(startMS + (fromMS + i * deltaMS));
            newDate.setSeconds(0);
            posts[i][dateProperty] = newDate;
        }
    }

    this.shuffleDatePosts = function(posts, dateProperty) {
        var len = posts.length;
        var swap = function(a, b) {
            var t = posts[a][dateProperty];
            posts[a][dateProperty] = posts[b][dateProperty];
            posts[b][dateProperty] = t;
        }

        for (var i = 0; i < len; i++) {
            swap(i, Math.floor(Math.random() * len));
        }

        posts.sort(function(a, b) {
            return a[dateProperty] - b[dateProperty];
        });

        return posts;
    }
}).apply(consolr.groupDate);
