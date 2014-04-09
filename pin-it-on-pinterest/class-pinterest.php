<?php
class Pinterest
{

    private $url;

    public function __construct()
    {
        global $plugin_url;
        $this->url = $plugin_url;
        add_image_size('pinterest-thumb', 200, 200, true);
    }

    public function backend_css_js()
    {
        wp_enqueue_style('pinterest', $this->url . '/css/backend.css');
        wp_enqueue_script('pinterest', $this->url . '/js/backend.js', array('jquery'));
    }

    public function frontend_css_js()
    {
        wp_enqueue_script('pinterest', 'http://assets.pinterest.com/js/pinit.js',array(),'1.0',true);
    }


    public function add_meta_box()
    {
        add_meta_box('pinterest_meta', 'Pin This', array($this, 'pinterest_html'), 'post', 'normal', 'high');
    }

public function pinterest_html($post)
{
 
        $attachID=get_post_meta($post->ID,'_pin_image',true);
        $pin_desc=get_post_meta($post->ID,'_pin_description',true);
        $pin_image=wp_get_attachment_image(get_post_meta($post->ID,'_pin_image',true),'pinterest-thumb');
        if($pin_image){
                $bar=$pin_image.'<br /><div class="pin-actions"><a href="#">Remove Image</a></div>';
        }
 
        $html=wp_nonce_field('save_pinterest_options','_pin_field',true,false);
        $html.='
        <div id="pinterest-options">
                <div class="pin_option">
                        <label for="pin_description" class="full_label">Description:</label>
                        <textarea name="pin_description" id="pin_description">'.$pin_desc.'</textarea>
                </div>
                <div class="pin_clearer"></div>
                <div class="pin_option">
                        <div class="col1_2"><label for="pin_image">Image:</label></div>
                        <div class="col1_2"><input type="button" class="button-secondary" id="pin_add_image" value="Choose file to upload" /></div>
                </div>
                <div class="pin_clearer"></div>
                <div class="pin_image_container">
                        <input type="hidden" name="pin_image" id="pin_image" value="'.$attachID.'" />
                        '.$bar.'
                </div>
                <div class="pin_clearer"></div>
        </div>
        <div class="last_clearer"></div>';
        echo $html;
}

    public function get_pin_thumb()
    {
        echo wp_get_attachment_image($_POST['attachID'],'pinterest-thumb');
        die();
    }

    public function save_pin_options($postID){
        if(!wp_verify_nonce($_REQUEST['_pin_field'],'save_pinterest_options')) return $postID;
	    if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE) return $postID;
	    if('post'!=$_POST['post_type']) return $postID;

        update_post_meta($postID,'_pin_image',$_REQUEST['pin_image']);
        update_post_meta($postID,'_pin_description',$_REQUEST['pin_description']);
    }

    public function add_pin_excerpt($excerpt){
        global $post;
        $attachID=get_post_meta($post->ID,'_pin_image',true);
        $image=wp_get_attachment_image_src($attachID,'full');
        $description=get_post_meta($post->ID,'_pin_description',true);
        if($image) $pin='<a href="http://pinterest.com/pin/create/button/?url='.urlencode(get_permalink($post->ID)).'&media='.urlencode($image[0]).'&description='.urlencode($description).'" class="pin-it-button" count-layout="horizontal">Pin It</a>';
        else $pin='';
        return $excerpt.$pin;
    }


}

$P = new Pinterest;
add_action('add_meta_boxes', array($P, 'add_meta_box'));
add_action('admin_enqueue_scripts', array($P, 'backend_css_js'));
add_action('wp_ajax_getPinThumb',array($P,'get_pin_thumb'));
add_action('save_post',array($P,'save_pin_options'));

// FrontEnd
add_action('wp_enqueue_scripts',array($P,'frontend_css_js'));

add_filter('the_excerpt',array($P,'add_pin_excerpt'));
add_filter('the_content',array($P,'add_pin_excerpt'));

?>