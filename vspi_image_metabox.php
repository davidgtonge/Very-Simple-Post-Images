<?php
/*
Plugin Name: Very Simple Post Images
Plugin URI: http://www.simplecreativity.co.uk
Description: Image Metabox using uploadify
Version: 0.2
Author: Dave Tonge & David Laing
Author URI: http://www.simplecreativity.co.uk & http://davidlaing.com
*/


function vspi_image_admin_init()
{
    if (is_admin()) {
        global $current_user;
        get_currentuserinfo();
        define('vspi_image_PLUGIN_PATH', plugins_url('Very-Simple-Post-Images'));
        add_meta_box("vspi_image_metabox", "Images", "vspi_image_metabox", "post", "normal", "high");
        wp_enqueue_script('swfobject');
        wp_enqueue_script('swfupload-all');
        //wp_enqueue_script('swfupload-handlers');
        //wp_enqueue_script('uploadify', vspi_image_PLUGIN_PATH . '/uploadify/jquery.uploadify.v2.1.4.min.js', array('jquery'));
        wp_enqueue_script('vspi_image_metabox', vspi_image_PLUGIN_PATH . '/vspi_image_metabox.js', array('jquery'));
        wp_enqueue_style('vspi_image_metabox', vspi_image_PLUGIN_PATH . '/vspi_image_metabox.css');
        wp_enqueue_script('jquery_tmpl', 'http://ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js', array('jquery'));
        wp_enqueue_style('uploadify', vspi_image_PLUGIN_PATH . '/uploadify/uploadify.css');
     

        $max_upload_size =  wp_max_upload_size();
        wp_localize_script('vspi_image_metabox', 'vspi_image_globals',
                           array(
                                'ajax_url' => admin_url('admin-ajax.php'),
                                'vspi_nonce' => wp_create_nonce('vspi_nonce'),
                                'url' => vspi_image_PLUGIN_PATH,
                                'user' => $current_user->ID,
                                'button_text' => __('Select Files'),
                                'button_image_url' => includes_url('images/upload.png?ver=20100531'),
                                'upload_url' => admin_url('async-upload.php'),
                               'flash_url' => includes_url('js/swfupload/swfupload.swf'),
                               'file_types' => apply_filters('upload_file_glob', '*.*'),
                               "_wpnonce" => wp_create_nonce('media-form'),
                               "auth_cookie" => (is_ssl() ? $_COOKIE[SECURE_AUTH_COOKIE] : $_COOKIE[AUTH_COOKIE]),
                               "logged_in_cookie" => $_COOKIE[LOGGED_IN_COOKIE],
                               'file_size_limit' => $max_upload_size . 'b',
                               'upload_success_handler' => apply_filters( 'swfupload_success_handler', 'uploadSuccess' )
                           )
        );
    }
}

add_action("admin_init", "vspi_image_admin_init");

function vspi_image_metabox($callback)
{
    global $post;
    $upload_size_unit = $max_upload_size =  wp_max_upload_size();
    $sizes = array( 'KB', 'MB', 'GB' );
	for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ )
		$upload_size_unit /= 1024;
	if ( $u < 0 ) {
		$upload_size_unit = 0;
		$u = 0;
	} else {
		$upload_size_unit = (int) $upload_size_unit;
	}
    ?>

<div id="flash-upload-ui" class="hide-if-no-js">
<?php do_action('pre-flash-upload-ui'); ?>

	<div>
	<?php _e( 'Choose files to upload' ); ?>
	<div id="vspi-flash-browse-button"></div>
	<span><input id="cancel-upload" disabled="disabled" onclick="cancelUpload()" type="button" value="<?php esc_attr_e('Cancel Upload'); ?>" class="button" /></span>
	</div>
	<p class="media-upload-size"><?php printf( __( 'Maximum upload file size: %d%s' ), $upload_size_unit, $sizes[$u] ); ?></p>
<?php do_action('post-flash-upload-ui'); ?>
	<p class="howto"><?php _e('After a file has been uploaded, you can add titles and descriptions.'); ?></p>
</div>


<input type="file" name="vspi_image" id="vspi_image"/>
<div id="vspi_image_box" data-post_id="<?php echo $post->ID; ?>">
</div>
<div class="clearfix"></div>

<script id="vspi_image_tmpl" type="text/x-jquery-tmpl">
    <div class="${class}" data-id="${id}">
        <div class="featured_marker"></div>
        <a class="vspi_delete" href="#">Delete</a>
        <a class="vspi_thumb" href="#">Featured</a>
        <img src="${src}" width="${width}" height="${height}" alt="thumbnail" />
    </div>
