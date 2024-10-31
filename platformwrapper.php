<?php
/*
This Class provide wrapper methods over the WP API. The plugin will invoke these methods and not the WP API methods directly.
*/

if ( !defined('ABSPATH') )   {
	// include wp-config as it has the ABSPATH initialization
	$root = dirname(dirname(dirname(dirname(__FILE__))));
	if (file_exists($root.'/wp-load.php')) { 	// WP 2.6		
		require_once($root.'/wp-load.php');
	} else {	// Before 2.6		
		require_once($root.'/wp-config.php');
	}
}
// these are included as functions from within these are called in the wrapper / plug-in
require_once(ABSPATH . 'wp-includes/pluggable.php');
require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
require_once(ABSPATH . 'wp-includes/version.php');
require_once(ABSPATH . 'wp-includes/author-template.php');
if(strpos($wp_version,"2.0") !== FALSE)
{
	require_once(ABSPATH . 'wp-includes/comment-functions.php');
}
else
{
	require_once(ABSPATH . 'wp-includes/comment-template.php');
	require_once(ABSPATH . 'wp-includes/comment.php');
}

class platformwrapper
{
	/*
	This returns the instance of the platformwrapper. This function make sures that there is only one instance of platformwrapper in the application
	*/
	function getInstance()	{
		if($_this->instance == NULL)  {
			$_this->instance = new platformwrapper();
		}
		return $_this->instance;
	}

	function yk_get_var($sql_query){
		global $wpdb ;
		$wpdb->hide_errors();
		return $wpdb->get_var($sql_query) ;
	}

	function yk_get_results($sql_query){
		global $wpdb ;
		$wpdb->hide_errors();
		return $wpdb->get_results($sql_query, OBJECT) ;
	}

	function yk_get_row($sql_query){
		global $wpdb ;
		$wpdb->hide_errors();
		return $wpdb->get_row($sql_query) ;
	}

	function yk_query($sql_query){
		global $wpdb ;
		$wpdb->hide_errors();
		return $wpdb->query($sql_query) ;
	}

	function yk_num_rows($sql_query){
		global $wpdb ;
		$wpdb->query($sql_query);
		return $wpdb->num_rows ;
	}

	function yk_maybe_create_table($table_name, $table_ddl_sql){
		global $wpdb ;
		$wpdb->hide_errors();
		maybe_create_table($wpdb->$table_name, $table_ddl_sql);
	}

	function yk_hide_errors(){
		global $wpdb ;
		$wpdb->hide_errors();
	}

	function yk_show_errors(){
		global $wpdb ;
		$wpdb->show_errors();
	}

	function yk_get_settings($setting_name){
		return get_settings($setting_name);
	}

	function get_sz_blogID(){
		global $wpdb, $blog_id;	
		//If blog_id is not present in the posts table name, it is regular wordpress.
		if(strpos($wpdb->posts, $blog_id) === false)
			return 0;
		else
			return $blog_id;
	}

	function yk_get_comments_number($post_id){
		return get_comments_number($post_id);
	}

	function yk_add_admin_footer_action($callback_method_name){
		add_action("admin_footer", $callback_method_name);
	}

	function yk_add_admin_menu_action($callback_method_name){
		add_action("admin_menu", $callback_method_name);
	}

	function yk_add_activate_sezwho_action($callback_method_name){
		add_action('activate_sezwho/cpratecomments.php', $callback_method_name);
	}

	function yk_add_comment_post_action($callback_method_name){
		add_action('comment_post', $callback_method_name);
	}

	function yk_add_wp_head_action($callback_method_name){
		add_filter('wp_head', $callback_method_name);
	}

	function yk_add_comment_text_filter($callback_method_name){
		add_action('comment_text', $callback_method_name);
	}

	function yk_get_db_prefix(){
		global $wpdb;
		return $wpdb->prefix ;
	}

	function yk_is_comment_approved($comment_id){
		$comment_data = get_commentdata($comment_id , 1 , true);
		return $comment_data["comment_approved"] ;
	}

	function yk_get_comment_author_email($comment_id){
		$comment_data = get_commentdata($comment_id , 1 , true);
		return $comment_data["comment_author_email"] ;
	}

