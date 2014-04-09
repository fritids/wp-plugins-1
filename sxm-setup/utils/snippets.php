<?php
/**
 * Useful snippets for setting up a theme
 * Sauce: http://wordpress.stackexchange.com/questions/1567/best-collection-of-code-for-your-functions-php-file
*/


/* Register yourself as an admin, place in functions.php */
function setupAsUser(){

	$user_name = "awatson";
	$user_email = "adam@syntaxmansion.com";	
	$user_role = "administrator";
	$tmp_password = "ChangeMe!#";


	$user_id = username_exists( $user_name );
	
	if ( !$user_id and email_exists( $user_email ) == false ) {
		
		//$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
		$user_id = wp_create_user( $user_name, $tmp_password, $user_email );

		//Set user role to be admin.
		wp_update_user( array ( 'ID' => $user_id, 'role' => $user_role ) ) ;
	} 

}

//////////////////////////////////////////////////////
/////// CUSTOM ADMIN MENU LINK FOR ALL SETTINGS //////
//////////////////////////////////////////////////////

/*
 * This little piece of code does something pretty cool. 
 * It will add an additional option to your settings menu with a link to "all settings" 
 * which will show you a complete list of all the settings you have within your database 
 * related to your wordpress site. The code below will only made this link visible to an 
 * admin user and hide it for all other users.
*/

function all_settings_link() {
add_options_page(__('All Settings'), __('All Settings'), 'administrator', 'options.php');
}

add_action('admin_menu', 'all_settings_link');


//////////////////////////////////////////////////////
/////// Modify the Login Logo & Image URL Link ///////
//////////////////////////////////////////////////////
/**
 * This code will allow you to easily modify the WordPress Login page Logo as well as the href link and title text of this logo.
*/

add_filter( 'login_headerurl', 'namespace_login_headerurl' );
/**
 * Replaces the login header logo URL
 *
 * @param $url
 */
function namespace_login_headerurl( $url ) {
    $url = home_url( '/' );
    return $url;
}

add_filter( 'login_headertitle', 'namespace_login_headertitle' );
/**
 * Replaces the login header logo title
 *
 * @param $title
 */
function namespace_login_headertitle( $title ) {
    $title = get_bloginfo( 'name' );
    return $title;
}

add_action( 'login_head', 'namespace_login_style' );
/**
 * Replaces the login header logo
 */
function namespace_login_style() {
    echo '<style>.login h1 a { background-image: url( ' . get_template_directory_uri() . '/images/logo.png ) !important; }</style>';
}


///////////////////////////////////////////////////////////////////////////////
/////// Remove Update Notification for all users except ADMIN User ////////////
///////////////////////////////////////////////////////////////////////////////

/**
 * This code will ensures that no users other than "admin" are notified by wordpress when updates are available..
*/


// REMOVE THE WORDPRESS UPDATE NOTIFICATION FOR ALL USERS EXCEPT SYSADMIN
global $user_login;
get_currentuserinfo();

if (!current_user_can('update_plugins')) { // checks to see if current user can update plugins 
	add_action( 'init', create_function( '$a', "remove_action( 'init', 'wp_version_check' );" ), 2 );
	add_filter( 'pre_option_update_core', create_function( '$a', "return null;" ) );

}


///////////////////////////////////////////////////////////////////
/////// Remove the WordPress Version Info for Security ////////////
///////////////////////////////////////////////////////////////////

// remove version info from head and feeds
function complete_version_removal() {
    return '';
}
add_filter('the_generator', 'complete_version_removal');


//////////////////////////////////////////////////////////
/////// Customize the order of the admin menu ////////////
//////////////////////////////////////////////////////////


/*
 * This code will allow you to reorganize the order of elements in the admin menu. 
 * All that you need to do is click on an existing link in the admin menu and 
 * copy everything before the /wp-admin/ URL. The order below represents the order the new admin menu will have.
*/


