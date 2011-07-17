/**
 * Created by JetBrains PhpStorm.
 * User: Samsung
 * Date: 05/07/11
 * Time: 12:46
 */

jQuery(document).ready(function() {
    var post_id = jQuery('#vspi_image_box').data('post_id');
    var vspi_image = jQuery('#vspi_image');
    if(vspi_image.length > 0){
    vspi_image.uploadify({
        'uploader'  : vspi_image_globals.url + '/uploadify/uploadify.swf',
        'script'    : vspi_image_globals.ajax_url,
        'cancelImg' : vspi_image_globals.url + '/uploadify/cancel.png',
        'auto'      : true,
        'multi'       : true,
        'scriptData' : {'post_id': post_id, 'action': 'vspi_image','vspi_user':vspi_image_globals.user, 'vspi_action': 'uploadify', '_wpnonce': vspi_image_globals.vspi_nonce },
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
    var holder = jQuery('#vspi_image_box')
    if(holder.length > 0){
    var post_id = holder.data('post_id');
    var getUrl = vspi_image_globals.ajax_url + '?action=vspi_image&vspi_action=reload&_wpnonce=' + vspi_image_globals.vspi_nonce + '&id=' + post_id;
    jQuery.getJSON(getUrl, '', function(response) {
        holder.empty();
        jQuery.each(response, function(a, b) {
            if (b) jQuery('#vspi_image_tmpl').tmpl(b).appendTo('#vspi_image_box');
        });
        jQuery('a.vspi_delete').click(function() {
             var parent = jQuery(this).parent();
            var id = parent.data('id');   

            jQuery.post(vspi_image_globals.ajax_url,
                {action: 'vspi_image', vspi_action:'delete_image', _wpnonce: vspi_image_globals.vspi_nonce, 'attach_id': id},
                function(response) {
                    console.log(response);
                },
                "json"
            );
            parent.fadeOut("slow");
            return false
        });
        jQuery('a.vspi_thumb').click(function() {
            var parent = jQuery(this).parent();
            var post_id = jQuery('#vspi_image_box').data('post_id');
            var id = parent.data('id');
            jQuery.post(vspi_image_globals.ajax_url,
                {action:"vspi_image", vspi_action: "set_thumbnail", post_id: post_id, thumbnail_id: id, _wpnonce : vspi_image_globals.vspi_nonce},
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
