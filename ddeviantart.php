<?php
/*
Plugin Name: dDeviantArt
Version: 0.6
Description: Allows you to insert DeviantArt Deviations into your Blog. <a href="options-general.php?page=ddeviantart/dda_options.php">Options page</a>
Author: Dion Hulse

Author URI: http://dd32.id.au/
Plugin URI: http://dd32.id.au/wordpress-plugins/ddeviantart/
Update URI: http://dd32.id.au/pluginupdate/dDeviantArt/
*/

add_action('admin_menu', 'dda_admin_init');
/**
 * Admin init. Add admin hooks and menu items.
 *
 * @param void
 * @return void
 */
function dda_admin_init(){
	global $pagenow,$wp_version;
	add_options_page('dDeviantArt', 'DeviantArt', 'administrator', 'ddeviantart/dda_options.php');

	if( version_compare( $wp_version, '2.3-alpha', '>=') ){
		//2.3 introduces print_scripts- so we'll use that instead.
		add_action('admin_print_scripts-ddeviantart/dda_options.php', 'dda_admin_head');
	} else {
		//If the correct page, we'll want to add a head entry.
		if( 'options-general.php' == $pagenow && isset( $_GET['page'] ) &&
			'ddeviantart/dda_options.php' == $_GET['page'])
				dda_admin_head();
		//Due to the register_activation_hook being broken on windows < 2.3
		if( false !== strpos(__FILE__, '\\') )
			dda_flush_rules();
	}
}
/**
 * Admin Head, addd jQuery, Deregister prototype as its unneeded on our pages.
 *
 * @param void
 * @return void
 */
function dda_admin_head(){
	wp_enqueue_script('jquery');
	wp_deregister_script('prototype'); //Deregister the prototype script so that it cant be used while jQuery is loaded; Allows use of $()
}

/*** Widget support ***/
add_action('widgets_init', 'dda_widget_init');
/**
 * Widget Init. Register all sidebar widgets
 *
 * @param void
 * @return void
 */
function dda_widget_init(){
	if ( ! function_exists('register_sidebar_widget') )
		return;
		
	$widgets = get_option('dda_widgets');
	foreach((array)$widgets as $widget){
		register_sidebar_widget($widget['name'], 'dda_widget', $widget);
	}
}

/**
 * Display a Sidebar widget
 *
 * @param array $args mixed Options for display
 * @param array $widget widget options
 * @return void
 */
function dda_widget($args,$widget){
	if( ! isset($widget['feed']) )
		return;

	extract($args,EXTR_SKIP);
	
	echo $before_widget;
	echo $before_title . $widget['name'] . $after_title;
		dda_display_widget($widget);
	echo $after_widget;
}
/**
 * Display a widget
 *
 * @param array $widget widget options
 * @return void
 */
function dda_display_widget($widget){
	$items = dda_get_feed_items($widget);
	if( ! $items )
		return;
	if( $widget['random'] == '1' )
		shuffle($items);

	if( $widget['numbertoshow'] > 0 && count($items) > $widget['numbertoshow'] )
		$items = array_slice($items, 0, $widget['numbertoshow']);
			
	foreach($items as $item){
		//If no Custom HTML specified, use default HTML.
		if( empty($widget['html']) ){
			echo '<a target="_blank" href="'.$item['link'].'" title="' . attribute_escape( $item['title'] . ' by ' . $item['symbol'] . $item['username'] . ' in '.$item['category']) .'">';
			if( !empty($item['imagelink']) ){
				echo '<img src="'.$item['imagelink'].'" alt="'.attribute_escape($item['title']).'"/>';
			} else {
				echo $item['title'];
			}
			echo '</a><br />';
		} else {
			$find = array('%id%','%link%','%category%','%image%','%title%','%author%');
			$replace = array($item['guid'], $item['link'],
							$item['category'], $item['imagelink'],
							$item['title'], $item['author']);
			echo str_replace($find, $replace, stripslashes($widget['html']));
		}//end if()
	}//end foreach()
}

/**
 * Display a widget via PHP Embed
 *
 * @param string $name the name of the Widget to embed, Defaults to first widget
 * @return void
 */
function dda_widget_embed($name = false){
	$widgets = get_option('dda_widgets');
	$name = sanitize_title_with_dashes($name); //Generate Slug
	foreach((array)$widgets as $widget){
		if( ! $name ){ //Display the first one
			dda_display_widget($widget);
			break;
		} elseif( sanitize_title_with_dashes($widget['name']) == $name){
			dda_display_widget($widget);
			break;
		}
	}
}

/*** General Functions ***/
/**
 * Deletes any Cached Feed items (option name = dda_<md5>
 *
 * @global $wpdb Wordpress DataBase object
 * @param boolean $uninstall Set to true to remove all plugin settings.
 * @return void
 */