// CUSTOMIZE ADMIN MENU ORDER
   function custom_menu_order($menu_ord) {
       if (!$menu_ord) return true;
       return array(
        'index.php', // this represents the dashboard link
        'edit.php?post_type=events', // this is a custom post type menu
        'edit.php?post_type=news', 
        'edit.php?post_type=articles', 
        'edit.php?post_type=faqs', 
        'edit.php?post_type=mentors',
        'edit.php?post_type=testimonials',
        'edit.php?post_type=services',
        'edit.php?post_type=page', // this is the default page menu
        'edit.php', // this is the default POST admin menu 
    );
   }
   add_filter('custom_menu_order', 'custom_menu_order');
   add_filter('menu_order', 'custom_menu_order');




//////////////////////////////////////////////////////
/////// Function to change the length of Exerpt //////
//////////////////////////////////////////////////////

/**
 * By default all excerpts are capped at 55 words. Utilizing the code below you can override this default settings:
*/
function new_excerpt_length($length) { 
    return 100;
}

add_filter('excerpt_length', 'new_excerpt_length');


//////////////////////////////////////////////////////////
/////// Add Thumbnails in Manage Posts/Pages List ////////
//////////////////////////////////////////////////////////

/** 
 * You can add this to your functions to display to the 
 * Manage/Edit Post and Pages List a new column with the thumbnail preview.
*/

/****** Add Thumbnails in Manage Posts/Pages List ******/
if ( !function_exists('AddThumbColumn') && function_exists('add_theme_support') ) {

    // for post and page
    add_theme_support('post-thumbnails', array( 'post', 'page' ) );

    function AddThumbColumn($cols) {

        $cols['thumbnail'] = __('Thumbnail');

        return $cols;
    }

function AddThumbValue($column_name, $post_id) {

        $width = (int) 35;
        $height = (int) 35;

        if ( 'thumbnail' == $column_name ) {
            // thumbnail of WP 2.9
            $thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
            // image from gallery
            $attachments = get_children( array('post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image') );
            if ($thumbnail_id)
                $thumb = wp_get_attachment_image( $thumbnail_id, array($width, $height), true );
            elseif ($attachments) {
                foreach ( $attachments as $attachment_id => $attachment ) {
                    $thumb = wp_get_attachment_image( $attachment_id, array($width, $height), true );
                }
            }
                if ( isset($thumb) && $thumb ) {
                    echo $thumb;
                } else {
                    echo __('None');
                }
        }
}




//////////////////////////////////////////////
/////// Remove pings to your own blog ////////
//////////////////////////////////////////////

//remove pings to self
function no_self_ping( &$links ) {
    $home = get_option( 'home' );
    foreach ( $links as $l => $link )
        if ( 0 === strpos( $link, $home ) )
            unset($links[$l]);
}

add_action( 'pre_ping', 'no_self_ping' );




//////////////////////////////////////////////
/////// Customize the Dashboard ////////
//////////////////////////////////////////////

add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');

function my_custom_dashboard_widgets() {
   global $wp_meta_boxes;

   //remove widgets
   unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
   unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
   unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);


   //add new widgets
   wp_add_dashboard_widget('custom_help_widget', 'Help and Support', 'custom_dashboard_help');

}


//////////////////////////////////////////////
/////// Sharpen Resized Images (only jpg) ////
//////////////////////////////////////////////

function ajx_sharpen_resized_files( $resized_file ) {

    $image = wp_load_image( $resized_file );
    if ( !is_resource( $image ) )
        return new WP_Error( 'error_loading_image', $image, $file );

    $size = @getimagesize( $resized_file );
    if ( !$size )
        return new WP_Error('invalid_image', __('Could not read image size'), $file);
    list($orig_w, $orig_h, $orig_type) = $size;

    switch ( $orig_type ) {
        case IMAGETYPE_JPEG:
            $matrix = array(
                array(-1, -1, -1),
                array(-1, 16, -1),
                array(-1, -1, -1),
            );

            $divisor = array_sum(array_map('array_sum', $matrix));
            $offset = 0; 
            imageconvolution($image, $matrix, $divisor, $offset);
            imagejpeg($image, $resized_file,apply_filters( 'jpeg_quality', 90, 'edit_image' ));
            break;
        case IMAGETYPE_PNG:
            return $resized_file;
        case IMAGETYPE_GIF:
            return $resized_file;
    }

    return $resized_file;
}   