	function yk_get_comment_author_url($comment_id){
		$comment_data = get_commentdata($comment_id , 1 , true);
		return $comment_data["comment_author_url"] ;
	}

	function yk_get_comment_content($comment_id){
		$comment_data = get_commentdata($comment_id , 1 , true);
		return $comment_data["comment_content"] ;
	}

	function yk_get_comment_date($comment_id){
		$comment_data = get_commentdata($comment_id , 1 , true);
		return $comment_data["comment_date"];
	}

	function yk_get_post_id($comment_id){
		$comment_data = get_commentdata($comment_id , 1 , true);
		return $comment_data["comment_post_ID"] ;
	}

	function yk_get_post_title($post_id){
		$post = get_post($post_id);
		return substr($post->post_title, 0, 200);
	}
	
        function yk_get_post_url($post_id){
                return get_permalink($post_id);
        }

        function yk_get_post_date($post_id){
		$post = get_post($post_id);
		$post_date = $post->post_date;
		return $post_date;
                //return get_the_time('Y-m-d H:i:s');
        }

	function yk_get_plugin_version($sz_tp_check = false){
		//global $table_prefix;
		$sz_table_prefix = $this->yk_get_table_prefix();
		$sz_pluginversion = $this->yk_get_var("SELECT plugin_version from ".$sz_table_prefix."sz_site;");
		if (empty($sz_pluginversion) && true == $sz_tp_check){
			$sz_pluginversion = $this->yk_get_var("SELECT plugin_version from sz_site;");
		}
		return $sz_pluginversion;
	}

	function yk_update_plugin_version($pluginversion, $sitekey, $sz_tp_check = false ){
		//global $table_prefix;
		$sz_table_prefix = $this->yk_get_table_prefix();
		$sz_update_version = $this->yk_query("update ".$sz_table_prefix."sz_site set plugin_version ='$pluginversion'  where site_key ='$sitekey'");
		if (false == $sz_update_version && true == $sz_tp_check)
			$sz_update_version = $this->yk_query("update sz_site set plugin_version ='$pluginversion'  where site_key ='$sitekey'");
		return $sz_update_version;		
	}
	
	function yk_get_table_prefix(){
		global $table_prefix;
		if ('wp_' == $table_prefix )
			return '';
		else 
			return $table_prefix;
	}

	function yk_get_plugin_install_error(){
		return get_option("YKPLUGIN_INSTALL_ERR");
	}

	function yk_update_plugin_install_error($plugin_install_err){
		return update_option("YKPLUGIN_INSTALL_ERR" , $plugin_install_err);
	}

	function yk_get_blog_key(){
		return get_option("CPBLOGKEY");
	}

	function yk_update_blog_key($blog_key){
		return update_option("CPBLOGKEY" , $blog_key);
	}

	function yk_delete_blog_key($blog_key){
		return delete_option("CPBLOGKEY");
	}

	function yk_get_blog_name(){
		return get_option("blogname");
	}

	function yk_get_blog_description(){
		return get_option("blogdescription");
	}

	function yk_get_template(){
		return get_option("template");
	}

	function yk_get_blog_charset(){
		return get_option("blog_charset");
	}

	function yk_get_plugin_db(){
		return get_option("CPPLUGINDB");
	}

	function yk_get_categories($post_id){
		global $wpdb, $wp_version;
		$db_prefix = $wpdb->prefix ;
		$mvf = (float)substr($wp_version, 0,3);
		if ($mvf >= 2.3) { // for WP versions > 2.3 since the WP platform DB schema has changed
			$cat_query = "SELECT name as cat_name from ".$db_prefix."terms, ".$db_prefix."term_relationships, ".$db_prefix."term_taxonomy where ".$db_prefix."term_relationships.term_taxonomy_id = ".$db_prefix."term_taxonomy.term_taxonomy_id AND ".$db_prefix."term_taxonomy.term_id = ".$db_prefix."terms.term_id AND ".$db_prefix."term_relationships.object_id =  $post_id AND ".$db_prefix."terms.term_id > 2";
		} else {// for WP versions < 2.3
			$cat_query = "SELECT cat_name from ".$db_prefix."categories, ".$db_prefix."post2cat where cat_ID = category_id and post_id = $post_id;";
		}
		$cat_result = mysql_query($cat_query);
		$categories = "" ;
		$co = 0 ;
		while ($cat_result && $cat = mysql_fetch_assoc($cat_result)) {
			$categories.= $cat["cat_name"]."," ;
		}
		$categories = substr($categories, 0, strlen($categories) - 1);
		return $categories ;
	}

