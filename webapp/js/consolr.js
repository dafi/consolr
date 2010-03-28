if (typeof(consolr) == "undefined") {
    var consolr = {};
}

(function() {
    /**
     * Move image widget to new date container or position
     * @param imageId the image element id to move to new position
     * @param newDate the new date string, it's used to determine the destination
     * container
     */
    this.moveImageWidget = function(imageId, newDate) {
        var time = newDate.getTime();
        var groupDateId = consolr.createGroupDateId(newDate);
        imageId = "i" + imageId;

        // Move image to new position
        var element;
        var lastElement;
        $("#" + groupDateId + " li:not(#" + imageId + ")").each(function(index) {
            var iTime = consolr.findPost(this.id)['publish-unix-timestamp'];

            lastElement = this;
            if (time < iTime) {
                element = this;
                return false;
            }
            return true;
        });

        var imageElement = $("#" + imageId).detach();

        if (element) {
            imageElement.insertBefore(element);
        } else if (lastElement) {
            imageElement.insertAfter(lastElement);
        } else {
            consolr.getGroupDateWidget(groupDateId, newDate).append(imageElement);
        }
    }

    this.updateImagePost = function(params) {
        $.ajax({url: 'doUpdate.php',
                type: 'post',
                async: false,
                data: params,
                success: function(data, status) {
                    var newDate = new Date(params.publishDate);
                    var post = consolr.movePost(params.postId, newDate);
                    post['tags'] = params.tags.replace(/,\s*/, ',').split(',');
                    post['photo-caption'] = params.caption;
                    consolr.moveImageWidget(params.postId, newDate);
                },
                error: function(xhr, status) {
                    alert(xhr.statusText);
                }
            });
    },

    /**
     * Use the global consolrPosts to find a post by id
     * @param postId the postId used for search
     * @returns the post if found, null otherwise
     */
    this.findPost = function(postId) {
        // remove the alphabetic prefix
        if (typeof(postId) == "string") {
            postId = parseInt(postId.replace(/^[a-z]/i, ''), 10);
        }
        var arr = consolrPosts['posts'];

        for (var i in arr) {
            if (arr[i].id == postId) {
                return arr[i];
            }
        }
        return null;
    },

    /**
     * Move post to new sorted position, the post 'publish-unix-timestamp' property
     * is updated to new value
     * @param postId the post id
     * @param destDateStr the date string representing new post publish time
     * @returns the post
     */
    this.movePost = function(postId, destDateStr) {
        var destDate = new Date(destDateStr);
        var destGroupId = consolr.createGroupDateId(destDate);
        var fromGroupDate = consolrPosts['group-date'][consolr.findGroupDateByPostId(postId)];
        var destGroupDate = consolrPosts['group-date'][destGroupId];

        // remove from current group
        fromGroupDate.splice(fromGroupDate.indexOf(postId), 1);

        var posts = consolrPosts['posts'];
        // move to ordered position
        var newIndex = consolr.findTimestampIndex(posts, destDate.getTime());
        var post;
        var currIndex;
        for (var i in posts) {
            if (posts[i]['id'] === postId) {
                currIndex = i;
                post = posts[i];
                break;
            }
        }
        // update date/time info
        post['publish-unix-timestamp'] = destDate.getTime();

        if (!destGroupDate) {
            destGroupDate = consolrPosts['group-date'][destGroupId] = [];
        }
        // add into dest group, order is not important
        destGroupDate.push(post['id']);

        posts.splice(currIndex, 1);
        posts.splice(newIndex, 0, post);
        
        return post;
    },

    this.findGroupDateByPostId = function(postId) {
        postId = parseInt(postId);
        var groups = consolrPosts['group-date'];

        for (var i in groups) {
            if (groups[i].indexOf(postId) >= 0) {
                return i;
            }
        }
        return null;
    },

    this.createGroupDateId = function(date) {
        function pad(num) {
            return (num < 10 ? "0" : "") + num;
        }
        return "gd" + (1900 + date.getYear()) + pad(date.getMonth() + 1) + pad(date.getDate());
    },

    this.findTimestampIndex = function(arr, ts) {
        if (ts <= arr[0]['publish-unix-timestamp']) {
            return 0;
        }
        if (ts >= arr[arr.length - 1]['publish-unix-timestamp']) {
            return arr.length - 1;
        }
        for (var i = 1; i < arr.length - 2; i++) {
            if (ts < arr[i]['publish-unix-timestamp']) {
                return i;
            }
        }
        return -1;
    },

    /**
     * Get the group date widget relative to passed date.
     * If the widget doesn't exist it is created and inserted at correct position
     * @param {Date} groupDate the date from which determine the widget
     * @returns the JQuery object
     */
    this.getGroupDateWidget = function(groupDateId, newDate) {
        var groupDateWidget = $("#" + groupDateId);

        // this date group doesn't exists create it and insert at correct position
        if (groupDateWidget.length == 0) {
            var value = parseInt(groupDateId, 10);
            var position;
            $('#date-container ul').each(function() {
                    if (parseInt(this.id, 10) > value) {
                        return false;
                    }
                    position = this;
                    return true;
                });

            var longDate = formatDate(newDate, "yyyy, EE dd MMM");
            var el = $('<h3 class="date-header ui-corner-top"><span>' + longDate + '</span></h3><ul id="' + groupDateId + '" class="date-image-container">');
            if (position) {
                el.insertAfter($(position));
            } else {
                // the new date group is the first
                el.insertBefore($('#date-container').children()[0]);
            }
            groupDateWidget = $("#" + groupDateId);
        }
        
        return groupDateWidget;
    },
    
    this.groupTags = function() {
        var tagsMap = [];

        $(consolrPosts['posts']).each(function(i, post) {
            $(post.tags).each(function(i, tag) {
                tagsMap[tag] = tagsMap[tag] ? tagsMap[tag] + 1 : 1;
            });
        });
        var tags = [];
        for (i in tagsMap) {
            tags.push({name: i, count : tagsMap[i]});
        }
        return tags;
    }

    this.drawTagsChart = function() {
        var tags = consolr.groupTags();
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Tags');
        data.addColumn('number', 'Posts');

        $(tags).each(function(i, tag) {
            data.addRow([tag.name, tag.count]);
        });

        data.sort(0);
        new google.visualization.BarChart(
            document.getElementById('tags-chart')).draw(data, {legend : 'none'});
    }

    this.updatePostsCount = function() {
        var days = 0;
        for (g in consolrPosts['group-date']) {
            if (consolrPosts['group-date'][g].length > 0) ++days;
        };
        $("#count").text(consolrPosts['posts'].length +  ' posts in ' + days + ' days');
    }
}).apply(consolr);