function dda_delete_cache($uninstall = false){
	global $wpdb;
	
	$noncache = array(	'dda_disableinlinethumbnails',
						'dda_hotlink',
						'dda_inlineclass',
						'dda_inlineize',
						'dda_inlinelink',
						'dda_inlinesize',
						'dda_inlinethumbnails',
						'dda_portfolios',
						'dda_portfolio_pages',
						'dda_timeout',
						'dda_widgets'); //We dont want to delete user settings.
	
	$sql = "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'dda_%'";
	
	if( ! $uninstall )
		$sql .= " AND option_name NOT IN ('" . implode("','",$noncache) . "')";
	
	$items = $wpdb->get_col( $sql );

	foreach( (array) $items as $item)
			delete_option($item);
	
	if( ! $uninstall )
		add_option('dda_imageaddresses', array() );
	
	return true;
}
/**
 * Retrieves the feed items from DeviantArt and caches them.
 *
 * @param array $widget widget options
 * @return array of processed items
 */
function dda_get_feed_items($widget){
	$option = 'dda_' . md5(serialize($widget));
	
	$items = get_option($option);
	if( isset($items['time']) && /* Backwards compatible with < 0.5 */
		$items['time'] + get_option('dda_timeout') < time() )
		return $items['items'];
	
	include_once (ABSPATH . 'wp-includes/rss.php');

	$rss = fetch_rss($widget['feed']);
	$items = dda_process_feed_items($rss->items, $widget);
	update_option($option, array('time' => time(), 'items' => $items) );
	return $items;
}
/**
 * Processes each Feed item
 *
 * @param array $items array of raw RSS items
 * @param array $widget widget options
 * @return array processed Items
 */
function dda_process_feed_items($items, $widget){
	for($i=0; $i < count($items); $i++){
		$result = dda_deviation_url($items[$i]['guid'], $widget['size']);

		if( ! result ){
			$items[$i] = null;
			unset($items[$i]);
			continue;
		} else {
			//Set the URL
			$items[$i]['imagelink'] = $result;

			//Determine the authors name from the image url:
			if( preg_match('#_by_(.*).jpg$#', $result, $mat) ){
				$items[$i]['author'] = $mat[1];
			}
		}
		if( 0 < $widget['numbertoshow'] && 
			'1' != $widget['random'] &&
			$i >= $widget['numbertoshow'])
				return array_slice($items, 0, $i);
	}//end for
	return $items;
}
/**
 * Converts a Deviation ID and size into a image URL
 *
 * @param integer $id Deviation ID
 * @param integer $size integer the size of the deviation, defaults to 150px
 * @return string $url URL of Deviation thumbnail
 */
function dda_deviation_url($id, $size=150){
	$urls = get_option('dda_imageaddresses');
	//Poor mans caching, Not using WP Object cache due to never being enabled.
	if ( isset($urls[ $id . '-' . $size ]) )
		return $urls[ $id . '-' . $size ];

	include_once( ABSPATH . 'wp-includes/class-snoopy.php' );
	$snoopy = new Snoopy();
	$snoopy->agent = $_SERVER['HTTP_USER_AGENT']; //Needs to be a valid Browser, DA block non-browsers
	$snoopy->maxredirs = 0; //Dont follow redirects
	$snoopy->_httpmethod = "HEAD"; //Dont want the image, just the headers
	//This URL is used by the Flash-embedable link.
	$snoopy->fetch('http://www.deviantart.com/global/getthumb.php?size=' . $size . '&id=' . $id );

	$url = $snoopy->_redirectaddr;
	if( empty($url) ){
		//This code branch is most likely not needed, but will not be executed unless needed anyway
		//Locate the redirection header, this is the location of the image to display
		foreach($snoopy->headers as $head){
			if( substr($head,0,8) == 'Location' ){
				$url = trim(substr($head,9));
				break;
			}
		}
	}//end empty(url);
	
	if( empty($url) )
		return false; //Could not find it.

	$urls[ $id . '-' . $size ] = $url;

	update_option('dda_imageaddresses',$urls);
	return $url;
}

/*** Inline thumbnail support ***/
if( ! get_option('dda_disableinlinethumbnails') )
	add_filter('the_content', 'dda_inline');
/**
 * Replaces the :thumb555555: codes in posts/pages to a Deviation URL
 *
 * @param string $post the content of the post
 * @return string $post
 */
function dda_inline($post){
	$post = preg_replace_callback('#:thumb(\d+):((\d+)?:)?((\w+):)?#i', 'dda_inline_callback', $post);
	return $post;
}
/**
 *  Replaces the :thumb555555: codes in posts/pages to a Deviation URL, Does the hard lifting
 *
 * @param mixed $matches array of matched thumburls and extra options
 * @return string $ret the HTML to replace with
 */