	function yk_get_option($key) {
		return get_option($key);
	}

	function yk_update_option($key, $value) {
		return update_option($key, $value);
	}

	function yk_get_widget_dynamic() {
		$wdfrmDB = get_option("sz_widget_dynamic");
		if (false === $wdfrmDB)
			return -1;
		else if ($wdfrmDB)
			return 1;
		else
			return 0;
	}

	function yk_set_widget_dynamic($value) {
		if(!$value)
			$value = 0;
		return update_option("sz_widget_dynamic", $value);
	}

	function yk_get_post_intro($post_id, $size=1000){
		$post = get_post($post_id);
		/* Try the excerpt, if available */
		$excerpt = $post->post_excerpt;
		if (($excerpt == null) || ('' == $excerpt)){
			$excerpt = $post->post_content;
		}
		/* Create a sized excerpt, get rid of markups beforehand */
		$excerpt = substr($excerpt, 0, $size);
		$badchr = array("\t", "\n", "\r", "\0", "\x0B");
		$c = trim(str_replace($badchr,"", $excerpt));
		$c = str_replace("  "," ", $c);
		
		return $c;
	}

	function yk_get_post_author_display_name($post_id=null){
		/* author_login is guaranteed to be present */
		if (empty($post_id)){
			return get_the_author_login();
		}else{
			$post = get_post($post_id);
			$author_id = $post->post_author;
			$author_data = get_userdata($author_id);
			if (!empty($author_data->display_name)){
				return $author_data->display_name;
			}else{
				return $author_data->user_login;
			}	
		}
	}

	function yk_get_post_author_email($post_id){
		$post = get_post($post_id);
		$author_id = $post->post_author;
		$author_data = get_userdata($author_id);
		return $author_data->user_email;
	}
	function yk_invoke_js_from_post ($post_id){
		global $wp_query;
		$wp_post = get_post($post_id);
		// only invoke js if this is the last post and there are no comments
		if (!(is_single() && isset($wp_post->comment_count) && $wp_post->comment_count) && 
		   ($wp_query->current_post + 1 == $wp_query->post_count))
			return true;
		return false;
	}
	function yk_get_post_comment_count($post_id){
		$post = get_post($post_id);
		return $post->comment_count;
	}
	
	function yk_get_paginated_params(&$start_comment_number, &$end_comment_number) {
		///////////////////////////////////////////////
		//if paginated comments plugin is installed
		///////////////////////////////////////////////
		$PdCs_Settings = get_option('PdCs_Settings');
		global $sz_options, $sz_comment_page_number;
		if($PdCs_Settings !== false && is_numeric($sz_comment_page_number) && $sz_options->sz_enable_comments_template==0) {
			if($PdCs_Settings['comments_pagination'] == "number") {
				$start_comment_number = ($sz_comment_page_number-1) * ($PdCs_Settings['comments_per_page']);
				$end_comment_number = $start_comment_number + $PdCs_Settings['comments_per_page'];
			}
		}
	}
	