add_filter('image_make_intermediate_size', 'ajx_sharpen_resized_files',900);


//////////////////////////////////////////////
/////// Enable GZIP output compression ////
//////////////////////////////////////////////
/**
 * Normally the server should be set up to do this automatically,
 * but a lot of shared hosts don t do this (probably to increase client bandwidth usage)
*/

if(extension_loaded("zlib") && (ini_get("output_handler") != "ob_gzhandler"))
   add_action('wp', create_function('', '@ob_end_clean();@ini_set("zlib.output_compression", 1);'));


/////////////////////////////////////////////////////////////////////////
/////// Display DB Queries, Time Spent and Memory Consumption ///////////
/////////////////////////////////////////////////////////////////////////

function performance( $visible = false ) {

    $stat = sprintf(  '%d queries in %.3f seconds, using %.2fMB memory',
        get_num_queries(),
        timer_stop( 0, 3 ),
        memory_get_peak_usage() / 1024 / 1024
        );

    echo $visible ? $stat : "<!-- {$stat} -->" ;
}

add_action( 'wp_footer', 'performance', 20 );



/////////////////////////////////////////////////////////////////////////
/////// Remove Plugin Update Notice ONLY for INACTIVE plugins ///////////
/////////////////////////////////////////////////////////////////////////


function update_active_plugins($value = '') {
    /*
    The $value array passed in contains the list of plugins with time
    marks when the last time the groups was checked for version match
    The $value->reponse node contains an array of the items that are
    out of date. This response node is use by the 'Plugins' menu
    for example to indicate there are updates. Also on the actual
    plugins listing to provide the yellow box below a given plugin
    to indicate action is needed by the user.
    */
    if ((isset($value->response)) && (count($value->response))) {

        // Get the list cut current active plugins
        $active_plugins = get_option('active_plugins');    
        if ($active_plugins) {

            //  Here we start to compare the $value->response
            //  items checking each against the active plugins list.
            foreach($value->response as $plugin_idx => $plugin_item) {

                // If the response item is not an active plugin then remove it.
                // This will prevent WordPress from indicating the plugin needs update actions.
                if (!in_array($plugin_idx, $active_plugins))
                    unset($value->response[$plugin_idx]);
            }
        }
        else {
             // If no active plugins then ignore the inactive out of date ones.
            foreach($value->response as $plugin_idx => $plugin_item) {
                unset($value->response);
            }          
        }
    }  
    return $value;
}
add_filter('transient_update_plugins', 'update_active_plugins');    // Hook for 2.8.+
//add_filter( 'option_update_plugins', 'update_active_plugins');    // Hook for 2.7.x



//////////////////////////////////////////////////////////////////////////////////////
/////// Output which theme template file a post/page is using in the header //////////
//////////////////////////////////////////////////////////////////////////////////////

add_action('wp_head', 'show_template');
function show_template() {
    global $template;
    print_r($template);
}



//////////////////////////////////////////////////////////////////////////////////////
/////// Shorten the default DIV output if your theme is using post_class. //////////
//////////////////////////////////////////////////////////////////////////////////////

/*
 if your theme is using something like
 <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

 You can have crazy long divs in your source that might look like this or even longer:
 <div id="post-4" class="post-4 post type-post hentry category-uncategorized category-test category-test-1-billion category-test2 category-test3 category-testing"> 


*/

