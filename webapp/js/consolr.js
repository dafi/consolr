var consolr = {
    /**
     * Move image element to new date container or position
     * @param imageId the image element id to move to new position
     * @param newDate the new date string, it's used to determine the destination
     * container
     */
    moveImage : function(imageId, newDate) {
        function pad(num) {
            return (num < 10 ? "0" : "") + num;
        }
        var d = new Date(newDate);
        var time = d.getTime();
        var destDate = (1900 + d.getYear()) + pad(d.getMonth() + 1) + pad(d.getDate());

        // Move image to new position
        var element;
        var lastElement;
        $("#" + destDate + " li:not(#" + imageId + ")").each(function(index) {
            var iTime = new Date($(this).attr("publish")).getTime();

            lastElement = this;
            if (time < iTime) {
                element = this;
                return false;
            }
            return true;
        });

        // update the publish time attribute
        var imageElement = $("#" + imageId)
                            .attr("publish", newDate)
                            .detach();

        if (element) {
            imageElement.insertBefore(element);
        } else {
            imageElement.insertAfter(lastElement);
        }
    },
    
    updateImagePost : function(params) {
        consolr.moveImage(params.postId, params.publishDate);
        
        
        return;
    
    
    
        $.ajax({url: 'doUpdate.php',
                type: 'post',
                async: false,
                data: params,
                success: function(data, status) {
                    consolr.moveImage(params.postId, params.publishDate);
                },
                error: function(xhr, status) {
                    alert(xhr.statusText);
                }
            });
    }
}