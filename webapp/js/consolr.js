if (typeof(consolr) == "undefined") {
    var consolr = {};
}

(function() {
    var GROUP_DATE_FORMAT_STRING = "yyyy, EE dd MMM";
    var TEMPL_DATE_CONTAINER = '<h3 class="date-header ui-corner-top"><span>$dateTitle</span></h3>'
                + '<ul id="$dateId" class="date-image-container ui-corner-bottom">$items</ul>';
    var TEMPL_DATE_IMAGE_ITEM = '<li id="i$postId">'
                + '<img src="$imgSrc" alt="$imgAlt"/>'
                + '</li>';
    var POST_PER_REQUEST = 50;

    /**
     * Move image widget to new date container or position
     * @param imageId the image element id to move to new position
     * @param newDate the new date, it's used to determine the destination
     * container
     */
    this.moveImageWidget = function(imageId, newDate) {
        var time = newDate.getTime();
        var groupDateId = consolr.createGroupDateId(newDate);
        var posts = [];

        // remove imageId for posts
        $(consolrPosts['group-date'][groupDateId]).each(function(i, post) {
            if (post.id != imageId) {
                posts.push(post);
            }
        });

        var imageElement = $("#i" + imageId).detach();
        if (posts.length) {
            var index = consolr.findTimeClosestIndex(posts, time);
            var post = posts[index < 0 ? posts.length - 1 : index];
            if (index < 0) {
                imageElement.insertAfter($('#i' + post.id));
            } else {
                imageElement.insertBefore($('#i' + post.id));
            }
        } else {
            consolr.getGroupDateWidget(groupDateId, newDate).append(imageElement);
        }
    }

    this.refreshImagePosition = function(params) {
        consolr.movePost(params);
        consolr.moveImageWidget(params.postId, new Date(params.publishDate));
    }

    /**
     * Update queued post on server
     * @param params contains new post values. {postId,publishDate,caption,tags}
     * @param settings the settings to use, the success property if set points
     * to a function called on update success
     */
    this.updateQueuedPost = function(params, settings) {
        var config = {success: null, error: null};
        if (settings) {
            $.extend(config, settings);
        }

        $.ajax({url: 'doUpdate.php',
                type: 'post',
                async: false,
                data: params,
                success: function(data, status) {
                    if (typeof (config.success) == "function") config.success(params);
                    consolr.hideOperationProgressMessageText();
                },
                error: function(xhr, status) {
                    if (typeof (config.error) == "function") config.error(params);
                    consolr.showOperationProgressMessageText(xhr.statusText, true);
                },
                beforeSend: function(xhr) {
                    consolr.showOperationProgressMessageText("Update post...");
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
     * @param params contains new post values. {postId,publishDate,caption,tags}
     * @returns the post
     */
    this.movePost = function(params) {
        var posts = consolrPosts['posts'];
        var postId = params.postId;

        for (var i in posts) {
            var post = posts[i];

            if (post.id === postId) {
                var publishDate = new Date(params.publishDate);
                var currPostDate = new Date(post['publish-unix-timestamp']);

                post['tags'] = params.tags.replace(/,\s*/, ',').split(',');
                post['photo-caption'] = params.caption;
                post['publish-unix-timestamp'] = publishDate.getTime();

                posts.splice(i, 1);

                // move to ordered position
                var newIndex = consolr.findTimeClosestIndex(posts, publishDate.getTime());
                newIndex = newIndex < 0 ? posts.length : newIndex;
                posts.splice(newIndex, 0, post);

                consolr.updatePostGroupDate(post, currPostDate, publishDate);

                return post;
            }
        }
        return null;
    },

    this.updatePostGroupDate = function(post, fromDate, destDate) {
        var groupDate = consolrPosts['group-date'];
        var fromGroupId = consolr.createGroupDateId(fromDate);
        var fromGroupDate = groupDate[fromGroupId];

        // remove the post from group
        for (var i in fromGroupDate) {
            if (fromGroupDate[i].id === post.id) {
                fromGroupDate.splice(i, 1);
                break;
            }
        }

        var destGroupId = consolr.createGroupDateId(destDate);
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
     * Find the left closest post index by publish-unix-timestamp
     * @param posts array
     * @param ts timestamp used to find closest index
     * @returns the index of -1 if ts in greater tha all posts
     */
    this.findTimeClosestIndex = function(posts, ts) {
        for (var i = 0; i < posts.length; i++) {
            if (ts < posts[i]['publish-unix-timestamp']) {
                return i;
            }
        }
        return -1;
    }

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

            var el = $(this.formatString(TEMPL_DATE_CONTAINER, {
                        "dateTitle" : newDate.format(GROUP_DATE_FORMAT_STRING),
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

    this.updateMessagePanel = function() {
        var postsCount = getPostsCount();
        var emptyDays = getEmptyDays();

        var panelStr = '$postsCount posts in $postsDays days';

        var html = this.formatString(panelStr, {
            postsCount : postsCount.count,
            postsDays : postsCount.days
            });
        this.setMessageText(html);

        $('.empty-days').remove();
        $(emptyDays).each(function(i, item) {
            var str = item.dayCount + " day(s) without posts";
            $('<div class="empty-days ui-corner-all"><span>' + str + '</span></div>')
                .insertAfter($('#' + consolr.createGroupDateId(item.start)))
        })
    }

    getPostsCount = function() {
        var days = 0;
        for (g in consolrPosts['group-date']) {
            if (consolrPosts['group-date'][g].length > 0) ++days;
        };
        return {count : consolrPosts['posts'].length, days : days};
    }

    this.formatString = function(str, patterns) {
        var reStr = [];
        for (p in patterns) {
            reStr.push(p);
        };
        return str.replace(new RegExp("\\$(" + reStr.join("|") + ")", "g"), function(str, p1) {
                return patterns[p1];
            });
    }

    this.getDateContainerHTML = function(settings) {
        var config = {
            dateProperty : 'publish-on-time',
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
        var direction = config.sortByDateAsc ? -1 : 1;
        sortedGroups.sort(function(a, b) {
            return a === b ? 0 : a < b ? -direction : direction;
        });

        for (g in sortedGroups) {
            var dateId = sortedGroups[g];
            var posts = consolrPosts['group-date'][dateId];
            var itemsHtml = "";
            var time = new Date(posts[0][config.dateProperty]).format(GROUP_DATE_FORMAT_STRING);

            for (var i in posts) {
                var post = posts[i];

                itemPatterns["postId"] = post.id;
                itemPatterns["imgSrc"] = post['photo-url-75'];
                itemPatterns["imgAlt"] = post['slug'];
                itemsHtml += this.formatString(TEMPL_DATE_IMAGE_ITEM, itemPatterns);
            }
            html += this.formatString(TEMPL_DATE_CONTAINER, {
                            "dateTitle" : time,
                            "dateId" : dateId,
                            "items" : itemsHtml});
        };
        return html;
    }

    function fetchTumblr(url, settings) {
        $.ajax({url: url + '&start=' + settings.start,
                dataType: 'json',
                async: false,
                success: function(data, status) {
                        settings.posts = settings.posts.concat(data['posts']);
                        if (data['posts'].length == settings.num) {
                            if (typeof (settings.progress) == "function") {
                                settings.progress(data, settings.posts);
                            }

                            settings.start += settings.num;
                            fetchTumblr(url, settings);
                        } else {
                            if (typeof (settings.complete) == "function") {
                                settings.complete(settings.posts);
                            }
                        }
                },
                error: function(xhr, status) {
                    alert(xhr.statusText);
                }
            });
    }

    this.readPublicPhotoPosts = function(url, settings) {
        var config = {start : 0,
                    num : POST_PER_REQUEST,
                    posts : [],
                    progress : null,
                    complete : null,
                    type: 'photo'};

        if (settings) {
            $.extend(config, settings);
        }
        fetchTumblr(url + '?callback=?&type=' + config.type + '&num=' + config.num, config);
    }

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
            dateProperty = 'publish-on-time';
        }
        var grouped = {};

        $(posts).each(function(index, post) {
            // ignore hours, minutes and seconds
            var strTime = consolr.createGroupDateId(new Date(post[dateProperty]));
            var g = grouped[strTime];

            if (!g) {
                g = [];
                grouped[strTime] = g;
            }
            post['group-date'] = strTime;
            g.push(post);
        });

        return grouped;
    }

    this.setMessageText = function(str) {
        $("#message-text").html(str);
    }

    this.showOperationProgressMessageText = function(str, isError) {
        if (isError) {
            $('#operation-in-progress-panel')
                .removeClass('ui-state-highlight')
                .addClass('ui-state-error')
                .show();
            $('#operation-in-progress-icon-error')
                .bind('click.closeError', function() {
                    $(this).unbind('click.closeError');
                    consolr.hideOperationProgressMessageText();
                })
                .show();
        } else {
            $('#operation-in-progress-panel')
                .removeClass('ui-state-error')
                .addClass('ui-state-highlight')
                .show();
            $('#operation-in-progress-icon-error').hide();
        }
        $('#operation-in-progress-text').html(str);
    }
    
    this.hideOperationProgressMessageText = function() {
        $('#operation-in-progress-text').html('');
        $('#operation-in-progress-panel').hide();
    }

    /**
     * Return the time adjusted to be comprised between prevTime and nextTime
     * @param {date} prevTime the time before currTime, can be null
     * @param {date} nextTime the time after currTime, can be null
     * @param minutesAmount the minutes to add/subtract if prevTime
     * or nextTime are null, default is 10
     * @returns {date} the new object with adjusted time
     */
    this.adjustTime = function(prevTime, nextTime, minutesAmount) {
        minutesAmount = minutesAmount ? minutesAmount : 10;
        var adjustedTime;

        if (!prevTime) {
            adjustedTime = new Date(nextTime).add("m", -minutesAmount);
            if (!adjustedTime.equalsIgnoreTime(nextTime)) {
                adjustedTime = nextTime;
            }
        } else if (!nextTime) {
            adjustedTime = new Date(prevTime).add("m", minutesAmount);
            if (!adjustedTime.equalsIgnoreTime(prevTime)) {
                adjustedTime = prevTime;
            }
        } else {
            adjustedTime = new Date((prevTime.getTime() + nextTime.getTime()) / 2);
            adjustedTime.setSeconds(0);
        }

        return adjustedTime;
    }

    getEmptyDays = function() {
        var ts = [];
        for (var k in consolrPosts['group-date']) {
            if (consolrPosts['group-date'][k].length) {
                ts.push(new Date(parseInt(k.substring(2, 6), 10),
                                 parseInt(k.substring(6, 8), 10) - 1,
                                 parseInt(k.substring(8), 10)));
            }
        }
        ts.sort(function(a, b) {
            return a - b;
        });
        var oneDay = 1000 * 60 * 60 * 24;

        var emptyDays = [];
        for (var i = 0; i < ts.length - 1; i++) {
            var days = Math.ceil((ts[i + 1].getTime() - ts[i].getTime()) / oneDay) - 1;
            if (days > 0) {
                emptyDays.push({
                    start : ts[i],
                    end : ts[i + 1],
                    dayCount : days});
            }
        }
        return emptyDays;
    }
}).apply(consolr);