// slice crazy long div outputs
// this slices the output to only include the first 5 values, so the above example becomes:
function category_id_class($classes) {
    global $post;
    foreach((get_the_category($post->ID)) as $category)
        $classes[] = $category->category_nicename;
        return array_slice($classes, 0,5);
}
add_filter('post_class', 'category_id_class');




//////////////////////////////////////////////////////////////////////////////////////
/////// Shorten the default DIV output if your theme is using post_class. //////////
//////////////////////////////////////////////////////////////////////////////////////
/**
 * Drop this code in your wp-config.php file ( AFTER YOU SAVE A BACKUP JUST IN CASE ) 
 * and then you can pass the ?debug=1, 2, or 3 parameters at the end of any url on your site.
 * ?debug=1 = shows all errors/notices 
 * ?debug=2 = forces them to be displayed
 * ?debug=3 = creates a debug.log file of all errors in /wp-content dir.
*/

/**
* Written by Jared Williams - http://new2wp.com
* @wp-config.php replace WP_DEBUG constant with this code
* Enable WP debugging for usage on a live site
* http://core.trac.wordpress.org/browser/trunk/wp-includes/load.php#L230
* Pass the '?debug=#' parameter at the end of any url on site
*
* http://example.com/?debug=1, /?debug=2, /?debug=3
*/
if ( isset($_GET['debug']) && $_GET['debug'] == '1' ) {
    // enable the reporting of notices during development - E_ALL
    define('WP_DEBUG', true);
} elseif ( isset($_GET['debug']) && $_GET['debug'] == '2' ) {
    // must be true for WP_DEBUG_DISPLAY to work
    define('WP_DEBUG', true);
    // force the display of errors
    define('WP_DEBUG_DISPLAY', true);
} elseif ( isset($_GET['debug']) && $_GET['debug'] == '3' ) {
    // must be true for WP_DEBUG_LOG to work
    define('WP_DEBUG', true);
    // log errors to debug.log in the wp-content directory
    define('WP_DEBUG_LOG', true);
}


/////////////////////////////////////////////////
/////// Enable shortcodes in widgets. //////////
////////////////////////////////////////////////

// shortcode in widgets
if ( !is_admin() ){
    add_filter('widget_text', 'do_shortcode', 11);
}



/////////////////////////////////////////////////
/////// Function to Disable RSS Feeds //////////
////////////////////////////////////////////////

function fb_disable_feed() {
wp_die( __('No feed available,please visit our <a href="'. get_bloginfo('url') .'">homepage</a>!') );
}

add_action('do_feed', 'fb_disable_feed', 1);
add_action('do_feed_rdf', 'fb_disable_feed', 1);
add_action('do_feed_rss', 'fb_disable_feed', 1);
add_action('do_feed_rss2', 'fb_disable_feed', 1);
add_action('do_feed_atom', 'fb_disable_feed', 1);


/////////////////////////////////////////////////////////////////////
/////// Add a codex search form to the dashboard header /////////////
/////////////////////////////////////////////////////////////////////


/**
 * This is a simple way to add a codex search form to the dashboard header,
 * on the top-right next to the quicklinks drop-down.
*/

/**
 * ADD WP CODEX SEARCH FORM TO DASHBOARD HEADER
 */
function wp_codex_search_form() {
    echo '<form target="_blank" method="get" action="http://wordpress.org/search/do-search.php" class="alignright" style="margin: 11px 5px 0;">
        <input type="text" onblur="this.value=(this.value==\'\') ? \'Search the Codex\' : this.value;" onfocus="this.value=(this.value==\'Search the Codex\') ? \'\' : this.value;" maxlength="150" value="Search the Codex" name="search" class="text"> <input type="submit" value="Go" class="button" />
    </form>';
}

if( current_user_can( 'manage_plugins' )) {
// The number 11 needs to be a 10 for this to work!
    add_filter( 'in_admin_header', 'wp_codex_search_form', 11 );
}












