/**
 * Created by JetBrains PhpStorm.
 * User: Samsung
 * Date: 05/07/11
 * Time: 12:46
 */


jQuery(document).ready(function($) {
    var post_id = jQuery('#vspi_image_box').data('post_id');
	var vspi_swfu;
SWFUpload.onload = function() {
	var settings = {
			button_text: vspi_image_globals.button_text,
			button_text_style: '.button { text-align: center; font-weight: bold; font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif; font-size: 11px; text-shadow: 0 1px 0 #FFFFFF; color:#464646; }',
			button_height: "23",
			button_width: "132",
			button_text_top_padding: 3,
			button_image_url: vspi_image_globals.button_image_url,
			button_placeholder_id: "vspi-flash-browse-button",
			upload_url : vspi_image_globals.upload_url,
			flash_url : vspi_image_globals.flash_url,
			file_post_name: "async-upload",
			file_types: vspi_image_globals.file_types,
			post_params : {
                'auth_cookie' : vspi_image_globals.auth_cookie,
                'logged_in_cookie' : vspi_image_globals.logged_in_cookie,
                '_wpnonce' : vspi_image_globals._wpnonce,
                'post_id' : post_id
            },
			file_size_limit : vspi_image_globals.file_size_limit,
			debug: false,
			upload_start_handler : vspi_start_handler,
			upload_success_handler : vspi_success_handler
		};
		vspi_swfu = new SWFUpload(settings);
};

    var vspi_handler = function (fileObj, serverData){
        console.log(fileObj);
        console.log(serverData);
    };
    var vspi_start_handler = function (file) {
      console.log('start');
	return true;
};

// The uploadSuccess event handler.  This function variable is assigned to upload_success_handler in the settings object
var vspi_success_handler = function (file, server_data, receivedResponse) {
	console.log(file, server_data, receivedResponse);
    //alert("The file " + file.name + " has been delivered to the server. The server responded with " + server_data);
};



    var vspi_image = jQuery('#vspi_image');
    if(vspi_image.length > 0){
    /*
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
    */
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