	function yk_get_published_comments($blog_id, $post_id) {
		$comment_array = get_approved_comments($post_id);
		$sz_comment_array = array();
		$sz_comment_id_array = array();
		$sz_author_emails = array();
		$start_comment_number = 0;
		$end_comment_number = $total_comments = count($comment_array);
		$this->yk_get_paginated_params($start_comment_number, $end_comment_number);
		
		for($i=$start_comment_number; $i<$end_comment_number; $i++) {
			$comment = $comment_array[$i];
			$sz_comment = new SZComment();
			$sz_comment->comment_wp_id = $comment->comment_ID;
			$sz_comment->comment_author_email = $comment->comment_author_email;
			$sz_comment->comment_author_url = $comment->comment_author_url;
			$sz_comment->comment_author_wp_id = $comment->user_id;
			$sz_comment->comment_author_display_name = $comment->comment_author;
			if($comment->comment_ID)
				$sz_comment_array[] = $sz_comment;
			$sz_comment_id_array[] = $comment->comment_ID;
			if (array_key_exists($sz_comment->comment_author_email, $sz_author_emails) != true){
				$sz_author_emails[] = $sz_comment->comment_author_email;
			}
		}
		$this->setup_comment_rating_data($blog_id, $post_id, $sz_comment_array, $sz_comment_id_array);
		$this->setup_encoded_email_and_comment_user_score($sz_comment_array, $sz_author_emails);
		return $sz_comment_array;
	}
	function setup_comment_rating_data($blog_id, $post_id, &$sz_comment_array, $sz_comment_id_array){
		//global $table_prefix;
		$sz_table_prefix = $this->yk_get_table_prefix();	
		if (empty($sz_comment_array) || count($sz_comment_array) <= 0) {return;}
		$comment_ids = implode(",", $sz_comment_id_array);
		$comment_score_query = "SELECT comment_id, posting_id, comment_score, rating_count, anon_rating_count, anon_raw_score, creation_date FROM ".$sz_table_prefix."sz_comment WHERE posting_id = '$post_id' AND blog_id = '$blog_id' AND comment_id IN (".$comment_ids.") ORDER BY comment_id ASC; " ;
		$comment_scores = $this->yk_get_results($comment_score_query);
		foreach($comment_scores as $c){
			for($i=0; $i<count($sz_comment_array); $i++) {
				if ($c->comment_id == $sz_comment_array[$i]->comment_wp_id) {
					$sz_comment_array[$i]->sz_comment_score = $c->comment_score;
					$sz_comment_array[$i]->sz_comment_anon_score = empty($c->anon_raw_score)?0:$c->anon_raw_score;
					$sz_comment_array[$i]->sz_comment_creation_date = empty($c->creation_date)?0:$c->creation_date;
					$sz_comment_array[$i]->sz_comment_rating_count = (empty($c->rating_count)?0:$c->rating_count) 
							+ (empty($c->anon_rating_count)?0:$c->anon_rating_count);				
					break;
				}
			}	
		}
	}
	function setup_encoded_email_and_comment_user_score(&$sz_comment_array, $sz_author_emails){
		//global $table_prefix;	
		$sz_table_prefix = $this->yk_get_table_prefix();
		if (empty($sz_comment_array) || empty($sz_author_emails) 
			|| (count($sz_comment_array) <= 0) || (count($sz_author_emails) <= 0)) {return;}
		$csv = '';
		$sz_author_emails_uniq = array_unique($sz_author_emails);
		$em_count = count($sz_author_emails_uniq);
		$tag = 0;
		foreach($sz_author_emails_uniq as $e){
			$csv = $csv . "'" . $e . "'";	
			$tag++;
			if ($tag < $em_count) $csv .= ',';
		}

		$yk_score_query = "SELECT email_address, yk_score, encoded_email FROM ".$sz_table_prefix."sz_email WHERE email_address IN(".$csv.")";
		$karmas = $this->yk_get_results($yk_score_query);
		for($i=0; $i<count($sz_comment_array); $i++) {
			$em = $sz_comment_array[$i]->comment_author_email;
			foreach ($karmas as $karma){
				if (strcmp($em, $karma->email_address) == 0){
					$sz_comment_array[$i]->sz_comment_author_score = number_format($karma->yk_score, 1);
					$sz_comment_array[$i]->comment_author_email_encoded = $karma->encoded_email;
					break;
				}
			}
		}
	}
	function yk_get_published_post($blog_id, $post_id, &$sz_post){
		$post = get_post($post_id);
		if (empty($sz_post)){
			$sz_post = new SZPost();
		}
		$sz_post->post_wp_id = $post->ID;
		$sz_post->post_author_wp_id = $post->post_author;
		$sz_post->comment_count = $post->comment_count;
		if($this->yk_get_post_rating_data($blog_id, $post_id, $sz_post))	
			$this->yk_get_post_author_data($post_id, $sz_post);
		
	}
	function yk_get_post_author_data($post_id, &$sz_post){
		//global $table_prefix;
		$sz_table_prefix = $this->yk_get_table_prefix();
		$author_data = get_userdata($sz_post->post_author_wp_id);
		if (!empty($author_data->display_name)){
			$sz_post->post_author_display_name = $author_data->display_name;
		}else{
			$sz_post->post_author_display_name = $author_data->user_login;
		}
		$sz_post->post_author_email = $author_data->user_email;	
		$sz_post->post_author_url = $author_data->user_url;
		$yk_encoded_email_query = "SELECT yk_score, encoded_email FROM ".$sz_table_prefix."sz_email WHERE email_address = '".$sz_post->post_author_email."'";
		$row = $this->yk_get_row($yk_encoded_email_query);
		$sz_post->post_author_email_encoded = $row->encoded_email;
		$sz_post->sz_post_author_score = $row->yk_score;
	
	}
	function yk_get_post_rating_data($blog_id, $post_id, &$sz_post){
		//global $table_prefix;
		$sz_table_prefix = $this->yk_get_table_prefix();
		$blog_post_score_query = "SELECT post_score, anon_raw_score, rating_count, anon_rating_count FROM ".$sz_table_prefix."sz_post WHERE blog_id = ".$blog_id." AND posting_id = ".$post_id. " LIMIT 1";
		$row = $this->yk_get_row($blog_post_score_query);
		if(null == $row)
			return false;
		$sz_post->sz_post_score = $row->post_score;
		$sz_post->sz_post_anon_rating_count = $row->anon_rating_count;
		
		$sz_post->sz_post_anon_score = empty($row->anon_raw_score)?0:$row->anon_raw_score;
		$sz_post->sz_post_rating_count = (empty($row->rating_count)?0:$row->rating_count) + (empty($row->anon_rating_count)?0:$row->anon_rating_count);		
		$sz_post->sz_post_score_for_display = $this->applyAnonymousScore($sz_post->sz_post_anon_score, $sz_post->sz_post_score);
		return true;

	}
	function applyAnonymousScore($anon_raw_score, $regular_score)
	{
		$n = $anon_raw_score / 25;
			
		$regular_score += ( 1 - pow(0.5, $n) );
		
		if($regular_score > 10)
			$regular_score = 10;
		elseif($regular_score < 0)
			$regular_score = 0;
			
		return $regular_score;
	}

	
}