function dda_inline_callback($matches){
	//Defaults
	$class = get_option('dda_inlineclass');
	$size = get_option('dda_inlinesize');

	$id = $matches[1];
	if( isset($matches[3]) && !empty($matches[3]) ) $size = $matches[3];
	if( isset($matches[5]) && !empty($matches[5]) ) $class = $matches[5];
	if( '300' == $size ) $size .= 'W';

	$imgurl = dda_deviation_url($id, $size);
	$img = "<img src='$imgurl' class='$class deviation' />";

	if( get_option('dda_inlinelink') ){
		if( ! $imgurl )
			return sprintf("<a href='http://www.deviantart.com/deviation/$id/' target='_blank'>%s $id</a>",__('View Deviation'));
		return "<a href='http://www.deviantart.com/deviation/$id/' target='_blank'>$img</a>";
	}
	if( $imgurl )
		return $img;
	else
		return '';
}

/*** Portfolio stuff ***/

add_action('template_redirect','dda_template',9);
/**
 * Redirects the template if the user has landed on a DA portfolio page
 *
 * @param string $arg the current template
 * @return string $arg the current template
 */
function dda_template($arg){
	global $wp_query;
	if( !isset($wp_query->query_vars['ddeviantart']) )
		return $arg;
	$wp_query->is_404 = false;
	// we're wanting to display a ddeviantart page, We dont do this here, we'll override the template for this page instead
	add_filter('home_template', 'dda_template_override');
	return $arg;
}
/**
 * Redirects the template to dda custom template
 *
 * @param string $template the current template (unused)
 * @return string dda custom template
 */
function dda_template_override($template){
	//Override the template for the current page and replace it with our own.
	return dirname(__FILE__).'/dda_portfolio.php';
}

if( get_option('dda_portfolio_pages') )
	add_action('get_pages', 'dda_portfolio_pages');

/**
 * Inserts the dda portfolio pages into the page listing
 *
 * @param array $pages The pages objects
 * @return array $pages
 */
function dda_portfolio_pages($pages){

	$portfolios = get_option('dda_portfolios');
	if( empty($portfolios) || ! is_array($portfolios) )
		return $pages;

	$url = get_bloginfo('siteurl');
	$url = trailingslashit($url);

	foreach( (array) $portfolios as $id=>$portfolio){
		$page = new stdClass;
		$page->post_title = $portfolio['name'];
		$page->post_name = $portfolio['slug'];
		//$page->guid = $url . $portfolio['slug'] . ($permalinks ? '/' : '');
		$page->ID = ( $id == 0 ) ? -1 : -($id+1); /* we use ID's starting at -1, shouldnt conflict with anything. */
		$pages[] = $page;
	}
	return $pages;
}

/**
 * Modifies the page link to ?ddeviantart for the negitive numbers
 *
 * @param string $link the current link
 * @param int $id the id of the current link
 * @return string $link the new link
 */
 add_filter('_get_page_link', 'dda_portfolio_pages_link',10,2);
function dda_portfolio_pages_link($link, $id=1){
	global $wp_rewrite;
	if($id > 0)
		return $link;

	$portfolios = get_option('dda_portfolios');
	$portfolio = $portfolios[ -($id)-1 ];

	if( ! empty($portfolio) ){
		if( ! $wp_rewrite || ! $wp_rewrite->using_permalinks() )
			$link = preg_replace('!\?p\w*=-\d+!', '?ddeviantart=' . $portfolio['slug'], $link);
		else
			$link .= $portfolio['slug']  . '/';
	}
	return $link;
}

/*** URL REWRITE STUFF ***/

//If the rewrite rules are regenerated, Add our pretty permalink stuff, redirect it to the correct queryvar
add_action('generate_rewrite_rules', 'dda_add_rewrite_rules');
/**
 * Adds rewrite rules for pretty permalinks
 *
 * @param object $wp_rewrite the Rewrite object
 * @return void
 */
function dda_add_rewrite_rules( $wp_rewrite ) {
	$pages = get_option('dda_portfolios');
	if( empty($pages) )
		return;
	
	$pageRegex = '';
	foreach((array)$pages as $page){
		if( empty($pageRegex) )
			$pageRegex = $page['slug'];
		else
			$pageRegex .= '|' . $page['slug'];
			
	}

	$new_rules = array( "($pageRegex)" => 'index.php?ddeviantart=' . $wp_rewrite->preg_index(1) );

	$wp_rewrite->rules = $wp_rewrite->rules + $new_rules;
}

//Add a Query Var, This allows us to access the query var via $wp_query
add_filter('query_vars', 'dda_queryvars' );
/**
 * Adds the query vars pretty permalinks
 *
 * @param object $qvars the current Query vars
 * @return void
 */
function dda_queryvars( $qvars ){
	$qvars[] = 'ddeviantart';
	return $qvars;
}


register_activation_hook(__FILE__,'dda_flush_rules');
/**
 * Flushes the rewrite rules causing them to be recreated on a plugin activation.
 *
 * @param void
 * @return void
 */
function dda_flush_rules(){
	//Flush the rewrite rules so that the new rules from this plugin get added.
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
	dda_delete_cache();
}

?>