</script>


<?php

}


add_action('wp_ajax_vspi_image', 'vspi_image_ajax');
add_action('wp_ajax_nopriv_vspi_image', 'vspi_image_ajax');

function vspi_image_ajax()
{
    $vspi_action = $_REQUEST['vspi_action'];

    //Needed because flash doesn't show as logged in
    if ($vspi_action === 'uploadify') {
        wp_set_current_user($_REQUEST['vspi_user']);
    }
    check_ajax_referer('vspi_nonce', '_wpnonce');

    switch ($vspi_action) {

        case "reload":

            $post_id = $_REQUEST['id'];
            $response = vspi_get_thumbs($post_id);
            echo json_encode($response);

            break;

        case "set_thumbnail":

            $post_ID = intval($_POST['post_id']);
            $thumbnail_id = intval($_POST['thumbnail_id']);

            if ($thumbnail_id && $post_ID) {
                update_post_meta($post_ID, '_thumbnail_id', $thumbnail_id);
                echo "Success";
            } else {
                echo "Fail";
            }

            break;


        case "delete_image":

            $attach_ID = intval($_POST['attach_id']);

            if ($attach_ID) {
                wp_delete_attachment($attach_ID);
                update_post_meta($post_ID, '_thumbnail_id', $thumbnail_id);
                echo "Success";
            } else {
                echo "Fail";
            }
            break;

        case "uploadify":

            echo "uploadify";

            function insert_attachment($file_handler, $post_id, $setthumb = 'false')
            {

                // check to make sure its a successful upload
                if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();

                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                require_once(ABSPATH . "wp-admin" . '/includes/file.php');
                require_once(ABSPATH . "wp-admin" . '/includes/media.php');

                $attach_id = media_handle_upload($file_handler, $post_id);

                if ($setthumb) update_post_meta($post_id, '_thumbnail_id', $attach_id);
                return $attach_id;
            }

            if (!empty($_FILES)) {
                $image_id = insert_attachment('Filedata', $_REQUEST['post_id']);
            }

            echo $image_id;

            break;

    }
    exit;
}


/*
* This function retrieve an array of images associated with the post
* The featured image has the property "featured"
*/

function vspi_get_images($post_id)
{
    if (has_post_thumbnail($post_id)) $thumbnail_id = get_post_thumbnail_id($post_id);
    $args = array(
        'post_type' => 'attachment',
        'post_parent' => $post_id,
        'numberposts' => -1,
        'post_status' => NULL
    );
    $attachments = get_posts($args);

    if (!isset($thumbnail_id)) $thumbnail_id = $attachments[0]->ID;

    $vspi_images = array();
    $i = 1;
    foreach ($attachments as $attachment) {
        $thumbnail = wp_get_attachment_image_src($attachment->ID, 'thumbnail');
        $medium = wp_get_attachment_image_src($attachment->ID, 'medium');
        $large = wp_get_attachment_image_src($attachment->ID, 'large');
        $full = wp_get_attachment_image_src($attachment->ID, 'full');
        $sc = wp_get_attachment_image_src($attachment->ID, 'vspi_thumb');

        $vspi_images[$i] = array(
            'id' => $attachment->ID,
            'large' => $large,
            'medium' => $medium,
            'thumbnail' => $thumbnail,
            'full' => $full,
            'sc' => $sc
        );
        if ($thumbnail_id == $attachment->ID) $vspi_images[$i]['thumb'] = true;
        $i++;
    }
    return $vspi_images;
}

function vspi_get_thumbs($post_id)
{
    if (has_post_thumbnail($post_id)) $thumbnail_id = get_post_thumbnail_id($post_id);
    $args = array(
        'post_type' => 'attachment',
        'post_parent' => $post_id,
        'numberposts' => -1,
        'post_status' => NULL
    );
    $attachments = get_posts($args);

    if (!isset($thumbnail_id)) $thumbnail_id = $attachments[0]->ID;

    $vspi_images = array();
    $i = 1;
    foreach ($attachments as $attachment) {
        $thumbnail = wp_get_attachment_image_src($attachment->ID, 'thumbnail');
        $vspi_images[$i] = array(
            'id' => $attachment->ID,
            'src' => $thumbnail[0],
            'width' => $thumbnail[1],
            'height' => $thumbnail[2]
        );
        if ($thumbnail_id == $attachment->ID) {
            $vspi_images[$i]['class'] = 'vspi_image vspi_is_featured_image';
        } else {
            $vspi_images[$i]['class'] = 'vspi_image';
        }
        $i++;
    }
    return $vspi_images;

}