<?php 
/**
 * Plugin Name: Event Tracking for JavaScript errors.
 * Description: Uses David Walshes script for <a href="http://davidwalsh.name/track-errors-google-analytics">Error Event Tracking of JavaScript</a>.
 * Version: 1.0
 * Author: David Walsh, Adam Watson
 * Author URI: http://syntaxmansion.com
 * License: GPL2
 */


// Requires Google Analytics Event Tracking
// http://davidwalsh.name/track-errors-google-analytics

function sxm_enqueue_eventjs_track(){
	
	wp_enqueue_script(
		'sxm-js-eventerror-track',
		plugins_url( 'ga-event-error-track.js' , __FILE__ ),
		array(),
		true
	);

}


add_action( 'wp_enqueue_scripts', 'sxm_enqueue_eventjs_track' );