class SZComment {

	var $comment_wp_id;
	var $comment_author_email;
	var $comment_author_email_encoded;
	var $comment_author_url;
	var $comment_author_wp_id;
	var $comment_author_display_name;
	var $sz_comment_rating_count;
	var $sz_comment_anon_rating_count;
	var $sz_comment_anon_score;
	var $sz_comment_score;
	var $sz_comment_author_score;
	function SZComment(){
	}
}

class SZPost {
	
	var $post_wp_id;
	var $post_author_wp_id;
	var $post_author_display_name;
	var $post_author_url;
	var $post_author_email;
	var $post_author_email_encoded;
	var $comment_count;
	var $comments = array();		
	var $sz_post_rating_count;
	var $sz_post_anon_rating_count;
	var $sz_post_anon_score;
	var $sz_post_score;
	var $sz_post_score_for_display;
	var $sz_post_author_score;
	var $comments_loaded;

	function SZPost(){
	}
	
	function get_comment($comment_id){
		foreach($this->comments as $c){
			if ($c->comment_wp_id == $comment_id) return $c;
		}
	}
}

class SZOptions {
	var $sz_site_key;
	var $sz_blog_key;
	var $sz_plugin_version_string;
	var $sz_platform;
	var $sz_version;
	var $sz_theme_name;
	var $blog_wp_id;
	var $sz_auto_option_bar;
	var $sz_auto_comment;
	var $sz_enable_comment_rating;
//	var $sz_enable_comment_filter;
	var $sz_widgets_dynamic;
	var $sz_enable_post_rating;
	var $sz_enable_auto_layout;
	var $sz_enable_post_cmo_link;
	var $sz_cmo_text_anchor;
	var $sz_rating_text_anchor;
	var $sz_cmo_text_bracel;
	var $sz_cmo_text_bracer;
	var $sz_wp_db_prefix;
	
	var $sz_disable_profile_hover;
	var $sz_use_local_css_js;
	var $sz_local_css_file;
	var $sz_local_js_file;
	
	function SZOptions(){
	}
}
?>
