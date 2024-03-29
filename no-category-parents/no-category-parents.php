<?php
/*
Plugin Name: No Category Parents
Description: Removes category parents from your category permalinks. Now it also works for the posts permalinks, when using the /%category%/ permastruct.
Version: 0.2.3
Author: <a href="http://www.milardovich.com.ar/donate/">Sergio Milardovich</a>
Author URI: http://www.milardovich.com.ar/no-category-parents/
Donate link: http://www.milardovich.com.ar/donate/
*/


/*  
    Based on "WP No Category Base" code -> http://wordpresssupplies.com/

    Copyright 2009-2012  Sergio Milardovich  (email : smilardovich@frro.utn.edu.ar)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


add_filter ("pre_post_link", "filter_category");			// will apply to post permalink
add_filter ("user_trailingslashit", "myfilter_category");


add_filter ("category_link", "filter_category_link");			// will apply to post permalink


add_filter( 'rewrite_rules_array','my_insert_rewrite_rules' );
add_filter( 'query_vars','my_insert_query_vars' );
add_action( 'wp_loaded','my_flush_rules' );

// seems category filters are not working
add_action('created_category','my_flush_rules2');
add_action('edited_category','my_flush_rules2');
add_action('delete_category','my_flush_rules2');

// flush_rules() if our rules are not yet included
function my_flush_rules(){
	update_option('category_base','');
	$rules = get_option( 'rewrite_rules' );

	//if ( ! isset( $rules['(.+?)-cat/?$'] ) ) { // have to comment this in order to refresh the rules
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	//}
}

function my_flush_rules2(){
	$rules = get_option( 'rewrite_rules' );

		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
}

// Adding a new rule
function my_insert_rewrite_rules( $rules )
{
	$newrules = array();
	$newrules['(.+?)-cat/?$'] = 'index.php?category_name=$matches[1]';
	$newrules['(.+?)-cat/page/?([0-9]{1,})/?$'] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
 	
	$categories = get_categories(array('hide_empty'=>false));
	
	
	if ($categories)
	{
		foreach ($categories as $key => $val)
		{
			$posts = get_posts (array("name" => $val->slug));		
			if (!$posts)
			{
				$newrules['('.$val->category_nicename.')/?$'] = 'index.php?category_name=$matches[1]';
				$newrules['('.$val->category_nicename.')/page/?([0-9]{1,})/?$'] = 'index.php?category_name=$matches[1]&paged=$matches[2]';

				$newrules['.+?/('.$val->category_nicename.')/?$'] = 'index.php?category_name=$matches[1]';
				$newrules['.+?/('.$val->category_nicename.')/page/?([0-9]{1,})/?$'] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
			}
		}
	}

	return $newrules + $rules;
}

function my_insert_query_vars( $vars )
{
    array_push($vars, 'id');
    return $vars;
}


//add_filter('request', 'mycategory_rewrite_rules');

function mycategory_rewrite_rules() {
	global $wp_rewrite;
	
	echo "<pre>";
	print_r ($wp_rewrite);
	echo "</pre>";
	
//	[(.+?)/?$] => index.php?category_name=$matches[1]

}


function filter_category_link ($termlink)
{
	if (preg_match ("/\?cat=/", $termlink))
		return $termlink;
	
	
	$str = explode("/", $termlink);
	
	$myslug = $slug = $str[count($str)-2];
	
	// check if category slug exist in post
	
	$posts = get_posts (array("name" => $slug));		
	preg_match ("/category.*?".$myslug."/", $termlink, $result);
	
	if ($posts)
		$slug .= "-cat";

	$str = explode("/", $result[0]);
	
	if (count($str) > 3)
		$link = $str[count($str)-2]."/".$slug ;		
	else
		$link = $slug;

	$termlink = preg_replace ("/category.*?".$myslug."/", $link, $termlink);
	
	return $termlink;	
}


function filter_category ($permalink)
{
	$permalink = str_replace ("%category%", "%mycategory%", $permalink); 
	
	return $permalink;
}
	
function myfilter_category ($string)
{
	if (preg_match ("/%mycategory%/", $string))
	{
		$str = explode("/", $string);
		$slug = $str[count($str)-2];
		
		$posts = get_posts (array("name" => $slug));
		
		$cats = get_the_category($posts[0]->ID);
		
		if ( $cats ) {
			usort($cats, '_usort_terms_by_ID'); 
			$category = $cats[0]->slug;
			if ( $parent = $cats[0]->parent )
			{
				$one = 1;
			}
		}
		
		$string = preg_replace("/%mycategory%/", $category, $string);		
	}
	
	return $string;	
}
?>
