if (typeof(consolr) == "undefined") {
    var consolr = {};
}

if (typeof(consolr.tags) == "undefined") {
    consolr.tags = {};
}

(function() {
    this.commands = {};

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
}).apply(consolr.tags);
