jQuery(function () {

    /**
     * Uploader
     */
    jQuery(document).ready(function () {
        var uploader = jQuery("#gallery-ajax-widget-drop-area");
        var savingLoader = jQuery("#gallery-ajax-widget-saving-loader");
        var list = uploader.closest(":has(.ajax-gallery-list)").find(".ajax-gallery-list");
        var url = list.data("action-url");
        url = url + "?action=" + list.data("param-upload") + "&modelId=" + list.data("model-id");
        var progress = uploader.find(".upload-progress");
        uploader.dmUploader({
            url: url,
            fieldName: "galleryData",
            allowedTypes: "image/*",
            onNewFile: function () {
                if (!validateMaxImagesCount(uploader)) {
                    var errorMessage = list.data("messages").maxFilesTotalError;
                    jqAlert(errorMessage, 'red');
                    uploader.find("input[type=file]").val("");
                    uploader.dmUploader("reset");
                    return false;
                }
            },
            onBeforeUpload: function () {
                progress.show();
            },
            onUploadProgress: function (id, percent) {
                progress.val(percent);
                if (percent == 100) {
                    progress.hide();
                    savingLoader.show();
                }
            },
            onUploadSuccess: function () {
                progress.val(0);
                savingLoader.hide();
                jQuery.pjax.reload({container: "#gallery-ajax-widget-pjax"});
            },
            onUploadError: function () {
                progress.hide();
                savingLoader.hide();
                var errorMessage = list.data("messages").errorUpload;
                jqAlert(errorMessage, 'red');
            },
            onComplete: function () {
                progress.val(0);
                progress.hide();
                savingLoader.hide();
            },
            onDragEnter: function(){
              this.addClass('active');
            },
            onDragLeave: function(){
              this.removeClass('active');
            },
        });
    });

    /**
     * Sends new order request
     */
    jQuery(document).on("sortupdate", ".ajax-gallery-list", function (event, ui) {
        var messages = jQuery(this).data("messages");
        var url = jQuery(this).data("action-url");
        url = url + "?action=" + jQuery(this).data("param-order") + "&modelId=" + jQuery(this).data("model-id");

        var items = jQuery(".ajax-gallery-list .ajax-gallery-item");
        var data = [];
        jQuery.each(items, function () {
            data.push(jQuery(this).data("image-id"));
        });

        jQuery.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {galleryData: data},
            success: function (result) {
                if (!result.success) {
                    jqAlert(messages.errorOrder, 'red');
                }
            },
            error: function () {
                jqAlert(messages.errorOrder, 'red');
            }
        });
    });

    /**
     * Delete images request
     */
    jQuery(document).on("click", ".ajax-gallery-delete-button", function (event) {
        event.preventDefault();

        var list = jQuery(this).closest(":has(.ajax-gallery-list)").find(".ajax-gallery-list");
        var messages = list.data("messages");
        var url = list.data("action-url");
        url = url + "?action=" + list.data("param-delete") + "&modelId=" + list.data("model-id");

        var items = list.find(".ajax-gallery-item");
        var data = [];
        jQuery.each(items, function () {
            if (jQuery(this).find("input:checked").length) {
                data.push(jQuery(this).data("image-id"));
            }
        });

        if (data.length === 0) {
            jqAlert(messages.notSelected, 'orange');
            return;
        }

        jQuery.confirm({
            title: false,
            content: '<b>' + messages.confirmDelete + '<b>',
            escapeKey: 'cancel',
            backgroundDismiss: 'cancel',
            type: 'blue',
            buttons: {
                OK: {
                    btnClass: 'btn-blue',
                    keys: ['enter'],
                    action: function () {
                        jQuery.ajax({
                            url: url,
                            type: 'POST',
                            dataType: 'json',
                            data: {galleryData: data},
                            success: function (result) {
                                if (result.success) {
                                    jQuery.pjax.reload({container: "#gallery-ajax-widget-pjax"});
                                } else {
                                    jqAlert(messages.errorDelete, 'red');
                                }
                            },
                            error: function () {
                                jqAlert(messages.errorDelete, 'red');
                            }
                        });
                    }
                },
                cancel: function () {
                    return;
                }
            }
        });
    });
    
    /**
     * Change ckeckbox color
     */
    jQuery(document).on("change", ".ajax-gallery-list .delete-image-checkbox", function (event, ui) {
        var item = jQuery(this).closest(".ajax-gallery-item");
        if(jQuery(this).is(':checked')) {
            item.addClass("checked");
        } else {
            item.removeClass("checked");
        }
    });

    /**
     * Check all
     */
    jQuery(document).on("click", ".ajax-gallery-select-all-button", function (event) {
        event.preventDefault();
        var list = jQuery(this).closest(":has(.ajax-gallery-list)").find(".ajax-gallery-list");
        var items = list.find(".ajax-gallery-item");
        jQuery.each(items, function () {
            jQuery(this).find("input[type=checkbox]").prop("checked", true);
            jQuery(this).addClass('checked');
        });
    });

    /**
     * Uncheck all
     */
    jQuery(document).on("click", ".ajax-gallery-clear-selection-button", function (event) {
        event.preventDefault();
        var list = jQuery(this).closest(":has(.ajax-gallery-list)").find(".ajax-gallery-list");
        var items = list.find(".ajax-gallery-item");
        jQuery.each(items, function () {
            jQuery(this).find("input[type=checkbox]").prop("checked", false);
            jQuery(this).removeClass('checked');
        });
    });

    /**
     * Checks if max images count is reached
     * @param {type} uploader
     * @returns {Boolean}
     */
    function validateMaxImagesCount(uploader) {
        var list = uploader.closest(":has(.ajax-gallery-list)").find(".ajax-gallery-list");
        var items = list.find(".ajax-gallery-item");
        var maxCount = list.data("max-files-total");
        
        if (maxCount <= 0) {
            return true;
        }
        
        var newFilesCount = uploader.find("input[type=file]")[0].files.length;
        if ((items.length + newFilesCount) > maxCount) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * JQuery Alert
     * @param {string} message
     * @param {string} type
     */
    function jqAlert(message, type) {
        jQuery.alert({
            title: false,
            content: '<b>' + message + '<b>',
            type: type,
            backgroundDismiss: 'OK',
            buttons: {
                OK: {
                    btnClass: 'btn-' + type,
                    keys: ['enter']
                }
            }
        });
    }
});
