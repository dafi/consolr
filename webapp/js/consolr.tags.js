if (typeof(consolr) == "undefined") {
    var consolr = {};
}

if (typeof(consolr.tags) == "undefined") {
    consolr.tags = {};
}

(function() {
    this.commands = {};

    // the map containing tag as key and its last publish time as value
    this.tagsLastPublishTime = {};

    /**
     * Return tags grouped by name
     * @returns {array} (elements are objects in the form {name, count})
     */
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
        var tags = consolr.tags.groupTags();
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Tags');
        data.addColumn('number', 'Posts');

        $(tags).each(function(i, tag) {
            data.addRow([tag.name, tag.count]);
        });

        var tagsChart = $('#tags-chart');
        var chartHeight = Math.max(tagsChart.height(), tags.length * 20);

        data.sort(0);
        new google.visualization.BarChart(
            tagsChart.get(0)).draw(data, {legend : 'none', height: chartHeight});
    }

    /**
     * Show images having tags present in includeTags, not matching images are hidden
     * @param posts the tumblr posts array
     * @param includeTags tags arrays to match (elements are objects in the form {name, count})
     * @param {boolean} [showAllIfEmpty=true] if includeTags is empty show all images, otherwise
     * hide all images
     */
    this.showImagesByTags = function(posts, includeTags, showAllIfEmpty) {
        showAllIfEmpty = showAllIfEmpty ? showAllIfEmpty : true;

        var containsTag = function(tag) {
            var retVal = false;
            $(includeTags).each(function(i, includeTag) {
                if (tag == includeTag.name) {
                    retVal = true;
                    return false;
                }
                return true;
            });
            return retVal;
        }

        if (showAllIfEmpty && !includeTags.length) {
            $(".date-image").show();
            return;
        }
        $(posts).each(function(i, post) {
            var hide = true;
            $(post.tags).each(function(i, tag) {
                if (containsTag(tag)) {
                    hide = false;
                    return false;
                }
                return true;
            });
            if (hide) {
                $("#i" + post.id).hide();
            } else {
                $("#i" + post.id).show();
            }
        });
        // simulate a scroll operation to ensure visible images are loaded
        $(window).scroll();
    }

    /**
     * Open dialog and filter by selected tags
     */
    this.commands.filterTags = function() {
        var tags = consolr.tags.groupTags();
        tags.sort(function(a, b) {
            return a.name == b.name ? 0 : a.name < b.name ? -1 : 1;
        });

        $("#dialog-filter-tags").dialog('option', 'tags', tags);
        $("#dialog-filter-tags").dialog('option', 'onOk', function(tags) {
            consolr.tags.showImagesByTags(consolrPosts.posts, tags);
        });
        $("#dialog-filter-tags").dialog('open');
    };

    this.commands.showTagChart = function() {
        $('#dialog-tags').dialog('open');
    }

    this.formatTagsPublishDaysAgo = function(timestamps) {
        var nowTime = new Date().clearTime().getTime();
        var arr = [];
        var dayTime = 24 * 60 * 60 * 1000;
        
        for (var i = 0; i < timestamps.length; i++) {
            var tagTS = timestamps[i];
            var dayString;
            if (tagTS.timestamp < 0) {
                dayString = "new";
            } else {
                var timestamp = new Date(tagTS.timestamp * 1000).clearTime().getTime();
                var spanTime = nowTime - timestamp;
                var days = Math.floor(spanTime / dayTime);
                var dayString;
                if (days <= 0) {
                    dayString = "today";
                } else if (days == 1) {
                    dayString = "yesterday";
                } else {
                    dayString = days + " days ago";
                }
            }
            arr.push(tagTS.tag + ' <b>(' + dayString + ')</b>');
        }
        return arr.join(', ');
    }

    /**
     * Get the last publish time for passed tags stored in local cache
     * returns the object containing found and missing tags in cache
     * {'tags', 'missingTags'}
     */
    this.getTagsLastPublishTime = function(tags) {
        var missingTags = [];
        var tagsLastPublishTime = [];

        for (var i = 0; i < tags.length; i++) {
            var tag = tags[i];
            var timestamp = this.tagsLastPublishTime[tag];

            if (timestamp) {
                tagsLastPublishTime.push({'tag': tag, 'timestamp' : timestamp});
            } else {
                missingTags.push(tag);
            }
        }
        return {'tags': tagsLastPublishTime, 'missingTags': missingTags};
    }
    
    /**
     * Update timestamps in cache using object {'tag', 'timestamp'}
     */
    this.updateTagsLastPublishTime = function(timestamps) {
        for (var i = 0; i < timestamps.length; i++) {
            var tagTS = timestamps[i];
            this.tagsLastPublishTime[tagTS.tag] = tagTS.timestamp;
        }
    }
    
    this.fetchTagsLastPublishTime = function(tumblrName, tags) {
        var lastPublishTime = this.getTagsLastPublishTime(tags);

        if (lastPublishTime.missingTags.length) {
            $.ajax({
                url: "doTagsPublishDate.php",
                type: 'post',
                data: {
                    tags: lastPublishTime.missingTags.join(","),
                    tumblrName: tumblrName
                },
                success: function(timestamps) {
                    consolr.tags.updateTagsLastPublishTime(timestamps);
    
                    // preserve tags order so reload all tags
                    lastPublishTime = consolr.tags.getTagsLastPublishTime(tags);
                },
                dataType: 'json',
                async: false
            });
        }
        return lastPublishTime;
    }
    
    this.evictTagsLastPublishTime = function(tags) {
        for (var i = 0; i < tags.length; i++) {
            var tag = tags[i];
            delete this.tagsLastPublishTime[tag];
        }
    }
    
    /**
     * Return the first tag of every post removing duplicates
     */
    this.getUniqueFirstTags = function(posts) {
        var mapTags = {};

        for (var i = 0; i < posts.length; i++) {
            var tags = posts[i].tags;
            if (tags.length) {
                var tag = tags[0];
                mapTags[tag] = 1;
            }
        }

        var unique = [];
        // create array from map
        for (var i in mapTags) {
            unique.push(i);
        }
        
        return unique;
    }
}).apply(consolr.tags);
