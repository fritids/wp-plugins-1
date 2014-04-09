var Pinterest = {

    is_upload:'',

    init:function() {
        jQuery('#pin_add_image').click(Pinterest.addImage);
        jQuery('.pin-actions').live('click', Pinterest.removeImage)
        Pinterest.appendImage();
    },

    ajax:function(action, type, callback) {
        jQuery.ajax({type:"POST",url:ajaxurl,dataType:type,data:action,success:function(html) {
            if (typeof callback == 'function') callback(html);
        }});
    },

    addImage:function() {
        Pinterest.is_upload = true;
        tb_show('', 'media-upload.php?post_id=' + jQuery('#post_ID').val() + 'type=image&TB_iframe=true');
        return false;
    },

    appendImage:function() {
        window.original_action = window.send_to_editor;
        window.send_to_editor = function(response) {
	if (Pinterest.is_upload) {
	var html=jQuery(response);
                Pinterest.is_upload = false;

                if(jQuery('img',html).size()>0){
                    var attachClasses = jQuery('img',html).attr('class').split(' ');
                } else {
                    var attachClasses = jQuery(html).attr('class').split(' ');
                }

                for (key in attachClasses) {
                    if (attachClasses[key].indexOf("wp-image-") != -1) {
                        var attachID = attachClasses[key].split('-')[2];
                    }
                }
                Pinterest.ajax('&action=getPinThumb&attachID=' + attachID, 'html', function(response) {
                    var old_image = jQuery('.pin_image_container img');
                    
                    if (old_image.size() > 0) old_image.fadeOut(function() {
                        jQuery(this).remove();
                    });

                    jQuery('#pin_image').attr('value', attachID);
                    jQuery('.pin_image_container').append(response+'<br /><div class="pin-actions"><a href="#">Remove Image</a></div>');

                    tb_remove();
                });
            } else {
                window.original_action(response);
            }
        }
    },

    removeImage:function() {
        jQuery('.pin_image_container img, .pin-actions').fadeOut(function() {
            jQuery(this).remove();
            jQuery('#pin_image').attr('value', '');
        });
        return false;
    }
}

jQuery(document).ready(function() {
    Pinterest.init();
});