/**
 * Created by JetBrains PhpStorm.
 * User: Samsung
 * Date: 05/07/11
 * Time: 12:46
 */

jQuery(document).ready(function() {
    var post_id = jQuery('#sc_image_box').data('post_id');
    var sc_image = jQuery('#sc_image');
    if(sc_image.length > 0){
    sc_image.uploadify({
        'uploader'  : sc_image_globals.url + '/uploadify/uploadify.swf',
        'script'    : sc_image_globals.ajax_url,
        'cancelImg' : sc_image_globals.url + '/uploadify/cancel.png',
        'auto'      : true,
        'multi'       : true,
        'scriptData' : {'post_id': post_id, 'action': 'sc_image','sc_user':sc_image_globals.user, 'sc_action': 'uploadify', '_wpnonce': sc_image_globals.sc_nonce },
        'onError'     : function (event, ID, fileObj, errorObj) {
            alert(errorObj.type + ' Error: ' + errorObj.info);
            //console.log(event,ID,fileObj,errorObj);
        },
        'onComplete'  : function(event, ID, fileObj, response, data) {
            //console.log(response);
            //console.log(data);
        },
        'onAllComplete' : function(event, data) {
            //console.log(data);
            scImageReload();
        }
    });
    scImageReload();
    }
});


function scImageReload() {
    var holder = jQuery('#sc_image_box')
    if(holder.length > 0){
    var post_id = holder.data('post_id');
    var getUrl = sc_image_globals.ajax_url + '?action=sc_image&sc_action=reload&_wpnonce=' +
        sc_image_globals.sc_nonce + '&id=' + post_id;
    jQuery.getJSON(getUrl, '', function(response) {
        holder.empty();
        jQuery.each(response, function(a, b) {
            if (b) jQuery('#sc_image_tmpl').tmpl(b).appendTo('#sc_image_box');
        });
        jQuery('a.sc_delete').click(function() {
             var parent = jQuery(this).parent();
            var id = parent.data('id');   

            jQuery.post(sc_image_globals.ajax_url,
                {action: 'sc_image', sc_action:'delete_image', _wpnonce: sc_image_globals.sc_nonce, 'attach_id': id},
                function(response) {
                    console.log(response);
                },
                "json"
            );
            parent.fadeOut("slow");
            return false
        });
        jQuery('a.sc_thumb').click(function() {
            var parent = jQuery(this).parent();
            var post_id = jQuery('#sc_image_box').data('post_id');
            var id = parent.data('id');
            jQuery.post(sc_image_globals.ajax_url,
                {action:"sc_image", sc_action: "set_thumbnail", post_id: post_id, thumbnail_id: id, _wpnonce : sc_image_globals.sc_nonce},
                function(response) {
                    console.log(response);
                    scImageReload();
                }
            );
            jQuery('div.sc-thumb').removeClass('sc-thumb');
            parent.addClass('sc-thumb');
            return false

        });
        console.log(response);
    });
}
}
