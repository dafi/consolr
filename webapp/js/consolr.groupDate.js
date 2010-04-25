if (typeof(consolr) == "undefined") {
    var consolr = {};
}

if (typeof(consolr.tags) == "undefined") {
    consolr.groupDate = {};
}

(function() {
    var GROUP_DATE_FORMAT_STRING = "yyyy, EE dd MMM";
    var GROUP_DATE_TITLE = "$date ($postsCount posts)";

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
        var groupDateWidget = $("#" + groupDateId);

        // this date group doesn't exists create it and insert at correct position
        if (groupDateWidget.length == 0) {
            var position;
            $('#date-container ul').each(function() {
                    if (this.id > groupDateId) {
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
}).apply(consolr.groupDate);
