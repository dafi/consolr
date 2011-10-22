if (typeof(consolr) == "undefined") {
    var consolr = {};
}

(function() {
    var POST_PER_REQUEST = 50;
    var POSTS_IN_DAYS = '$postsCount posts in $postsDays days';
    var DAYS_WITHOUT_POST = "$dayCount day(s) without posts";
    var UPDATE_POST = "Updating post...";
    var DELETE_POST = "Deleting post...";
    var PUBLISH_POST = "Publishing post...";
    var SAVE_TAGS_LIST = "Saving tags...";

    this.dateProperty = 'publish-on-time';
    this.isAscending = false;

    this.SORT_BY_UPLOAD_TIME = 0;
    this.SORT_BY_LAST_PUBLISH_TIME = 1;

    this.sortType = this.SORT_BY_UPLOAD_TIME;

    /**
     * Move image widget to new date container or position
     * @param imageId the image element id to move to new position
     * @param newDate the new date, it's used to determine the destination
     * container
     */
    this.moveImageWidget = function(imageId, newDate) {
        var time = newDate.getTime();
        var groupDateId = consolr.groupDate.createGroupDateId(newDate);
        var posts = [];

        // remove imageId for posts
        $(consolrPosts['group-date'][groupDateId]).each(function(i, post) {
            if (post.id != imageId) {
                posts.push(post);
            }
        });

        posts.sort(function(a, b) {
            return a - b;
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
            consolr.groupDate.getGroupDateWidget(groupDateId, newDate).append(imageElement);
        }
    }

    /**
     * refresh image widget position and update internal post data
     * @param params contains new post values. {postId,publishDate,caption,tags}
     * @param moveImage if true the image widget is moved to new position
     */
    this.refreshImagePosition = function(params, moveImage) {
        var fromDate = consolr.findPost(params.postId)['consolr-date'];
        var toDate = new Date(params.publishDate);

        consolr.movePost(params);
        if (moveImage) {
            consolr.moveImageWidget(params.postId, toDate);
        }

        $('#i' + params.postId + ' .date-image-time').html(toDate.format('HH:mm:ss'));
        consolr.groupDate.setGroupDateTitle(fromDate);
        consolr.groupDate.setGroupDateTitle(toDate);
    }

    /**
     * Update queued post on server
     * @param params contains new post values. {postId,publishDate,caption,tags,clickThroughLink}
     * @param settings the settings to use, the success property if set points
     * to a function called on update success
     */
    this.updateQueuedPost = function(params, settings) {
        settings.progressMessage = UPDATE_POST;
        doServerOperation('doUpdate.php', params, settings);
    },

    this.deletePost = function(post, settings) {
        if (typeof(settings) == "undefined") {
            settings = {};
        }
        settings.progressMessage = DELETE_POST;
        settings.success = function(params) {
            var groupDate = consolrPosts['group-date'][post['group-date']];
            var index = consolr.groupDate.findPostIndex(groupDate, post.id);
            groupDate.splice(index, 1);
            consolrPosts['posts'].splice(consolr.findPostIndex(post.id), 1);

            $('#i' + post.id).fadeOut(500, function() {
                $('#i' + post.id).remove();
                consolr.groupDate.setGroupDateTitle(post['consolr-date']);
                consolr.updateMessagePanel();
            });

        }
        var params = {postId: post.id};

        doServerOperation('doDelete.php', params, settings);
    },

    this.publishPost = function(post, settings) {
        if (typeof(settings) == "undefined") {
            settings = {};
        }
        settings.async = true;
        settings.progressMessage = PUBLISH_POST;
        settings.success = function(params) {
            var groupDate = consolrPosts['group-date'][post['group-date']];
            var index = consolr.groupDate.findPostIndex(groupDate, post.id);
            groupDate.splice(index, 1);
            consolrPosts['posts'].splice(consolr.findPostIndex(post.id), 1);

            $('#i' + post.id).fadeOut(500, function() {
                $('#i' + post.id).remove();
                consolr.groupDate.setGroupDateTitle(post['consolr-date']);
                consolr.updateMessagePanel();
            });

        }
        var params = {
                postId: post.id,
                caption: post['photo-caption']
        };
        if (post['photo-link-url']) {
            params.clickThroughLink = post['photo-link-url'];
        };

        doServerOperation('doPublish.php', params, settings);
    },

    doServerOperation = function(url, params, settings) {
        var config = {success: null, error: null, progressMessage: null, async : false};
        if (settings) {
            $.extend(config, settings);
        }

        $.ajax({url: url,
                type: 'post',
                async: config.async,
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
                    if (config.progressMessage) {
                        consolr.showOperationProgressMessageText(config.progressMessage);
                    }
                }
            });
    },

    /**
     * Use the global consolrPosts to find a post by id
     * @param postId the postId used for search
     * @returns the post if found, null otherwise
     */
    this.findPost = function(postId) {
        var index = consolr.findPostIndex(postId);

        return index < 0 ? null : consolrPosts['posts'][index];
    },

    /**
     * Use the global consolrPosts to find the index post by id
     * @param postId the postId used for search
     * @returns the index if found, -1 otherwise
     */
    this.findPostIndex = function(postId) {
        // remove the alphabetic prefix
        if (typeof(postId) == "string") {
            postId = parseInt(postId.replace(/^[a-z]/i, ''), 10);
        }
        var arr = consolrPosts['posts'];

        for (var i in arr) {
            if (arr[i].id == postId) {
                return i;
            }
        }
        return -1;
    },

    /**
     * Move post to new sorted position, the post 'consolr-date' property
     * is updated to new value
     * @param params contains new post values. {postId,publishDate,caption,tags}
     * @returns the post
     */
    this.movePost = function(params) {
        var posts = consolrPosts['posts'];
        var postId = params.postId;

        for (var i in posts) {
            var post = posts[i];

            if (post.id == postId) {
                var publishDate = new Date(params.publishDate);
                var currPostDate = post['consolr-date'];

                post['tags'] = params.tags.replace(/,\s*/, ',').split(',');
                post['photo-caption'] = params.caption;
                post['consolr-date'] = publishDate;
                post['photo-link-url'] = params.clickThroughLink;

                posts.splice(i, 1);

                // move to ordered position
                var newIndex = consolr.findTimeClosestIndex(posts, publishDate);
                newIndex = newIndex < 0 ? posts.length : newIndex;
                posts.splice(newIndex, 0, post);

                consolr.groupDate.updatePostGroupDate(post, currPostDate, publishDate);

                return post;
            }
        }
        return null;
    },

    /**
     * Find the left closest post index by consolr-date
     * @param posts array
     * @param time used to find closest index
     * @returns the index of -1 if ts in greater tha all posts
     */
    this.findTimeClosestIndex = function(posts, time) {
        for (var i = 0; i < posts.length; i++) {
            if (time < posts[i]['consolr-date']) {
                return i;
            }
        }
        return -1;
    }

    this.updateMessagePanel = function(showEmptyDays) {
        showEmptyDays = typeof(showEmptyDays) == "undefined" || showEmptyDays == null ? true : showEmptyDays;
        var postsCount = getPostsCount();

        var html = $.formatString(POSTS_IN_DAYS, {
            postsCount : postsCount.count,
            postsDays : postsCount.days
            });
        this.setMessageText(html);

        if (showEmptyDays) {
            var propPosition = consolr.isAscending ? 'start' : 'end';
            var emptyDays = getEmptyDays();

            $('.empty-days').remove();
            $(emptyDays).each(function(i, item) {
                var str = $.formatString(DAYS_WITHOUT_POST, {
                    dayCount: item.dayCount
                });

                $('<div class="empty-days ui-corner-all"><span>' + str + '</span></div>')
                    .insertAfter($('#c' + consolr.groupDate.createGroupDateId(item[propPosition])))
            })
        }
    }

    getPostsCount = function() {
        var days = 0;
        for (g in consolrPosts['group-date']) {
            if (consolrPosts['group-date'][g].length > 0) ++days;
        };
        return {count : consolrPosts['posts'].length, days : days};
    }

    function fetchTumblr(url, settings) {
        $.ajax({url: url + '&start=' + settings.start,
                dataType: 'json',
                async: false,
                success: function(data, status) {
                        if (settings.postsToGet === null) {
                            settings.postsToGet = data['posts-total'];
                        }
                        settings.posts = settings.posts.concat(data['posts']);
                        if (data['posts'].length > 0 && ((settings.start + settings.num) < settings.postsToGet)) {
                            if (typeof (settings.progress) == "function") {
                                settings.progress(data, settings.posts);
                            }

                            settings.start += settings.num;
                            fetchTumblr(url, settings);
                        } else {
                            if (typeof (settings.complete) == "function") {
                                settings.complete(settings.posts, data);
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
                    postsToGet : null,
                    progress : null,
                    complete : null,
                    tags : null,
                    id: null,
                    type: 'photo'};

        if (settings) {
            $.extend(config, settings);
        }
        if (config.postsToGet !== null && config.postsToGet < config.num) {
            config.num = config.postsToGet;
        }
        if (config.id) {
            url = url + '?callback=?&id=' + config.id;
            config.postsToGet = 1;
        } else {
            url = url + '?callback=?&type=' + config.type + '&num=' + config.num;
            if (config.tags) {
                url += '&tagged=' + config.tags;
            }
        }
        fetchTumblr(url, config);
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
     * Return the time adjusted to be comprised between startTime and endTime
     * @param {date} startTime the lower bound time, if it is null endTime must be not null
     * @param {date} endTime the upper bound time, if it is null startTime must be not null
     * @param timeSpan when startTime is null the time returned is computed
     * using only endTime to which is subtracted the timeSpan, if endTime is null
     * then it is used only startTime to which is added the timeSpan (default 10).
     * @returns {date} the new object with adjusted time, null if startTime and
     * endTime are both null
     */
    this.adjustTime = function(startTime, endTime, timeSpan) {
        timeSpan = timeSpan ? timeSpan : 10;
        var adjustedTime;

        if (!startTime && !endTime) {
            return null;
        }
        if (!startTime) {
            adjustedTime = new Date(endTime).add("m", -timeSpan);
            // Check if the day is the same
            if (!adjustedTime.equalsIgnoreTime(endTime)) {
                adjustedTime = endTime;
            }
        } else if (!endTime) {
            adjustedTime = new Date(startTime).add("m", timeSpan);
            if (!adjustedTime.equalsIgnoreTime(startTime)) {
                adjustedTime = startTime;
            }
        } else {
            adjustedTime = new Date((startTime.getTime() + endTime.getTime()) / 2);
            adjustedTime.setSeconds(0);
        }

        return adjustedTime;
    }

    getEmptyDays = function() {
        var ts = [];
        for (var k in consolrPosts['group-date']) {
            if (consolrPosts['group-date'][k].length) {
                // uses UTC to be sure the calculation ignores daylight saving offset
                // e.g difference between "01 nov 2010" and "31 oct 2010" is 2
                ts.push(Date.UTC(parseInt(k.substring(2, 6), 10),
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
            var days = Math.ceil((ts[i + 1] - ts[i]) / oneDay) - 1;
            if (days > 0) {
                emptyDays.push({
                    start : new Date(ts[i]),
                    end : new Date(ts[i + 1]),
                    dayCount : days});
            }
        }
        return emptyDays;
    }

    this.initTimeline = function(tumblrDateProperty, ascending) {
        if (consolrPosts.posts.length == 0) {
            return;
        }
        consolr.dateProperty = tumblrDateProperty;
        consolr.isAscending = ascending;

        if (this.sortType == this.SORT_BY_LAST_PUBLISH_TIME) {
            var tags = consolr.tags.getUniqueFirstTags(consolrPosts.posts);
            var lastPublished = consolr.tags.fetchTagsLastPublishTime(tumblrName, tags);
            var map = {};
            for (var i = 0; i < lastPublished.tags.length; i++) {
                var tagTS = lastPublished.tags[i];
                map[tagTS.tag] = tagTS.timestamp;
            }
            var timeForNewtags = new Date(0)
            $(consolrPosts['posts']).each(function(i, post) {
                var tag = post.tags.length > 0 ? post.tags[0] : null;
                var timestamp = tag ? map[tag] : -1;
                post['consolr-date'] = tag ? (timestamp > 0 ? new Date(timestamp * 1000) : timeForNewtags) : timeForNewtags;
            });
        } else {
            // This ensure dates are normalized with client side timezone
            $(consolrPosts['posts']).each(function(i, el) {
                el['consolr-date'] = new Date(el[consolr.dateProperty]);
            });
        }
        var direction = consolr.isAscending ? 1 : -1;
        consolrPosts['posts'].sort(function(a, b) {
            a = a['consolr-date'];
            b = b['consolr-date'];
            return a === b ? 0 : a < b ? -direction : direction;
        });
        consolrPosts['group-date'] = consolr.groupDate.groupPostsByDate(consolrPosts.posts);

        $('#date-container').empty().html(consolr.groupDate.getDateContainerHTML({
                sortByDateAsc : consolr.isAscending}));
        $('.title-group-date').click(function() {
            var groupDate = 'gd' + $(this).attr('id').replace(/^[a-z]*/i, '');
            $('#dialog-time-distribution').dialog('option', 'groupDate', groupDate);
            $('#dialog-time-distribution').dialog('open');
        })
        this.initLazyImageLoader();
    }

    this.initLazyImageLoader = function() {
        var showImages = function() {
                $('.date-image:visible img[asrc]').each(function() {
                var img = $(this);
                var top = $(window).scrollTop();
                var bottom = top + $(window).height();
                var imgTop = img.offset().top;
                var imgBottom = imgTop + img.height();

                if ((top <= imgTop && imgTop <= bottom)
                    || (top <= imgBottom && imgBottom <= bottom)) {
                    img.attr('src', img.attr('asrc'));
                    img.removeAttr('asrc');
                }
            });
        };

        $(window).scroll(function() {
            showImages();
        });
        $(window).resize(function() {
            showImages();
        });

        // trigger a resize to make "on screen" images visible
        $(window).resize();
    }

    this.saveTagsList = function(params, settings) {
        if (typeof(settings) == "undefined") {
            settings = {};
        }
        if (typeof(params) == "undefined") {
            params = {};
        }

        settings.progressMessage = SAVE_TAGS_LIST;
        doServerOperation('doSaveTagsList.php', params, settings);
    }
}).apply(consolr);
