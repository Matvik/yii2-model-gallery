jQuery(function () {
    /**
     * Writes new order in order input
     */
    jQuery(document).on("sortupdate", ".form-gallery-list", function (event, ui) {
        var input = jQuery("#gallery-form-widget-input-order");
        var items = jQuery(".form-gallery-list .form-gallery-item");
        var data = [];
        jQuery.each(items, function () {
            data.push(jQuery(this).data("image-id"));
        });
        input.val(JSON.stringify(data));
    });

    /**
     * Writes infomation about deleting images in delete input
     */
    jQuery(document).on("change", ".form-gallery-list .delete-image-checkbox", function (event, ui) {
        var input = jQuery("#gallery-form-widget-input-deleting");
        var items = jQuery(".form-gallery-list .form-gallery-item");
        var data = [];
        jQuery.each(items, function () {
            if (jQuery(this).find("input.delete-image-checkbox:checkbox:checked").length) {
                data.push(jQuery(this).data("image-id"));
            }
        });
        input.val(JSON.stringify(data));
    });

    /**
     * Validates count and renders uploaded images preview
     */
    jQuery(document).on("change", "#gallery-form-widget-input-files", function () {
        if (this.files) {
            
            var list = jQuery("#gallery-form-widget-input-files-list");
            var imageWidth = list.data("item-width");
            var imageHeight = list.data("item-height");
            var currentCount = list.data("current-images-count");
            var maxUploaded = list.data("max-files-uploaded");
            var maxTotal = list.data("max-files-total");
            var maxUploadedError = list.data("max-files-uploaded-error");
            var maxTotalError = list.data("max-files-total-error");
            
            // count validation
            if (maxUploaded > 0 && this.files.length > maxUploaded) {
                clearInput();
                alert(maxUploadedError);
                return;
            }
            if (maxTotal > 0 && (this.files.length + currentCount) > maxTotal) {
                clearInput();
                alert(maxTotalError);
                return;
            }
            
            if (typeof (FileReader) != "undefined") {
                list.empty();
                var readers = [];
                for (var i = 0; i < this.files.length; i++) {
                    // Only process image files.
                    if (!this.files[i].type.match('image.*')) {
                        continue;
                    }

                    var file = this.files[i];
                    readers[i] = new FileReader();

                    readers[i].onload = function (e) {
                        var item = jQuery("<li><img></li>");
                        var image = item.children("img");

                        //image.attr("width", imageWidth);
                        image.attr("height", imageHeight);
                        list.append(item);
                        image.attr('src', e.target.result);
                    };
                    readers[i].readAsDataURL(file);
                }
            } else {
                alert("This browser does not support HTML5 FileReader.");
            }
        }
    });

    /**
     * Clears input list
     */
    jQuery(document).on("click", "#gallery-form-widget-input-files-clear", function (e) {
        e.preventDefault();
        clearInput();
    });

    function clearInput() {
        jQuery("#gallery-form-widget-input-files").val('');
        jQuery("#gallery-form-widget-input-files-list").empty();
    }
});
