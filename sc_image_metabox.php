<?php
/*
Plugin Name: SC Image Metabox
Plugin URI: http://www.simplecreativity.co.uk
Description: Image Metabox using uploadify
Version: 0.1
Author: Dave Tonge
Author URI: http://www.simplecreativity.co.uk
*/


function sc_image_admin_init()
{
    if (is_admin()) {
        global $current_user;
        get_currentuserinfo();
        define('SC_IMAGE_PLUGIN_PATH', plugins_url('sc_image_metabox'));
        add_meta_box("sc_image_metabox", "Images", "sc_image_metabox", "post", "normal", "high");
        wp_enqueue_script('swfobject');
        wp_enqueue_script('uploadify', SC_IMAGE_PLUGIN_PATH . '/uploadify/jquery.uploadify.v2.1.4.min.js', array('jquery'));
        wp_enqueue_script('sc_image_metabox', SC_IMAGE_PLUGIN_PATH . '/sc_image_metabox.js', array('jquery', 'uploadify'));
        wp_enqueue_style('sc_image_metabox', SC_IMAGE_PLUGIN_PATH . '/sc_image_metabox.css');
        wp_enqueue_script('jquery_tmpl', 'http://ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.min.js', array('jquery'));
        wp_enqueue_style('uploadify', SC_IMAGE_PLUGIN_PATH . '/uploadify/uploadify.css');
        wp_localize_script('sc_image_metabox', 'sc_image_globals',
                           array(
                                'ajax_url' => admin_url('admin-ajax.php'),
                                'sc_nonce' => wp_create_nonce('sc_nonce'),
                                'url' => SC_IMAGE_PLUGIN_PATH,
                                'user' => $current_user->ID
                           )
        );
    }
}

add_action("admin_init", "sc_image_admin_init");

function sc_image_metabox($callback)
{
    global $post;
    ?>

<input type="file" name="sc_image" id="sc_image"/>
<div id="sc_image_box" data-post_id="<?php echo $post->ID; ?>">
</div>
<div class="clearfix"></div>

<script id="sc_image_tmpl" type="text/x-jquery-tmpl">
    <div class="${class}" data-id="${id}">
        <a class="sc_delete" href="#">Delete</a>
        <a class="sc_thumb" href="#">Primary</a>
        <img src="${src}" width="${width}" height="${height}" alt="thumbnail" />
    </div>
</script>


<?php

}


add_action('wp_ajax_sc_image', 'sc_image_ajax');
add_action('wp_ajax_nopriv_sc_image', 'sc_image_ajax');

function sc_image_ajax()
{
    $sc_action = $_REQUEST['sc_action'];

    //Needed because flash doesn't show as logged in
    if ($sc_action === 'uploadify') {
        wp_set_current_user($_REQUEST['sc_user']);
    }
    check_ajax_referer('sc_nonce', '_wpnonce');

    switch ($sc_action) {

        case "reload":

            $post_id = $_REQUEST['id'];
            $response = sc_get_thumbs($post_id);
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

            function sc_check_thumb()
            {
                $postid = $_REQUEST['postid'];
                if (get_post_meta($postid, '_thumbnail_id')) return false;
                return true;
            }

            if (!empty($_FILES)) {
                $image_id = insert_attachment('Filedata', $_REQUEST['post_id'], sc_check_thumb());
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

function sc_get_images($post_id)
{
    if (has_post_thumbnail($post_id)) $thumbnail_id = get_post_thumbnail_id($post_id);
    $args = array(
        'post_type' => 'attachment',
        'post_parent' => $post_id,
        'numberposts' => -1,
        'post_status' => NULL
    );
    $attachs = get_posts($args);

    if (!isset($thumbnail_id)) $thumbnail_id = $attachs[0]->ID;

    $sc_images = array();
    $i = 1;
    foreach ($attachs as $att) {
        $thumbnail = wp_get_attachment_image_src($att->ID, 'thumbnail');
        $medium = wp_get_attachment_image_src($att->ID, 'medium');
        $large = wp_get_attachment_image_src($att->ID, 'large');
        $full = wp_get_attachment_image_src($att->ID, 'full');
        $sc = wp_get_attachment_image_src($att->ID, 'sc_thumb');

        $sc_images[$i] = array(
            'id' => $att->ID,
            'large' => $large,
            'medium' => $medium,
            'thumbnail' => $thumbnail,
            'full' => $full,
            'sc' => $sc
        );
        if ($thumbnail_id == $att->ID) $sc_images[$i]['thumb'] = true;
        $i++;
    }
    return $sc_images;
}

function sc_get_thumbs($post_id)
{
    if (has_post_thumbnail($post_id)) $thumbnail_id = get_post_thumbnail_id($post_id);
    $args = array(
        'post_type' => 'attachment',
        'post_parent' => $post_id,
        'numberposts' => -1,
        'post_status' => NULL
    );
    $attachs = get_posts($args);

    if (!isset($thumbnail_id)) $thumbnail_id = $attachs[0]->ID;

    $sc_images = array();
    $i = 1;
    foreach ($attachs as $att) {
        $thumbnail = wp_get_attachment_image_src($att->ID, 'thumbnail');
        $sc_images[$i] = array(
            'id' => $att->ID,
            'src' => $thumbnail[0],
            'width' => $thumbnail[1],
            'height' => $thumbnail[2]
        );
        if ($thumbnail_id == $att->ID) {
            $sc_images[$i]['class'] = 'sc_image sc_thumb';
        } else {
            $sc_images[$i]['class'] = 'sc_image';
        }
        $i++;
    }
    return $sc_images;

}