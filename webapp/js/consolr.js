var consolr = {
    /**
     * Move image element to new date container or position
     * @param imageId the image element id to move to new position
     * @param newDate the new date string, it's used to determine the destination
     * container
     */
    moveImage : function(imageId, newDate) {
        var time = newDate.getTime();
        var groupDateId = consolr.createGroupDateId(newDate);

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
            $("#" + groupDateId).append(imageElement);
        }
    },

    updateImagePost : function(params) {
        $.ajax({url: 'doUpdate.php',
                type: 'post',
                async: false,
                data: params,
                success: function(data, status) {
                    var newDate = new Date(params.publishDate);
                    var post = consolr.movePost(parseInt(params.postId, 10), newDate);
                    post['tags'] = params.tags.replace(/,\s*/, ',').split(',');
                    post['photo-caption'] = params.caption;
                    consolr.moveImage(params.postId, newDate);
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
    findPost : function(postId) {
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
    movePost : function(postId, destDateStr) {
        var destDate = new Date(destDateStr);
        var fromGroupDate = consolrPosts['group-date'][consolr.findGroupDateByPostId(postId)];
        var destGroupDate = consolrPosts['group-date'][consolr.createGroupDateId(destDate)];

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

        // add into dest group, order is not important
        destGroupDate.push(post['id']);

        posts.splice(currIndex, 1);
        posts.splice(newIndex, 0, post);
        
        return post;
    },

    findGroupDateByPostId : function(postId) {
        postId = parseInt(postId);
        var groups = consolrPosts['group-date'];

        for (var i in groups) {
            if (groups[i].indexOf(postId) >= 0) {
                return i;
            }
        }
        return null;
    },

    createGroupDateId : function(date) {
        function pad(num) {
            return (num < 10 ? "0" : "") + num;
        }
        return (1900 + date.getYear()) + pad(date.getMonth() + 1) + pad(date.getDate());
    },

    findTimestampIndex : function(arr, ts) {
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
    }
}