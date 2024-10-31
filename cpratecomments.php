<?php
/*
Plugin Name: SezWho
Plugin URI: http://SezWho.com/
Description: A plugin that improves community engagement by showing universal web-wide profiles for participants and by empowering the community to rate comments and post. You need a key to use this service. Get your key at <a href="http://SezWho.com/register.php">SezWho</a>.
Author: SezWho
Version: WP2.2
Author URI: http://SezWho.com
*/

/*
1. Unzip cpratecomments.zip
2. Upload the folder to your wp-content/plugins directory
3. activate via wp-admin
4. For further details on installation please visit http://sezwho.com/install_wp.php

*/


ini_set("display_errors" , false);
ini_set('allow_url_fopen', 'On');

if(isset($_REQUEST['action']) && "addblog"==$_REQUEST['action'])
	return;

include_once(ABSPATH.'wp-content/plugins/sezwho/cpconstants.php');
global $cpserverurl, $cpjsserverurl, $cpcssserverurl, $cppluginurl, $platformwrapper, $sz_comment_num, $sz_comment_iteration_num ,$sz_blog_id , $wpdb, $sz_global_script_string, $sz_comment_id, $comment_author_email_enc, $sz_user_score, $sz_comment_score, $sz_comment_rating_count, $sz_auto_option_bar, $sz_auto_comment, $sz_comment_author_url, $sz_comment_creation_date, $sz_widgets_dynamic, $sz_global_img_repo_arr, $sz_comment_page_number;
$sz_auto_option_bar = 0;
$sz_auto_comment = 0;
$sz_comment_id = 0;
$sz_comment_iteration_num = -1 ;
$sz_global_script_string = "";
$sz_global_img_repo_arr = array();
$sz_comment_page_number = 1;
global $sz_options, $sz_post;
$sz_global_comment_id_array = array();
// this will create the yk plugin schema when the plugin is activated
$platformwrapper->yk_add_activate_sezwho_action('yk_plugin_create_schema');
$sz_blog_id = $platformwrapper->get_sz_blogID(); 

function yk_plugin_create_schema () {
	global $platformwrapper, $cpserverurl, $pluginversion, $jstagname, $existingpluginversion, $siteurl;
	global $sz_table_prefix;
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

	$sz_enable_comment_rating = $platformwrapper->yk_get_option("sz_enable_comment_rating");
	if(false === $sz_enable_comment_rating) {	//Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_enable_comment_rating", "1");
	}

	$sz_enable_post_rating = $platformwrapper->yk_get_option("sz_enable_post_rating");
	if(false === $sz_enable_post_rating) {	//Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_enable_post_rating", "1");
	}

	$sz_enable_auto_layout = $platformwrapper->yk_get_option("sz_enable_auto_layout");
	if(false === $sz_enable_auto_layout) {	//Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_enable_auto_layout", "1");
	}

	$sz_enable_auto_layout_post = $platformwrapper->yk_get_option("sz_enable_auto_layout_post");
	if(false === $sz_enable_auto_layout_post) {	//Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_enable_auto_layout_post", "1");
	}

	$sz_enable_post_cmo_link = $platformwrapper->yk_get_option("sz_enable_post_cmo_link");
	if(false === $sz_enable_post_cmo_link) {  //Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_enable_post_cmo_link", "0");
	}

	$sz_cmo_text_anchor = $platformwrapper->yk_get_option("sz_cmo_text");
	if(false === $sz_cmo_text_anchor) {  //Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_cmo_text", "Who am I?");
	}

	$sz_rt_text_anchor = $platformwrapper->yk_get_option("sz_rating_text");
	if(false === $sz_rt_text_anchor) {  //Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_rating_text", "Rate this");
	}

	$sz_cmo_text_bracel = $platformwrapper->yk_get_option("sz_cmo_text_bracel");
	if(false === $sz_cmo_text_bracel) {  //Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_cmo_text_bracel", "(");
	}

	$sz_cmo_text_bracer = $platformwrapper->yk_get_option("sz_cmo_text_bracer");
	if(false === $sz_cmo_text_bracer) {  //Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_cmo_text_bracer", ")");
	}
	
	$sz_disable_profile_hover = $platformwrapper->yk_get_option("sz_disable_profile_hover");
	if(false === $sz_disable_profile_hover) {  //Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_disable_profile_hover", "0");
	}
	
	$sz_enable_comments_template = $platformwrapper->yk_get_option("sz_enable_comments_template");
	if(false === $sz_enable_comments_template) {  //Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_enable_comments_template", "0");		
	}

	$sz_enable_comment_filter = $platformwrapper->yk_get_option("sz_enable_comment_filter");
	if(false === $sz_enable_comment_filter) {	//Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_enable_comment_filter", "0");
	}
	
	$sz_use_local_css_js = $platformwrapper->yk_get_option("sz_use_local_css_js");
	if(false === $sz_use_local_css_js) {  //Variable hasn't been set, setting now.
		$platformwrapper->yk_update_option("sz_use_local_css_js", "0");
	}
	
	$sz_comment_theme = $platformwrapper->yk_get_option("sz_comment_theme");
	if(false === $sz_comment_theme) {  //Variable hasn't been set, setting now. 		
		$platformwrapper->yk_update_option('sz_comment_theme','default.css');
	}
	
	if(!$existingpluginversion || $pluginversion > $existingpluginversion)
	{
		$sql_site = " CREATE TABLE IF NOT EXISTS ".$sz_table_prefix."sz_site (
			    site_key varchar(32) NOT NULL DEFAULT '' ,
			    plugin_version varchar(6) NOT NULL ,
			    site_url VARCHAR(255) ,
			    rating_verification VARCHAR(16) ,
			    CONSTRAINT site_pk PRIMARY KEY (site_key)
			    ) type=InnoDB ; " ;

		$platformwrapper->yk_maybe_create_table("sz_site", $sql_site);

		$sql_blog = "CREATE TABLE IF NOT EXISTS ".$sz_table_prefix."sz_blog (
			    blog_id int(8) NOT NULL,
			    blog_key varchar(32),
			    blog_url VARCHAR(255) ,
			    blog_title VARCHAR(255) ,
			    blog_subject VARCHAR(255) ,
			    display_template VARCHAR(16) ,
			    language VARCHAR(16) ,
			    site_key varchar(32) NOT NULL,
			    CONSTRAINT blog_pk PRIMARY KEY (blog_id),
				INDEX site_key_index (site_key),
				FOREIGN KEY (site_key) REFERENCES ".$sz_table_prefix."sz_site(site_key),
			    INDEX blog_idx2(blog_key)) type=InnoDB;";

		$platformwrapper->yk_maybe_create_table("sz_blog", $sql_blog);

		$sql_email = "CREATE TABLE IF NOT EXISTS ".$sz_table_prefix."sz_email (
			    email_address VARCHAR(255) NOT NULL,
			    yk_score float ,
			    global_name VARCHAR(255) ,
			    encoded_email VARCHAR(255),
			    CONSTRAINT email_pk PRIMARY KEY (email_address)) type=InnoDB;" ;

		$platformwrapper->yk_maybe_create_table("sz_email", $sql_email);


		$sql_comment = "CREATE TABLE IF NOT EXISTS ".$sz_table_prefix."sz_comment (
			    blog_id int(8) NOT NULL,
			    posting_id int(8) NOT NULL,
			    comment_id int(8) NOT NULL,
			    creation_date DATE,
			    comment_rating float ,
			    comment_score float ,
			    raw_score float ,
			    rating_count int(8) default 0,
			    anon_raw_score float ,
			    anon_rating_count int(8) default 0,
			    email_address VARCHAR(255) NOT NULL,
			    exclude_flag VARCHAR(1),
			    CONSTRAINT comment_pk PRIMARY KEY (blog_id, posting_id, comment_id)
			    ) type=InnoDB;";

		$platformwrapper->yk_maybe_create_table("sz_comment", $sql_comment);

		$sql_blog_user = "CREATE TABLE IF NOT EXISTS ".$sz_table_prefix."sz_blog_user (
			    blog_id int(8) NOT NULL,
			    screen_name VARCHAR(255) NOT NULL,
			    email_address VARCHAR(255) NOT NULL,
			    yk_score float ,
			    CONSTRAINT email_pk PRIMARY KEY (blog_id, email_address),
			    CONSTRAINT email_uk UNIQUE (screen_name),
				INDEX blog_id_index (blog_id),
			    FOREIGN KEY (blog_id) REFERENCES ".$sz_table_prefix."sz_blog(blog_id),
				INDEX email_address_index (email_address),
			    FOREIGN KEY (email_address) REFERENCES ".$sz_table_prefix."sz_email(email_address)) type=InnoDB;" ;

		$platformwrapper->yk_maybe_create_table("sz_blog_user", $sql_blog_user);

		$sql_post = "CREATE TABLE IF NOT EXISTS ".$sz_table_prefix."sz_post (
			  blog_id int(11) NOT NULL,
			  posting_id int(11) NOT NULL,
			  creation_date date NOT NULL,
			  post_score float default NULL,
			  raw_score float default NULL,
			  rating_count int(8) default 0,
			  anon_raw_score float default NULL,
			  anon_rating_count int(8) default 0,
			  email_address varchar(255) NOT NULL,
			  exclude_flag VARCHAR(1),
			  PRIMARY KEY  (blog_id,posting_id)
			) ENGINE=InnoDB CHARSET=utf8;";

		$platformwrapper->yk_maybe_create_table("sz_post", $sql_post);
		$existingpluginversion = $pluginversion;
	}
	//Get the site key from plugin database
	$sitequery = "select site_key from ".$sz_table_prefix."sz_site ";
	$row = $platformwrapper->yk_get_row($sitequery) ;

	$sitekey = $row->site_key;
	$response = cp_http_post("",  $cpserverurl, "/webservices/ykinstallplugin.php?site_key=$sitekey&pluginversion=$pluginversion&remoteurl=".urlencode($siteurl), 80);
	$resultArr = array();
	$returned_values = explode(',', $response); // split at the commas
	foreach($returned_values as $item) {
		list($key, $value) = explode('=', $item, 2); // split at the =
		$resultArr[$key] = $value;
	}
	$sitequery;
	if($resultArr['SUCCESS'] == "Y")
	{
		//update the plugin database
		if($resultArr['SITEKEY'])
		{
			//insert site detail
			$sitequery ="insert into ".$sz_table_prefix."sz_site (site_key,plugin_version,site_url) values('".$resultArr['SITEKEY']."','$pluginversion','$siteurl')";
			$platformwrapper->yk_query($sitequery);
		}
		else
			//update site details when plugin is reinstalled
			$platformwrapper->yk_update_plugin_version($pluginversion, $sitekey);
			$platformwrapper->yk_update_option("sz_plugin_installed", "true");
	}
	else
	{
		if($resultArr['ERRORMSGCODE'] == "SYSERR")
			$platformwrapper->yk_update_plugin_install_error("System error . Plugin activation failed");
		else if($resultArr['ERRORMSGCODE'] == 'SITEKEYERR')
			$platformwrapper->yk_update_plugin_install_error("Wrong sitekey  . SezWho  plugin activation failed");
	}
}

/****************************************************************************************************/
// Adding action to add warning/err message to plugin
$sezwho_wp_version = (float)substr($wp_version, 0,3);
if ($sezwho_wp_version >= 2.3)
add_action('admin_notices', 'youkarma_warning');
else
add_action('admin_footer', 'youkarma_warning');
add_action('admin_menu', 'yk_pluginconfig');
/****************************************************************************************************/
function youkarma_warning() {
        global $platformwrapper, $siteurl, $sezwho_wp_version;
        $ykpluginerr = $platformwrapper->yk_get_plugin_install_error();

        //Validates the plugin version and upgrades if neccessary.
        validateSezWhoPlugin();

        if($ykpluginerr)
        {
                if ($sezwho_wp_version >= 2.3)
                        echo "<div id='youkarma_warning' class='updated fade'><p><strong>".__($ykpluginerr)."</strong></p></div>";
                else
                        echo "
                        <div id='youkarma_warning' class='updated fade-ff0000'><p><strong>".__($ykpluginerr)."</strong></p></div>
                        <style type='text/css'>
                        #adminmenu { margin-bottom: 5em; }
                        #youkarma_warning { position: absolute; top: 7em; left:24em; }
                        </style>
                        ";
        }
        else if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL)
        {
                if ($sezwho_wp_version >= 2.3)
                        echo "<div id='youkarma_warning' class='updated fade'><p><strong>".__('SezWho is almost ready. You need an access key to use it for your blog. Enter it ')."<a href='".get_settings('siteurl')."/wp-admin/plugins.php?page=youkarma-key-config'> here<a>"."</strong></p></div>";
                else
                        echo "<div id='youkarma_warning' class='updated fade'><p><strong>".__('SezWho is almost ready. You need an access key to use it for your blog. Enter it ')."<a href='".get_settings('siteurl')."/wp-admin/plugins.php?page=youkarma-key-config'> here<a>"."</strong></p></div>
                        <style type='text/css'>
                        #adminmenu { margin-bottom: 5em; }
                        #youkarma_warning { position: absolute; top: 7em; left:24em; }
                        </style>
                        ";
        }
        $platformwrapper->yk_update_plugin_install_error('');
}

function yk_pluginconfig()
{
	global $wpdb;

	//Validates the plugin version and upgrades if neccessary.
	validateSezWhoPlugin();

	if ( function_exists('add_submenu_page') ) {
		add_submenu_page('plugins.php', __('SezWho Configuration'), __('SezWho Configuration'), 'manage_options', 'youkarma-key-config', 'youkarma_conf');
	}
}

function youkarma_activate($blogkey, $is_sync)
{
		global $platformwrapper, $cpserverurl, $siteurl, $homeurl, $sz_blog_id;
		global $sz_table_prefix;
		$sitequery = "select site_key from ".$sz_table_prefix."sz_site ";
		$row = $platformwrapper->yk_get_row($sitequery) ;
		$sitekey = $row->site_key;
		$blogtitle = $platformwrapper->yk_get_blog_name();
		$blogsubject = $platformwrapper->yk_get_blog_description();
		$blogid = $sz_blog_id;
		$blogtitle   = addslashes($blogtitle) ;
		$blogsubject = addslashes($blogsubject) ;
		$response = cp_http_post("",  $cpserverurl, "/webservices/ykactivateblogkey.php?sitekey=$sitekey&blogtitle=".urlencode($blogtitle)."&blogid=$blogid&blogkey=$blogkey&blogurl=$siteurl&homeurl=$homeurl", 80);
		$returned_values = explode(',', $response); // split at the commas
		$resultArr = array();
		foreach($returned_values as $item) {
			list($key, $value) = explode('=', $item, 2); // split at the =
			$resultArr[$key] = $value;
		}
		if($resultArr['SUCCESS']== 'N')
		{
			if($resultArr['ERRORMSGCODE'] == 'SYSERR')
			{
				echo("<div class='updated fade-ff0000'>SezWho Server Error</div>");
			}
			else if($resultArr['ERRORMSGCODE'] == 'BLOGKEYERR')
			{
				echo("<div class='updated fade-ff0000'>Invalid Blog Key</div>");
			}
			else if($resultArr['ERRORMSGCODE'] == 'SITEKEYERR')
			{
				echo("<div class='updated fade-ff0000'>Invalid Site Key</div>");
			}
			else if($resultArr['ERRORMSGCODE'] == 'ALREADYACTIVATED')
			{
				echo("<div class='updated fade-ff0000'>Blog Key Already Activated</div>");
				if($_POST['syncblog'])
				{
					echo("<div class='updated fade-ff0000'>Syncronizing your existing comments, Please wait. This step can take a few minutes depending on the number of comments you have</div>");
					$blogsyncurl = ABSPATH."/wp-content/plugins/sezwho/syncblog.php";
					include($blogsyncurl);
				}
			}
		}
		else if($resultArr['SUCCESS']== 'Y')
		{

			echo("<div class='updated fade-ff0000'>Blog Key Successfully  Activated</div>");
			$blog_query="select count(*) as bcount from ".$sz_table_prefix."sz_blog  where blog_key='$blogkey'";
			$row = $platformwrapper->yk_get_row($blog_query) ;
			$blog_num = $row->bcount;
			if($blog_num == 0)
			{
				$insert_blog_query ="insert into ".$sz_table_prefix."sz_blog(blog_id,blog_key,blog_url,
				blog_title,blog_subject,site_key) values ('$blogid','$blogkey','$siteurl','$blogtitle','$blogsubject','$sitekey')";
				$platformwrapper->yk_query($insert_blog_query);
				$platformwrapper->yk_update_blog_key($blogkey);
			}

			if($is_sync)
			{
				echo("<div class='updated fade-ff0000'>Syncronizing your existing comments, Please wait. This step can take a few minutes depending on the number of comments you have</div>");
				$blogsyncurl = ABSPATH."/wp-content/plugins/sezwho/syncblog.php";
				include($blogsyncurl);
			}
		}
}

add_action( 'load-plugins_page_youkarma-key-config', create_function('$a', 'if ( !empty( $_POST ) ) check_admin_referer( "sez-who-rate-comments-options" );') );

function youkarma_conf()
{
	global $platformwrapper, $sz_widgets_dynamic, $cpserverurl, $siteurl, $homeurl, $sz_blog_id;
	global $sz_table_prefix;
	$new_blog_activation = false;
	
	// look for CSS theme files
	$CTdir = ABSPATH."/wp-content/plugins/sezwho/sezwho_comments";
	if(is_dir($CTdir)) {
		if($CTd = opendir($CTdir)) {
			$CTfiles = array();
			while(($CTfile = readdir($CTd))) {
				if(strpos($CTfile,'.css') !== false) {
					$f = get_theme_data($CTdir.'/'.$CTfile);
					$f['filename'] = $CTfile;
					$CTfiles[] = $f; 
				}
			}
			closedir($CTd);
		}
	}
	
	//Validate Key
	if ( isset($_POST['key']) && isset($_POST['submit'])) {
		$blogkey = trim($_POST['key']);
		if($_POST['alraedy_activated']) {
			echo("<div class='updated fade-ff0000'>Syncronizing your existing comments, Please wait. This step can take a few minutes depending on the number of comments you have</div>");
			$blogsyncurl = ABSPATH."/wp-content/plugins/sezwho/syncblog.php";
			include($blogsyncurl);
		}
		else {
			$new_blog_activation = true;
			$sitequery = "select site_key from ".$sz_table_prefix."sz_site ";
			$row = $platformwrapper->yk_get_row($sitequery) ;
			$sitekey = $row->site_key;
			$blogtitle = $platformwrapper->yk_get_blog_name();
			$blogsubject = $platformwrapper->yk_get_blog_description();
			$blogid = $sz_blog_id;
			$blogtitle   = addslashes($blogtitle) ;
			$blogsubject = addslashes($blogsubject) ;
			$response = cp_http_post("",  $cpserverurl, "/webservices/ykactivateblogkey.php?sitekey=$sitekey&blogtitle=".urlencode($blogtitle)."&blogid=$blogid&blogkey=$blogkey&blogurl=$siteurl&homeurl=$homeurl", 80);
			$returned_values = explode(',', $response); // split at the commas
			$resultArr = array();
			foreach($returned_values as $item) {
				list($key, $value) = explode('=', $item, 2); // split at the =
				$resultArr[$key] = $value;
			}
			if($resultArr['SUCCESS']== 'N')
			{
				if($resultArr['ERRORMSGCODE'] == 'SYSERR')
				{
					echo("<div class='updated fade-ff0000'>SezWho Server Error</div>");
				}
				else if($resultArr['ERRORMSGCODE'] == 'BLOGKEYERR')
				{
					echo("<div class='updated fade-ff0000'>Invalid Blog Key</div>");
				}
				else if($resultArr['ERRORMSGCODE'] == 'SITEKEYERR')
				{
					echo("<div class='updated fade-ff0000'>Invalid Site Key</div>");
				}
				else if($resultArr['ERRORMSGCODE'] == 'ALREADYACTIVATED')
				{
					echo("<div class='updated fade-ff0000'>Blog Key Already Activated</div>");
					if($_POST['syncblog'])
					{
						echo("<div class='updated fade-ff0000'>Syncronizing your existing comments, Please wait. This step can take a few minutes depending on the number of comments you have</div>");
						$blogsyncurl = ABSPATH."/wp-content/plugins/sezwho/syncblog.php";
						include($blogsyncurl);
					}
				}
			}
			else if($resultArr['SUCCESS']== 'Y')
			{

				echo("<div class='updated fade-ff0000'>Blog Key Successfully  Activated</div>");
				$blog_query="select count(*) as bcount from ".$sz_table_prefix."sz_blog  where blog_key='$blogkey'";
				$row = $platformwrapper->yk_get_row($blog_query) ;
				$blog_num = $row->bcount;
				if($blog_num == 0)
				{
					$insert_blog_query ="insert into ".$sz_table_prefix."sz_blog(blog_id,blog_key,blog_url,
					blog_title,blog_subject,site_key) values ('$blogid','$blogkey','$siteurl','$blogtitle','$blogsubject','$sitekey')";
					$platformwrapper->yk_query($insert_blog_query);
					$platformwrapper->yk_update_blog_key($blogkey);
				}

				if($_POST['syncblog'])
				{
					echo("<div class='updated fade-ff0000'>Syncronizing your existing comments, Please wait. This step can take a few minutes depending on the number of comments you have</div>");
					$blogsyncurl = ABSPATH."/wp-content/plugins/sezwho/syncblog.php";
					include($blogsyncurl);
				}
			}
		}
	}
	$blogkey = $platformwrapper->yk_get_blog_key();
	if($blogkey) {
		if($new_blog_activation)
			echo("<div class='updated fade-ff0000'>Blog activated . Your key is ".$blogkey."</div>");
		echo("<div class='wrap'>");
		echo("<h2>Synchronize Content:</h2>");
		echo("<form action='' method='post' >");
		wp_nonce_field( 'sez-who-rate-comments-options' );
		echo("<input id='key' name='key' type='hidden' value='".$blogkey."' />");
		echo("<input type='hidden' name='syncblog' value='1' />");
		echo("<input type='hidden' name='alraedy_activated' value='1' />");
		echo("<br />Sync allows you to get the latest user reputation data and to enable ratings for existing comments.");
		echo("<p class='submit'><input type='submit' onclick=\"this.style.display='none';\" name='submit' value='Synchronize my content with SezWho' /></p></form>");
		echo("</div>");
	}
	else {
		echo("<div class='wrap'>");
		echo("<h2>SezWho Configuration</h2> &nbsp; If you don't have a blog key, <a target ='_blank' href='".$cpserverurl."/register.php'>get it here</a>");
		echo("<form action='' method='post' >");
		wp_nonce_field( 'sez-who-rate-comments-options' );
		echo("<table class='form-table optiontable'>");
		echo("<tr valign='top'>");
		echo("<th scope='row'>Blog Key:</th>");
		echo("<td><input id='key' name='key' type='text' size='15' /></td></tr>");
		echo("<tr valign='top'>");
		echo("<th scope='row'>Synchronize:</th>");
		echo("<td><input type='checkbox' name='syncblog' checked />");
		echo("<br />Sync allows you to get the latest user reputation data and to enable ratings for existing comments.");
		echo("</td></tr>");
		echo("</table>");
		echo("<p class='submit'><input type='submit' onclick=\"this.style.display='none';\" name='submit' value='Activate & Sync' /></p></form>");
		echo("</div>");
		return;
	}

	//general options
	if(isset($_POST['sz_reset_defaults']) && $_POST['sz_reset_defaults']==1)
	{
		$platformwrapper->yk_update_option("sz_cmo_text", "Who am I?");
		$platformwrapper->yk_update_option("sz_rating_text", "Rate this");
		$platformwrapper->yk_update_option("sz_cmo_text_bracel", "(");
		$platformwrapper->yk_update_option("sz_cmo_text_bracer", ")");
		$platformwrapper->yk_update_option("sz_enable_auto_layout", "1");
		$platformwrapper->yk_update_option("sz_enable_auto_layout_post", "1");
		$platformwrapper->yk_update_option("sz_enable_comment_rating", "1");
		$platformwrapper->yk_update_option("sz_enable_comment_filter", "0");
		$platformwrapper->yk_update_option("sz_enable_post_rating", "1");
		$platformwrapper->yk_update_option("sz_enable_post_cmo_link", "0");
        	$platformwrapper->yk_update_option('sz_enable_comments_template','0');
		$platformwrapper->yk_update_option('sz_comment_theme','default.css');
		
		$platformwrapper->yk_update_option("sz_disable_profile_hover", "0");
		$platformwrapper->yk_update_option("sz_use_local_css_js", "0");
	}
	
	if( isset($_POST['dmsubmit']) ) {
		if(isset($_POST['DCRoption']))
			$platformwrapper->yk_update_option("sz_enable_comment_rating", "0");
		else
			$platformwrapper->yk_update_option("sz_enable_comment_rating", "1");

		if(isset($_POST['DFoption']))
			$platformwrapper->yk_update_option("sz_enable_comment_filter", "0");
		else
			$platformwrapper->yk_update_option("sz_enable_comment_filter", "1");

		if(isset($_POST['DWoption']))
			$platformwrapper->yk_set_widget_dynamic('1');
		else
			$platformwrapper->yk_set_widget_dynamic('0');
		if(isset($_POST['DPRoption']))
			$platformwrapper->yk_update_option("sz_enable_post_rating", "0");
		else
			$platformwrapper->yk_update_option("sz_enable_post_rating", "1");

		if(isset($_POST['DAoption'])) {
			$platformwrapper->yk_update_option("sz_enable_auto_layout", "0");
		}
		else {
			$platformwrapper->yk_update_option("sz_enable_auto_layout", "1");
		}

		if(isset($_POST['DAPoption'])) {
			$platformwrapper->yk_update_option("sz_enable_auto_layout_post", "0");
		}
		else {
			$platformwrapper->yk_update_option("sz_enable_auto_layout_post", "1");
		}

		if(isset($_POST['PAoption']))
			$platformwrapper->yk_update_option("sz_enable_post_cmo_link", "0");
		else
			$platformwrapper->yk_update_option("sz_enable_post_cmo_link", "1");

		if(isset($_POST['PLoption']))
			$platformwrapper->yk_update_option("sz_cmo_text", $_POST['PLoption']);
		else
			$platformwrapper->yk_update_option("sz_cmo_text", "Who am I?");
	
		if(isset($_POST['RToption']))
			$platformwrapper->yk_update_option("sz_rating_text", $_POST['RToption']);
		else
			$platformwrapper->yk_update_option("sz_rating_text", "Rate this");
	
		if(isset($_POST['PBLoption']))
			$platformwrapper->yk_update_option("sz_cmo_text_bracel", $_POST['PBLoption']);
		else
			$platformwrapper->yk_update_option("sz_cmo_text_bracel", "(");
	
		if(isset($_POST['PBRoption']))
			$platformwrapper->yk_update_option("sz_cmo_text_bracer", $_POST['PBRoption']);
		else
			$platformwrapper->yk_update_option("sz_cmo_text_bracer", ")");

        	if(isset($_POST['CToption'])) {
			$platformwrapper->yk_update_option('sz_enable_comments_template','1');
			$platformwrapper->yk_update_option("sz_enable_auto_layout", "0");
		}
        	else {
			$platformwrapper->yk_update_option('sz_enable_comments_template','0');
			//$platformwrapper->yk_update_option("sz_enable_auto_layout", "1");
		}
		
		if(isset($_POST['CTPoption'])) {
			$platformwrapper->yk_update_option('sz_comment_theme',$_POST['CTPoption']);
		}
		else {
			$platformwrapper->yk_update_option('sz_comment_theme','default.css');
		}
		
		if(isset($_POST['DPLHoption']))
			$platformwrapper->yk_update_option("sz_disable_profile_hover", "1");
		else
			$platformwrapper->yk_update_option("sz_disable_profile_hover", "0");
			
		if(isset($_POST['ULCJoption']))
			$platformwrapper->yk_update_option("sz_use_local_css_js", "1");
		else
			$platformwrapper->yk_update_option("sz_use_local_css_js", "0");

	}

	$sz_enable_comment_rating = $platformwrapper->yk_get_option("sz_enable_comment_rating");
	$sz_enable_comment_filter = $platformwrapper->yk_get_option("sz_enable_comment_filter");
	$sz_widgets_dynamic = $platformwrapper->yk_get_widget_dynamic();
	$sz_enable_post_rating = $platformwrapper->yk_get_option("sz_enable_post_rating");
	$sz_enable_auto_layout = $platformwrapper->yk_get_option("sz_enable_auto_layout");
	$sz_enable_auto_layout_post = $platformwrapper->yk_get_option("sz_enable_auto_layout_post");
	$sz_enable_post_cmo_link = $platformwrapper->yk_get_option("sz_enable_post_cmo_link");
	//general options
	$sz_cmo_text_anchor = $platformwrapper->yk_get_option("sz_cmo_text");
	$sz_rt_text_anchor = $platformwrapper->yk_get_option("sz_rating_text");
	$sz_cmo_text_bracel = $platformwrapper->yk_get_option("sz_cmo_text_bracel");
	$sz_cmo_text_bracer = $platformwrapper->yk_get_option("sz_cmo_text_bracer");
	$sz_enable_comments_template = $platformwrapper->yk_get_option('sz_enable_comments_template');
	$sz_comment_theme = $platformwrapper->yk_get_option('sz_comment_theme');
	
	$sz_use_local_css_js = $platformwrapper->yk_get_option("sz_use_local_css_js");
	$sz_disable_profile_hover = $platformwrapper->yk_get_option("sz_disable_profile_hover");
	


//for Disable markup options 
	echo("<div class='wrap' style='margin-top:20px'>");
	echo("<h2>SezWho Options</h2>");
	echo("<form name='sz_options_form' id='sz_options_form' action='' method='post' >");
	wp_nonce_field( 'sez-who-rate-comments-options' );
	
	echo("<h3 style='margin-top:20px;margin-bottom:5px'>Comments Options</h2>");
	echo("<table class='form-table optiontable'>");
	echo("<tr valign='top'>");
	echo("<th scope='row'>Disable Comment Rating</th>");
	if($sz_enable_comment_rating)
		echo("<td><input type='checkbox' name='DCRoption' unchecked />");
	else
		echo("<td><input type='checkbox' name='DCRoption' checked />");
	echo("&nbsp; This option disables the rating option for your comments.");
	echo("</td></tr>");

// enable/disable included comment template
	echo '<tr valign="top">'.
         '<th scope="row">Enable Comment Template</th>'.
         '<td><input type="checkbox" name="CToption" '.($sz_enable_comments_template == 1 ? 'checked' : 'unchecked').' onclick="if(this.checked) {document.getElementById(\'CTPoption\').disabled=false; document.getElementById(\'DFoption\').disabled=false;} else {document.getElementById(\'CTPoption\').disabled=true; document.getElementById(\'DFoption\').disabled=true;}" />'.
         '&nbsp; This option enables the threaded comments template provided with the SezWho plugin.</td>'.
         '</tr>';

// choose comment template
	echo '<tr valign="top">'.
		 '<th scope="row">Choose Comments Theme</th>';

	if($sz_enable_comments_template)
		echo	 '<td><select id="CTPoption" name="CTPoption" style="width:250px;">';
	else
		echo     '<td><select id="CTPoption" name="CTPoption" style="width:250px;" disabled=true>';

	foreach($CTfiles AS $CTFile) { 
		echo '<option value="'.$CTFile['filename'].'"'.
			 ($sz_comment_theme == $CTFile['filename'] ? ' selected="selected"' : '').
			 '>'.($sz_comment_theme == $CTFile['filename'] ? '&middot; ' : '').$CTFile['Name'].'</option>'; 
	}
	echo '</select> &nbsp;&nbsp (This option is available when comment template is enabled)'.
		 '</td></tr>';

	//  Disable rating and filter
        echo("<tr valign='top'>");
        echo("<th scope='row'>Disable Comment Filter</th>");
        if($sz_enable_comment_filter)
        	echo "<td><input type='checkbox' id='DFoption' name='DFoption' unchecked " . ($sz_enable_comments_template == 1 ? '' : 'disabled=true') . "/>";
        else
	        echo "<td><input type='checkbox' id='DFoption' name='DFoption' checked " . ($sz_enable_comments_template == 1 ? '' : 'disabled=true') . "/>";
        echo("&nbsp; This option disables filtering of comments. (This option is available when comment template is enabled)");
        echo("</td></tr>");


	echo("</table>");

// Disable Post Rating, Score 
	echo("<h3 style='margin-top:20px;margin-bottom:5px'>Post Options</h2>");
	echo("<table class='form-table optiontable'>");
	echo("<tr valign='top'>");
	echo("<th scope='row'>Disable Post Rating</th>");
	if($sz_enable_post_rating)
		echo("<td><input type='checkbox' name='DPRoption' unchecked />");
	else
		echo("<td><input type='checkbox' name='DPRoption' checked />");
	echo("&nbsp; This option disables the rating option for your post.");
	echo("</td></tr>");

// Disable Post Check me out Link
	echo("<tr valign='top'>");
	echo("<th scope='row'>Disable Post 'Who am I?' link</th>");
	if($sz_enable_post_cmo_link)
		echo("<td><input type='checkbox' name='PAoption' unchecked />");
	else
		echo("<td><input type='checkbox' name='PAoption' checked />");
	echo("&nbsp; This option disables the check me out link next to the author name.");
	echo("</td></tr>");
	echo("</table>");

	echo("<h3 style='margin-top:20px;margin-bottom:5px'>General Options</h2>");
	echo("<table class='form-table optiontable'>");

//  Change the "Who am I? text
	echo("<tr valign='top'>");
	echo("<th scope='row'>Profile Link Text</th>");
	if($sz_cmo_text_anchor)
		echo("<td><input type='text' name='PLoption' size='15' value='$sz_cmo_text_anchor' />");
	else
		echo("<td><input type='text' name='PLoption' size='15' value='Who am I?' />");
	echo("&nbsp; This option changes the anchor text used for profile links. The default value is 'Who am I?'.");
	echo("</td></tr>");

//  Change the seperator text
	echo("<tr valign='top'>");
	echo("<th scope='row'>Profile Link Seperator</th>");
	if($sz_cmo_text_bracel && $sz_cmo_text_bracer)
		echo("<td><input type='text' name='PBLoption' size='1' value='$sz_cmo_text_bracel' /> Who am I? <input type='text' name='PBRoption' size='1' value='$sz_cmo_text_bracer' />");
	else
		echo("<td><input type='text' name='PBLoption' size='1' value='(' /> Who am I? <input type='text' name='PBRoption' size='1' value=')' />");
	echo("&nbsp; This option changes the seperators around the profile links. The default value is '(' and ')'.");
	echo("</td></tr>");

//  Change the Rate this text
	echo("<tr valign='top'>");
	echo("<th scope='row'>Rating Header Text</th>");
	if($sz_rt_text_anchor)
		echo("<td><input type='text' name='RToption' size='15' value='$sz_rt_text_anchor' />");
	else
		echo("<td><input type='text' name='RToption' size='15' value='Rate this' />");
	echo("&nbsp; This option changes the anchor text used for the heading for ratings bar. The default value is 'Rate this'.");
	echo("</td></tr>");

//  Disable rating and filter for comment
	echo("<tr valign='top'>");
	echo("<th scope='row'>Disable Comments Auto Layout</th>");
	if($sz_enable_auto_layout)
		echo("<td><input type='checkbox' name='DAoption' unchecked />");
	else
		echo("<td><input type='checkbox' name='DAoption' checked />");
	echo("&nbsp; This option disables the automatic instrumentation of SezWho markup on Comments. If you are modifying your comments template to incorporate SezWho, use this option.");
	echo("</td></tr>");

//  Disable rating and filter for post
	echo("<tr valign='top'>");
	echo("<th scope='row'>Disable Posts Auto Layout</th>");
	if($sz_enable_auto_layout_post)
		echo("<td><input type='checkbox' name='DAPoption' unchecked />");
	else
		echo("<td><input type='checkbox' name='DAPoption' checked />");
	echo("&nbsp; This option disables the automatic instrumentation of SezWho markup on Posts. If you are modifying your posts template to incorporate SezWho, use this option.");
	echo("</td></tr>");
	
// WIDGET DYNAMIC 
/*
	echo("<tr valign='top'>");
	echo("<th scope='row'>Dynamic Widget</th>");
	if($sz_widgets_dynamic)
		echo("<td><input type='checkbox' name='DWoption' checked />");
	else
		echo("<td><input type='checkbox' name='DWoption' unchecked />");
	echo("&nbsp; This helps you in access widgets dynamically. ");
	echo("</td></tr>");
*/

//Disable profile link hover
	echo("<tr valign='top'>");
	echo("<th scope='row'>Disable Profile Link On Hover</th>");
	if($sz_disable_profile_hover)
		echo("<td><input type='checkbox' name='DPLHoption' checked />");
	else
		echo("<td><input type='checkbox' name='DPLHoption' unchecked />");
	echo("&nbsp; If checked, SezWho profiles will only be shown on mouse clicks.");
	echo("</td></tr>");
	echo("</table>");
	
	
	echo("<h3 style='margin-top:20px;margin-bottom:5px'>Local CSS & JavaScript (Advanced)</h3> (Please note presentation customization from your account at SezWho for this blog will be lost when you use local CSS/JS)");
	echo("<table class='form-table optiontable'>");
//Send local CSS for style
	echo("<tr valign='top'>");
	echo("<th scope='row'>Use Local CSS/JS</th>");
	if($sz_use_local_css_js)
		echo("<td><input type='checkbox' name='ULCJoption' checked />");
	else
		echo("<td><input type='checkbox' name='ULCJoption' unchecked />");
	echo("&nbsp; If checked, the CSS & JS files will be served from your blog server instead of SezWho server.");
	echo("</td></tr>");
	
	echo("<input type='hidden' id='sz_reset_defaults' name='sz_reset_defaults' value='0'>");

	echo("</table>");
	echo("<p><a href='javascript:void(0);' onclick=\"this.style.display='none';document.getElementById('sz_reset_defaults').value=1;document.getElementById('sz_options_form').submit();\">Reset to default</a></p>");
	echo("<p class='submit'><input type='submit' onclick=\"this.style.display='none';\" name='dmsubmit' value='Update Options' /></p></form>");
	echo("</div>");
}

/****************************************************************************************************/
// plugin the post comment interface API here
add_action('comment_post', 'yk_postComment');
add_action('wp_set_comment_status', 'yk_postComment');
/****************************************************************************************************/

function yk_postComment($comment_id){
	global  $platformwrapper, $homeurl;
	global $cpserverurl;
	global $wpdb, $sz_blog_id;
	global $sz_table_prefix;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL)
		return;

	//Validates the plugin version and upgrades if neccessary.
	validateSezWhoPlugin();

	$query= "select blog_id, blog_key,site_key from ".$sz_table_prefix."sz_blog where blog_id = '$sz_blog_id'";
	$row = $platformwrapper->yk_get_row($query) ;
	$blog_id=$row->blog_id;
	$blog_key=$row->blog_key;
	$site_key = $row->site_key;
	$comment_approved = $platformwrapper->yk_is_comment_approved($comment_id) ;
	$comment_author_email = urlencode($platformwrapper->yk_get_comment_author_email($comment_id));
	$comment_date = $platformwrapper->yk_get_comment_date($comment_id);
	
	// if comment is not approved OR commenter email is not provided, return without processing
	if ($comment_approved != '1' || $comment_author_email == "" || $site_key == "" || $blog_key == "")
		return;

	$comment_author_url = $platformwrapper->yk_get_comment_author_url($comment_id);
	$post_id = $platformwrapper->yk_get_post_id($comment_id);
	$post_url = $platformwrapper->yk_get_post_url($post_id) ;
	$comment_url = $post_url.'#comment-'.$comment_id ;
	$post_content = $postData["Title"] ;
	$post_title = $platformwrapper->yk_get_post_title($post_id);
	$post_intro = $platformwrapper->yk_get_post_intro($post_id, 1000);
	$comment_intro = "" ;

	$comment_content = $platformwrapper->yk_get_comment_content($comment_id);
	$chars_tobe_replaced = array("<br>","<br/>","<br />","<p>");
	$replace_with = array("\n");
	if ($comment_content)
		$comment_intro = substr($comment_content, 0, 2000);

	$categories = $platformwrapper->yk_get_categories($post_id);

	// get plugin version
	$plugin_version = $platformwrapper->yk_get_plugin_version();
	// assume plugin version is of the type WP1.2
	$platform = substr($plugin_version, 0, 2) ;
	$version= substr($plugin_version, 2) ;
	/* call the webservice here - enocode the introductions as they may contain special characters */
	$content = cp_http_post("", $cpserverurl, "/webservices/ykwebservice_front.php?method=postComment&site_key=$site_key&blog_id=$blog_id&blog_key=$blog_key&posting_id=$post_id&posting_url=".urlencode($post_url)."&posting_title=".urlencode($post_title)."&posting_intro=".urlencode($post_intro)."&comment_id=$comment_id&comment_url=".urlencode($comment_url)."&comment_intro=".urlencode($comment_intro)."&email_address=".$comment_author_email."&screen_name=nothing&comment_author_url=".urlencode($comment_author_url)."&plugin_version=$version&posting_tags=".urlencode($categories), 80);
	$postcomment_ws_result =trim(substr($content,strpos($content,"CPRESPONSE")+10,strlen($content)));
	$returned_values = explode(',', $postcomment_ws_result); // split at the commas
	$resultArr = array();
	foreach($returned_values as $item) {
		list($key, $value) = explode('=', $item, 2); // split at the =
		$resultArr[$key] = $value;
	}
	
	$comment_author_email = urldecode($comment_author_email);
	if ($resultArr["Success"] == 'Y') { // The webservice call has been a success, hence insert/update the plugin schema		
		// Query row from email using email_address
		$email_query = "select email_address from ".$sz_table_prefix."sz_email where email_address = '".$comment_author_email."';" ;
		$email_res = $platformwrapper->yk_get_var($email_query) ;
		$email_res_count = $platformwrapper->yk_num_rows($email_query) ;
		if ($email_res_count == 1) { // this row already exists, hence update it
			$update_email_query = "update ".$sz_table_prefix."sz_email set yk_score = '".$resultArr["YKScore"]."', global_name = '".$resultArr["Global_Name"]."' where email_address = '".$comment_author_email."';" ;
			$platformwrapper->yk_query($update_email_query);
		} else { // this row does not exist, hence insert it
			$insert_email_query = "insert into ".$sz_table_prefix."sz_email (email_address, yk_score, global_name, encoded_email) values ( '".$comment_author_email."' , '".$resultArr["YKScore"]."' , '".$resultArr["Global_Name"]."', '".$resultArr["encoded_email"]."');" ;
			$platformwrapper->yk_query($insert_email_query);
		}
		// now insert comment
		$yk_score =  $resultArr["YKScore"] ; // Get the YK Score returned by the webservice
		$raw_score=($yk_score-5)*10;
		$comment_score;
		if($raw_score >=1)
			$comment_score =log($raw_score,5)+5;
		else if($raw_score <= -1)
			$comment_score = (-1*log(-1*$raw_score, 5))+5;
		else
			$comment_score=5;

		$insert_comment_query ;
		if ($yk_score != null) {
			$insert_comment_query = "insert into ".$sz_table_prefix."sz_comment (blog_id, posting_id, comment_id, email_address, rating_count, comment_score,raw_score, creation_date) values ('".$blog_id."', '".$post_id."', '".$comment_id."', '".$comment_author_email."' , '0' , ".$comment_score.",".$raw_score.", '".$comment_date."')";
		} else {
			$insert_comment_query = "insert into ".$sz_table_prefix."sz_comment (blog_id, posting_id, comment_id, email_address, creation_date) values ('".$blog_id."', '".$post_id."', '".$comment_id."', '".$comment_author_email."', '".$comment_date."')";
		}

		$platformwrapper->yk_query($insert_comment_query);
	} else { // status is blocked, insert only minimal data. If the comment is approved, the reverse websservice callback will happen to update the sz_comment table
		$insert_comment_query = "insert into ".$sz_table_prefix."sz_comment (blog_id, posting_id, comment_id, email_address, creation_date, exclude_flag) values ('".$blog_id."', '".$post_id."', '".$comment_id."', '".$comment_author_email."', '".$comment_date."', 'B')";
		$platformwrapper->yk_query($insert_comment_query);
	}
}

/****************************************************************************************************/
// this is to insert a hidden image in each comment to identify where to insert nodes in the JS DOM w.r.t. this
add_filter('get_comment_author_link', 'yk_comment_author_link');
/****************************************************************************************************/

function yk_comment_author_link($content) {
	global $sz_options, $platformwrapper, $comment,$sz_global_comment_id_array;
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return $content;
	if (is_admin() || !$sz_options->sz_enable_auto_layout || is_feed() || $sz_options->sz_enable_comments_template) return $content;
	if (!in_the_loop() && array_key_exists('szGlobalsCommentTemplateOn', $GLOBALS) && !$GLOBALS['szGlobalsCommentTemplateOn']) return $content;
	if (!in_array($comment->comment_ID, $sz_global_comment_id_array))
        	$sz_global_comment_id_array[] =  $comment->comment_ID;
	else return $content;
	
	return cp_comment_user_image().$content.cp_comment_profile_link();
}

/****************************************************************************************************/
// this is to insert a hidden image in each comment to identify where to insert nodes in the JS DOM w.r.t. this
add_filter('comment_text', 'yk_comment_text');
/****************************************************************************************************/

function yk_comment_text($content) {
	if (is_admin() || is_feed()) return $content;
	global $platformwrapper, $sz_global_script_string, $comment, $sz_comment_num, $sz_comment_iteration_num ,$sz_blog_id , $wpdb, $sz_comment_id, $comment_author_email_enc, $sz_user_score, $sz_comment_score, $sz_auto_option_bar, $sz_auto_comment, $sz_comment_author_url, $szuserlinkrepo, $szuserimagerepo,$szuserimagerepo_post, $szuserlinkrepo_use_en, $sz_show_commenter_pic, $szuserimage_title, $sz_comment_rating_count, $sz_comment_creation_date;
	global $sz_options, $sz_post;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return $content;

	yk_comment_data_gen();

	$sz_comment_author_url = $comment->comment_author_url;

	// call to include generated Javascript
	if ($sz_comment_iteration_num == 0)
	{
		$sz_global_script_string .= insert_header_js_code();
		$sz_global_script_string .= dump_sz_global_params();
		$sz_global_script_string .= '<script type="text/javascript" id="szCommentHiddenTag:'.$sz_comment_id.'">';
		$sz_global_script_string .= 'var sz_comment_config_params = {post_id:"'.$comment->comment_post_ID.'",sortOrder:"'.$sortOrder.'",sz_auto_comment:'.$sz_auto_comment.',sz_auto_option_bar:'.$sz_auto_option_bar.',sz_enable_comment_rating:'.$platformwrapper->yk_get_option("sz_enable_comment_rating").',comment_number:'.$sz_comment_num.',sz_user_link_repo:"'.$szuserlinkrepo.'",sz_user_image_repo:"'.$szuserimagerepo.'",sz_user_image_repo_post:"'.$szuserimagerepo_post.'", sz_user_link_repo_use_enc:'.$szuserlinkrepo_use_en.',sz_show_commenter_pic:'.$sz_show_commenter_pic.',sz_commenter_pic_title:"'.$szuserimage_title.'", sz_comment_data:[]};';
	}
	else
		$sz_global_script_string .= '<script type="text/javascript" id="szCommentHiddenTag:'.$sz_comment_id.'">';

	$sz_global_script_string .= 'sz_comment_config_params.sz_comment_data['.$sz_comment_iteration_num.']= {comment_id:"'.$sz_comment_id.'", comment_author:"'.rawurlencode($comment->comment_author).'", comment_author_url:"'.$comment->comment_author_url.'", comment_author_email:"'.$comment_author_email_enc.'",sz_score:"'.$sz_user_score.'",comment_score:"'.$sz_comment_score.'",encoded_email:"'.md5(strtolower($comment->comment_author_email)).'",rating_count:"'.$sz_comment_rating_count.'",creation_date:"'.$sz_comment_creation_date.'"};';

	$sz_global_script_string .= '</script>';
	$fc = "";
	if ($sz_options->sz_enable_auto_layout && !$sz_options->sz_enable_comments_template) $fc = cp_comment_footer_content();
	return $content.$fc;
}

function sz_dump_js_exec_command(){
	return '<script type="text/javascript"> // <![CDATA[ if(!(!(/Safari|Konqueror|KHTML/gi).test(navigator.userAgent) && !navigator.userAgent.match(/opera/gi) && navigator.userAgent.match(/msie/gi))) if (window.SezWho.Utils.callJSFramework)SezWho.Utils.callJSFramework(); // ]]> </script>';
}

function yk_options_preload(){
	global $sz_options;
	global $platformwrapper, $sz_blog_id;
	global $sz_table_prefix;
	
	if (!empty($sz_options)) return;
	
	$sz_options = new SZOptions();
	
	$query = "SELECT site_key, blog_key, blog_id FROM ".$sz_table_prefix."sz_blog where blog_id = '$sz_blog_id'";
	$row = $platformwrapper->yk_get_row($query);
	$sz_options->blog_wp_id = $row->blog_id;
	$sz_options->sz_site_key = $row->site_key;
	$sz_options->sz_blog_key = $row->blog_key;
	
	$sz_options->sz_plugin_version_string = $platformwrapper->yk_get_plugin_version();
	$sz_options->sz_platform = strtolower(substr($sz_options->sz_plugin_version_string, 0, 2));
	$sz_options->sz_version = substr($sz_options->sz_plugin_version_string, 2);
	$sz_options->sz_theme_name = $platformwrapper->yk_get_template();
	$sz_options->sz_auto_option_bar = $platformwrapper->yk_get_option("sz_enable_auto_layout");
	$sz_options->sz_auto_comment = 0; 
	$sz_options->sz_enable_comment_rating = $platformwrapper->yk_get_option("sz_enable_comment_rating");
	$sz_options->sz_enable_comment_filter = $platformwrapper->yk_get_option("sz_enable_comment_filter");
	$sz_options->sz_widgets_dynamic = $platformwrapper->yk_get_widget_dynamic();
	$sz_options->sz_enable_post_rating = $platformwrapper->yk_get_option("sz_enable_post_rating");
	$sz_options->sz_enable_auto_layout = $platformwrapper->yk_get_option("sz_enable_auto_layout");
	$sz_options->sz_enable_auto_layout_post = $platformwrapper->yk_get_option("sz_enable_auto_layout_post");
	$sz_options->sz_enable_post_cmo_link = $platformwrapper->yk_get_option("sz_enable_post_cmo_link");
	$sz_options->sz_cmo_text_anchor = $platformwrapper->yk_get_option("sz_cmo_text");
	$sz_options->sz_rating_text_anchor = $platformwrapper->yk_get_option("sz_rating_text");
	$sz_options->sz_cmo_text_bracel = $platformwrapper->yk_get_option("sz_cmo_text_bracel");
	$sz_options->sz_cmo_text_bracer = $platformwrapper->yk_get_option("sz_cmo_text_bracer");
	$sz_options->sz_wp_db_prefix = $platformwrapper->yk_get_db_prefix();
      $sz_options->sz_enable_comments_template = $platformwrapper->yk_get_option('sz_enable_comments_template');
      
     	$sz_options->sz_disable_profile_hover = $platformwrapper->yk_get_option("sz_disable_profile_hover");
	$sz_options->sz_use_local_css_js = $platformwrapper->yk_get_option("sz_use_local_css_js");

}

function yk_post_data_load($load_comment_data=true,$post_id=null){

	global $sz_options, $sz_post, $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	yk_options_preload();
	
	if (!empty($post_id) && ($post_id == $sz_post->post_wp_id)
                     && ($sz_post->comments_loaded == $load_comment_data)){
                        return;
	}
	if (empty($post_id)){
		$post_id = get_the_ID();
	}
	$sz_post = new SZPost();
	$platformwrapper->yk_get_published_post($sz_options->blog_wp_id, $post_id, $sz_post);	
        if ($load_comment_data == true){
	        $sz_post->comments = $platformwrapper->yk_get_published_comments($sz_options->blog_wp_id, $post_id);
	        $sz_post->comments_loaded = true;
	}
}
function yk_comment_data_gen() {
	global $comment, $cpserverurl, $cppluginurl, $platformwrapper, $sz_comment_num, $sz_comment_iteration_num ,$sz_blog_id ,  $wpdb, $sz_comment_id, $comment_author_email_enc, $sz_comment_rating_count, $sz_user_score, $sz_comment_score, $sz_comment_author_url, $sz_comment_creation_date;
	global $sz_options, $sz_post;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;

	if ($sz_comment_id == $comment->comment_ID) return;
	$post_id = $comment->comment_post_ID ;
	$sz_comment_id = $comment->comment_ID ;
	yk_post_data_load(true, $post_id);

	// call to populate the data that was originally getting passed in the footer
	if ($sz_comment_iteration_num == -1) {
		$sz_blog_id = $sz_options->blog_wp_id;
		$sz_comment_num = $sz_post->comment_count;
		$sortOrder = $_GET["sortOrder"] ;
	}
	if ($comment->comment_author_email != '') {
		$comment_iteration = $sz_post->get_comment($sz_comment_id);
		$sz_user_score = $comment_iteration->sz_comment_author_score;
		$comment_author_email_enc = $comment_iteration->comment_author_email_encoded;
		$sz_comment_score = $comment_iteration->sz_comment_score;
		$sz_anon_comment_score = empty($comment_iteration->sz_comment_anon_score)?0:$comment_iteration->sz_comment_anon_score;
		$sz_comment_rating_count = (empty($comment_iteration->sz_comment_rating_count)?0:$comment_iteration->sz_comment_rating_count) + (empty($comment_iteration->sz_comment_anon_rating_count)?0:$comment_iteration->sz_comment_anon_rating_count);
		$sz_comment_score = number_format($platformwrapper->applyAnonymousScore($sz_anon_comment_score, $sz_comment_score), 1);
		$sz_comment_creation_date = empty($comment_iteration->sz_comment_creation_date)?0:$comment_iteration->sz_comment_creation_date;
	}
	else{
		$sz_user_score = 0;
		$comment_author_email_enc = '';
		$sz_comment_score = 0;
		$sz_comment_rating_count = 0;
	}
	$sz_comment_author_url = $comment_iteration->comment_author_url;

	$sz_comment_iteration_num = $sz_comment_iteration_num + 1;

	return;
}

function insert_header_js_code() {
	global $platformwrapper, $sz_global_script_string, $cpserverurl, $cppluginurl, $jstagname, $sz_global_script_string ;
	global $sz_options;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	if ($GLOBALS['szJSAlreadyAdded']) return;
	$replace_chars = array('/', ' ');

	$theme_name = str_replace($replace_chars, '_', $sz_options->sz_theme_name);
	
	yk_options_preload();
	$blog_key = $sz_options->sz_blog_key ;
	$site_key = $sz_options->sz_site_key ;
	$platform = $sz_options->sz_platform;
	$version= $sz_options->sz_version;
		
	if($sz_options->sz_use_local_css_js)
		$script_src = "$cppluginurl/sezwho.js";
	else 
		$script_src = "$cpjsserverurl/widgets/profile/js_output/$platform/$theme_name/$version/$jstagname/$site_key/$blog_key";
		
	$sz_global_script_string .= "<script type='text/javascript'>";
 	$sz_global_script_string .=  "if (!document.getElementById('szJsEmbedScript')){";
 	$sz_global_script_string .=  "var script = document.createElement('script');";
        $sz_global_script_string .=  "script.src = '$script_src';";
        $sz_global_script_string .=  "document.getElementsByTagName('head')[0].appendChild(script);";
	$sz_global_script_string .= "}</script>";

	$GLOBALS['szJSAlreadyAdded'] = 1;
}

function dump_sz_global_params() {
	global $cpserverurl, $cpcssserverurl, $cppluginurl, $platformwrapper, $jstagname, $pluginversion, $homeurl, $sz_global_script_string;
	global $sz_options;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	if ($GLOBALS['szGlobalsJSAlreadyAdded']) return;
	yk_options_preload();
	$blog_id=$sz_options->blog_wp_id;
	$blog_key=$sz_options->sz_blog_key;
	$site_key = $sz_options->sz_site_key;
	$sz_use_local_css_js = $sz_options->sz_use_local_css_js;
	
	$replace_chars = array('/', ' ');
	$theme_name = str_replace($replace_chars, '_', $sz_options->sz_theme_name);

        $sz_global_script_string .=  "<script type=\"text/javascript\">";
        $sz_global_script_string .= 'var sz_global_config_params = {cppluginurl:"'.$cppluginurl.'",cpserverurl:"'.$cpserverurl.'",cpcssserverurl:"'.$cpcssserverurl.'", rating_submit_path:"'.$homeurl.'/", sitekey:"'.$site_key.'",blogkey:"'.$blog_key.'",blogid:"'.$blog_id.'", plugin_version:"'.substr($pluginversion, 2).'", sz_use_local_css_js:"'.$sz_use_local_css_js.'",js_tag_name:"'.$jstagname.'",theme:"'.$theme_name.'",platform:"'.substr($pluginversion, 0, 2).'"} ; ';
	$sz_global_script_string .= "</script>";
	$GLOBALS['szGlobalsJSAlreadyAdded'] = 1;
}

/****************************************************************************************************/

function cp_comment_profile_link() {

	global $sz_comment_iteration_num, $comment_author_email_enc, $sz_comment_id, $sz_comment_score, $platformwrapper;
	global $sz_options;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return "";
	
	yk_comment_data_gen();

	if( !$sz_comment_id || !$comment_author_email_enc || (0 == $sz_comment_score))
		return "";

	$sz_cmo_text_anchor = $sz_options->sz_cmo_text_anchor;
	if(!$sz_cmo_text_anchor)
		$sz_cmo_text_anchor = "Who am I?";
	$sz_cmo_text_bracel = $sz_options->sz_cmo_text_bracel;
	if(!$sz_cmo_text_bracel)
		$sz_cmo_text_bracel = "(";
	$sz_cmo_text_bracer = $sz_options->sz_cmo_text_bracer;
	if(!$sz_cmo_text_bracer)
		$sz_cmo_text_bracer = ")";
	
	if($sz_options->sz_disable_profile_hover)
		$sz_take_event = 'onclick';
	else 
		$sz_take_event = 'onmouseover';

	$ret_str = " $sz_cmo_text_bracel<a class='cpEmbedPageProfileLink' id='sz_profile_link:$sz_comment_iteration_num' $sz_take_event ='javascript:SezWho.Utils.cpProfileEventHandler(event);' onmousedown='javascript:SezWho.Utils.cpProfileEventHandler(event);' href='javascript:void(0);' onmouseout='javascript:SezWho.DivUtils.cancelPopUp();'>$sz_cmo_text_anchor</a>$sz_cmo_text_bracer";
	return $ret_str;
}


function cp_comment_footer_content($c_id=null) {
	global $sz_comment_iteration_num, $sz_comment_score, $sz_comment_rating_count, $platformwrapper, $comment_author_email_enc, $sz_comment_id, $sz_comment_score;
	global $sz_options;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return "";
	
	if(!$sz_options->sz_enable_comment_rating){
		return "";
	}
	yk_comment_data_gen();
	if( !$sz_comment_id || !$comment_author_email_enc || (0 == $sz_comment_score))
		return "";
	$sz_rt_text_anchor = $sz_options->sz_rating_text_anchor;
	if(!$sz_rt_text_anchor)
		$sz_rt_text_anchor = "Rate this";
		
	$width = ((float)$sz_comment_score);
	$width = (int)($width*10);
	$print_score = sprintf("%1.1f", $sz_comment_score/2);

	$result_str =  "<table class='cpEmbedPageTable' style='width:auto'>";
	$result_str .=  "<tr>";
	// reply text
	if($c_id !== null && $c_id !== false) {
		$result_str .= '<td class="cpEmbedPageTableCell sz_reply_footer_table_cell">'.
					   '<a href="#" class="sz_reply" onclick="sz_add_comment(\'comment-'.$c_id.'\', '.$c_id.', true); return false;">'.
					   'Reply</a>&nbsp; | &nbsp;</td>';
	}
	
	$result_str .=  "<td class='cpEmbedPageTableCell'>";
	$result_str .= "<span class='cpEmbedPageCommFooterCS'><a href='javascript:void(0);' onmousedown='SezWho.DivUtils.activateRatingsHelpDIV(event);'>$sz_rt_text_anchor</a>: </span>";
	$result_str .=  "</td><td class='cpEmbedPageTableCell'>";
	$result_str .= '<ul id="cpEmbedCommScore:'.$sz_comment_iteration_num.'" class="cpEmbedCommScoreUl" onmousedown="SezWho.Utils.ScoreDisplay.update(event)" onmousemove="SezWho.Utils.ScoreDisplay.cur(event)" title="'.$sz_rt_text_anchor.'!"><li id="cpEmbedCommScoreLi:'.$sz_comment_iteration_num.'" class="cpEmbedCommScoreLi" title="'.sprintf("%1.1f", $sz_comment_score/2).'" style="width:'.$width.'%;list-style-image:none !important;border: 0 none !important; list-style-position:outside !important; list-style-type:none !important; margin:0px !important; padding:0px !important;"></li></ul>';
	$result_str .=  "</td><td class='cpEmbedPageTableCell'>";
	$result_str .= '<span id="cpEmbedCommScoreSpan:'.$sz_comment_iteration_num.'" class="cpEmbedCommScoreSpan" title="'.$print_score.'">'.$print_score.'</span>';
	if ($sz_comment_rating_count)
		if ($sz_comment_rating_count == "1")
		$result_str .= '<span id="cpEmbedCommCountSpan:'.$sz_comment_iteration_num.'" class="cpEmbedCommCountSpan" title="'.$sz_comment_rating_count.'"> ('.$sz_comment_rating_count.' person)</span>';
		else
		$result_str .= '<span id="cpEmbedCommCountSpan:'.$sz_comment_iteration_num.'" class="cpEmbedCommCountSpan" title="'.$sz_comment_rating_count.'"> ('.$sz_comment_rating_count.' people)</span>';
	else
		$result_str .= '<span id="cpEmbedCommCountSpan:'.$sz_comment_iteration_num.'" class="cpEmbedCommCountSpan" title="'.$sz_comment_rating_count.'"></span>';
	$result_str .=  "</td>";
	$result_str .= '</tr>';
	$result_str .= '</table>';
	return $result_str;
}

function cp_comment_user_image(){
	global $sz_comment_iteration_num, $sz_comment_id, $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return "";
	
	global $comment_author_email_enc, $sz_comment_score, $sz_global_img_repo_arr;
	global $sz_options, $sz_post;
	yk_comment_data_gen();
	if( !$sz_comment_id || !$comment_author_email_enc || (0 == $sz_comment_score))
		return "";
		
	if($sz_options->sz_disable_profile_hover)
		$sz_take_event = 'onclick';
	else
		$sz_take_event = 'onmouseover';

	$ret_str = "<img src='http://s3.amazonaws.com/sz_users_images/noimg.gif' alt='no image' title='no image' class='cpEmbedImageUserh' $sz_take_event ='javascript:SezWho.Utils.cpProfileEventHandler(event);' onmousedown='javascript:SezWho.Utils.cpProfileEventHandler(event);' onmouseout='javascript:SezWho.DivUtils.cancelPopUp();' onerror=\"this.src='http://s3.amazonaws.com/sz_users_images/noimg.gif'\" id='sz_image_link:".$sz_comment_iteration_num."'/>";
	
	$comment_iteration = $sz_post->get_comment($sz_comment_id);
	$val = md5(strtolower($comment_iteration->comment_author_email)) . ":" . "sz_image_link:" . $sz_comment_iteration_num;
	if (!in_array($val, $sz_global_img_repo_arr))
		$sz_global_img_repo_arr[] = $val;
	return $ret_str;
}

function cp_post_author_image($author_name){
	global $wpdb, $platformwrapper, $sz_global_img_repo_arr;
	global $sz_post, $sz_options;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return "";

	$post_id = get_the_ID();
	
	if (!isset($post_id) || !$post_id) return "";
	yk_post_data_load(false, $post_id);

	$blog_id = $sz_options->blog_wp_id;
	
	// Attach the global params
	$blog_author_encoded_email = $sz_post->post_author_email_encoded;
	$blog_author_email = $sz_post->post_author_email;
	if (!$blog_author_encoded_email){
		return $text;
	}
	
	
	if($sz_options->sz_disable_profile_hover)		
		$sz_take_event = 'onclick';
	else
		$sz_take_event = 'onmouseover';

	$text = "<img src='http://s3.amazonaws.com/sz_users_images/noimg.gif' onmouseout='javascript:SezWho.DivUtils.cancelPopUp();' onmousedown='javascript:void SezWho.Utils.cpProfileImgClickHandler(".$post_id.", \"P\");' $sz_take_event ='javascript:SezWho.Utils.cpProfilePostEventHandler(event)' class='cpEmbedImageAuthor' onerror=\"this.src='http://s3.amazonaws.com/sz_users_images/noimg.gif'\" id='sz_image_link_post:".$post_id."' alt='User Image' title='No image'/>";

	$val = md5(strtolower($blog_author_email)) . ":" . "sz_image_link_post:" . $post_id;
	if (!in_array($val, $sz_global_img_repo_arr))
		$sz_global_img_repo_arr[] = $val;
	return $text;
}
function cp_post_author_link(){
	global $wpdb, $platformwrapper;
	global $sz_options, $sz_post;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return "";
	$post_id = get_the_ID();
	yk_post_data_load(false, $post_id);
	
	if (!isset($post_id) || !$post_id) return "";
	$sz_cmo_text_anchor = $sz_options->sz_cmo_text_anchor;
	if(!$sz_cmo_text_anchor)
		$sz_cmo_text_anchor = "Who am I?";
	$sz_cmo_text_bracel = $sz_options->sz_cmo_text_bracel;
	if(!$sz_cmo_text_bracel)
		$sz_cmo_text_bracel = "(";
	$sz_cmo_text_bracer = $sz_options->sz_cmo_text_bracer;
	if(!$sz_cmo_text_bracer)
		$sz_cmo_text_bracer = ")";
      
	if($sz_options->sz_disable_profile_hover)		
		$sz_take_event = 'onclick';
	else
		$sz_take_event = 'onmouseover';
	
      $text = "<span id='sz_author_span_post:".$post_id."'> $sz_cmo_text_bracel<a href='javascript:void(0);' onmouseout='javascript:SezWho.DivUtils.cancelPopUp();' onmousedown='javascript:void SezWho.Utils.cpProfilePostEventHandler(event);' $sz_take_event ='javascript:SezWho.Utils.cpProfilePostEventHandler(event)' class='cpEmbedPageProfileLink' id='sz_author_link_post:".$post_id."'/>$sz_cmo_text_anchor</a>$sz_cmo_text_bracer</span>";

	return $text;
}

function cp_post_ratingbar($sz_post_id = -1, $sz_post_score = -1, $rating_count=-1) {
	global $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return "";
	
	global $sz_post, $sz_options;
	$result_str = "";


	if ($sz_post_id == -1) $post_id = get_the_ID();
	else $post_id = $sz_post_id;

	if(!$post_id) return "";
	
	yk_post_data_load(false, $post_id);
	
	if(!$sz_options->sz_enable_post_rating){
		return $result_str;
	}
	 
	$sz_rt_text_anchor = $sz_options->sz_rating_text_anchor;
	if(!$sz_rt_text_anchor)
		$sz_rt_text_anchor = "Rate this";

   if ($sz_post_score == -1 || $rating_count == -1) {// Get the score
   		$post_score = $sz_post->sz_post_score;
   		$rating_count = $sz_post->sz_post_rating_count;
   }
   else
        $post_score = $sz_post_score;	

	$width = ((float)$post_score);
	$width = (int)($width*10);
	$print_score = sprintf("%1.1f", $post_score/2);

	$result_str =  "<table class='cpEmbedPageTable' style='width:auto'>";
        $result_str .=  "<tr>";
        $result_str .=  "<td class='cpEmbedPageTableCell'>";
	$result_str .= "<span class='cpEmbedPagePostFooterCS'><a href='javascript:void(0);' onmousedown='SezWho.DivUtils.activateRatingsHelpDIV(event);'>$sz_rt_text_anchor</a>: </span>";
        $result_str .=  "</td><td class='cpEmbedPageTableCell'>";
	$result_str .= '<ul id="cpEmbedPostScore:'.$post_id.'" class="cpEmbedPostScoreUl" onmousedown="SezWho.Utils.ScoreDisplay.update(event)" onmousemove="SezWho.Utils.ScoreDisplay.cur(event)" title="'.$sz_rt_text_anchor.'"><li id="cpEmbedPostScoreLi:'.$post_id.'" class="cpEmbedPostScoreLi" title="'.$print_score.'" style="width:'.$width.'%;list-style-image:none !important;border: 0 none !important; list-style-position:outside !important; list-style-type:none !important; margin:0px !important; padding:0px !important;"></li></ul>';
        $result_str .=  "</td><td class='cpEmbedPageTableCell'>";
	$result_str .= '<span id="cpEmbedPostScoreSpan:'.$post_id.'" class="cpEmbedPostScoreSpan" title="'.$print_score.'">'.$print_score.'</span>';
	if ($rating_count)
		if ($rating_count == "1")
		$result_str .= '<span id="cpEmbedPostCountSpan:'.$post_id.'" class="cpEmbedPostCountSpan" title="'.$rating_count.'"> ('.$rating_count.' person) </span>';
		else
		$result_str .= '<span id="cpEmbedPostCountSpan:'.$post_id.'" class="cpEmbedPostCountSpan" title="'.$rating_count.'"> ('.$rating_count.' people) </span>';
	else
	$result_str .= '<span id="cpEmbedPostCountSpan:'.$post_id.'" class="cpEmbedPostCountSpan" title="'.$rating_count.'"></span>';
        $result_str .=  "</td>";
	$result_str .= '</tr>';
	$result_str .= '</table>';

	return $result_str;
}

/****************************************************************************************************/
// this is to render the comment rating buttons etc.
add_action('wp_head', 'sz_addto_head');

/****************************************************************************************************/

function sz_addto_head() {
	global $platformwrapper, $cpserverurl, $cpjsserverurl, $cpcssserverurl, $cppluginurl, $jstagname, $pluginversion;
	global $sz_options;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	yk_options_preload();	
	
	$replace_chars = array('/', ' ');
	$theme_name = str_replace($replace_chars, '_', $sz_options->sz_theme_name);
	
	$blog_key = $sz_options->sz_blog_key;
	$site_key = $sz_options->sz_site_key;
	$platform = $sz_options->sz_platform;
	$version= $sz_options->sz_version;
	
	//Validates the plugin version and upgrades if neccessary.
	validateSezWhoPlugin();
	
	if($sz_options->sz_use_local_css_js)
		echo "<link rel='stylesheet' id='szProfAndEmbedStyleSheet' href='$cppluginurl/sezwho.css' type='text/css' media='screen' />\n";
	else
		echo "<link rel='stylesheet' id='szProfAndEmbedStyleSheet' href='$cpcssserverurl/widgets/profile/css_output/$platform/$theme_name/$version/$jstagname/$site_key/$blog_key.css' type='text/css' media='screen' />\n";
		
	if($sz_options->sz_use_local_css_js)
		echo "<script type='text/javascript' id='szJsEmbedScript' src='$cppluginurl/sezwho.js'></script>\n";
	else 
		echo "<script type='text/javascript' id='szJsEmbedScript' src='$cpjsserverurl/widgets/profile/js_output/$platform/$theme_name/$version/$jstagname/$site_key/$blog_key'></script>\n";
		
}
// Returns array with headers in $response[0] and body in $response[1]
function cp_http_post($request, $host, $path, $port = 80) {
	// host is of the type http://xxx.xxx.xxxx/xxxx
	global $wp_version, $platformwrapper, $pluginversionminor, $pluginrevision;
	$cpserver= substr($host, 7);
	$cpserverparams = split( "/" , $cpserver);
	if (strpos($cpserver , ':') > 0 ) {
		$cpserver_port_arr = split (':' , $cpserverparams[0]) ;
		$cpserver = $cpserver_port_arr[0] ;
		//$cpport = $cpserver_port_arr[1] ;
	} else {
		$cpserver = $cpserverparams[0] ;
		//$cpport = 80 ;
	}
	$plugin_version = $platformwrapper->yk_get_plugin_version();
	if (isset($pluginversionminor)) $plugin_version .= '.'.$pluginversionminor;
	if (isset($pluginrevision)) $plugin_version .= '.'.$pluginrevision;
	$cpserverpath = $cpserverparams[1].$path;
	$http_request  = "POST /$cpserverpath HTTP/1.0\r\n";
	$http_request .= "Host: $cpserver\r\n";
	$http_request .= "Content-Type: application/x-www-form-urlencoded; charset=" . get_option('blog_charset') . "\r\n";
	$http_request .= "Content-Length: " . strlen($request) . "\r\n";
	$http_request .= "User-Agent: WordPress/$wp_version | SezWho/$plugin_version\r\n";
	$http_request .= "\r\n";
	$http_request .= $request;

	$response = '';
	if( false != ( $fs = @fsockopen($cpserver, $port, $errno, $errstr, 10) ) ) {
		fwrite($fs, $http_request);
		while ( !feof($fs) ) {
			$response .= fgets($fs, 1160); // One TCP-IP packet
		}
		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);
		return $response[1];
	}
	return $response;
}
///////////////////////////////////////
///// Red Carpet Widget		/////////
///////////////////////////////////////

// Register Red Carpet Widget functions
add_action('init', 'widget_sezwho_rc_register');

function widget_sezwho_rc_register() {
	global $wp_version, $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	$name = __('SezWho RedCarpet');
	if("2.5" == $wp_version)
	{
		$dims = array();
		$widget_ops = array('classname' => 'widget_sezwho_rc', 'description' => __( 'SezWho RedCarpet Widget' ));
	}
	else 
	{
		$dims = array('width' => 400, 'height' => 250);
		$widget_ops = array('classname' => 'widget_sezwho_rc');
	}
	
	if(function_exists(wp_register_sidebar_widget))
		wp_register_sidebar_widget('sezwho-red-carpet', $name, 'widget_sezwho_rc', $widget_ops );
	if(function_exists(wp_register_widget_control))
		wp_register_widget_control('sezwho-red-carpet', $name, 'widget_sezwho_rc_control', $dims );
	
	//Validates the plugin version and upgrades if neccessary.
	validateSezWhoPlugin();
}

function widget_sezwho_rc($args) {
	global $sz_widgets_dynamic, $sz_global_script_string, $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	extract($args);
	insert_header_js_code();
	dump_sz_global_params();
	$sz_global_script_string .=  "<script type=\"text/javascript\">var sz_rc_config_params = {sz_rc_data:[]};</script>";

	echo $before_widget;
	echo "<div id='cp_rc_placeholder'>";
	if($sz_widgets_dynamic)
		$sz_global_script_string .= "<script type='text/javascript' src='sz_global_config_params.cppluginurl+\"/cpredcarpet.php\"' />";
	else
		include_once(ABSPATH.'wp-content/plugins/sezwho/cpredcarpet.php');
	echo "</div>";
	echo $after_widget;
}

function widget_sezwho_rc_control() {
	global $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	$options = $newoptions = get_option('widget_sezwho_rc');
	//default values
	$rc_limit_comments = 5;
	$sz_rc_default_img_width = 40;
	$sz_rc_default_img_height = 40;
	$sz_rc_default_width = "";
	$sz_rc_default_color = "transparent";

	if ( !is_array($options) )
		$options = $newoptions = array();
	if ( $_POST["sezwho-submit"] )
	{
		$newoptions['sz_rc_rows_num'] = strip_tags(stripslashes($_POST["sz_rc_rows_num"]));
		if($newoptions['sz_rc_rows_num']=='')			$newoptions['sz_rc_rows_num'] = $rc_limit_comments;
		if(!is_numeric($newoptions['sz_rc_rows_num']))			$newoptions['sz_rc_rows_num'] = $rc_limit_comments;

		$newoptions['sz_rc_img_width'] = strip_tags(stripslashes($_POST["sz_rc_img_width"]));
		if($newoptions['sz_rc_img_width']=='')			$newoptions['sz_rc_img_width'] = $sz_rc_default_img_width;
		if(!is_numeric($newoptions['sz_rc_img_width']))			$newoptions['sz_rc_img_width'] = $sz_rc_default_img_width;

		$newoptions['sz_rc_img_height'] = $newoptions['sz_rc_img_width'];

		$newoptions['sz_rc_width'] = strip_tags(stripslashes($_POST["sz_rc_width"]));

		$newoptions['sz_rc_color'] = strip_tags(stripslashes($_POST["sz_rc_color"]));
		if($newoptions['sz_rc_color']=='')	$newoptions['sz_rc_color'] = $sz_rc_default_color;
	}

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_sezwho_rc', $options);
	}
	$sz_rc_rows_num = htmlspecialchars($options['sz_rc_rows_num'], ENT_QUOTES);
	if($sz_rc_rows_num=='')		$sz_rc_rows_num = $rc_limit_comments;
	if(!is_numeric($sz_rc_rows_num))		$sz_rc_rows_num = $rc_limit_comments;

	$sz_rc_img_width = htmlspecialchars($options['sz_rc_img_width'], ENT_QUOTES);
	if($sz_rc_img_width=='')		$sz_rc_img_width = $sz_rc_default_img_width;
	if(!is_numeric($sz_rc_img_width))		$sz_rc_img_width = $sz_rc_default_img_width;

	$sz_rc_img_height = htmlspecialchars($options['sz_rc_img_height'], ENT_QUOTES);
	if($sz_rc_img_height=='')		$sz_rc_img_height = $sz_rc_default_img_height;
	if(!is_numeric($sz_rc_img_height))		$sz_rc_img_height = $sz_rc_default_img_height;

	$sz_rc_width = htmlspecialchars($options['sz_rc_width'], ENT_QUOTES);

	$sz_rc_color = htmlspecialchars($options['sz_rc_color'], ENT_QUOTES);

	$sz_rc_color = ($sz_rc_color=='') ? $sz_rc_color : $sz_rc_color;
	if($sz_rc_color=='')	$sz_rc_color = $sz_rc_default_color;

	echo 'Number of rows in SezWho widget.<br>';
	echo '<input style="width: 20px;" id="sz_rc_rows_num" name="sz_rc_rows_num" type="text" value="'.$sz_rc_rows_num.'"><br><br>';
	echo 'User Image width for the SezWho widget.<br>';
	echo '[Aspect ratio will be maintained against original size (80x80)] <br>';
	echo '<input style="width: 50px;" id="sz_rc_img_width" name="sz_rc_img_width" type="text" value="'.$sz_rc_img_width.'"><br><br>';
	echo 'Width of SezWho widget.<br>';
	echo '<input style="width: 50px;" id="sz_rc_width" name="sz_rc_width" type="text" value="'.$sz_rc_width.'"><br><br>';
	echo 'Color for the SezWho widget.<br>';
	echo '<input style="width: 100px;" id="sz_rc_color" name="sz_rc_color" type="text" value="'.$sz_rc_color.'"><br>';
	echo '<input type="hidden" id="sezwho-submit" name="sezwho-submit" value="1" />';
}


////////////////////////////////////
//// SezWho Badge Widget ///////////
////////////////////////////////////

// Register Badge widget functions
add_action('init', 'widget_sezwho_badge_register');

function widget_sezwho_badge_register() {
	global $wp_version, $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	if("2.5" == $wp_version)
	{
		if ( !$options = get_option('sezwho_badge') )
			$options = array();
		$widget_ops = array('classname' => 'sezwho_badge', 'description' => __( 'SezWho Badge Widget' ));
		$control_ops = array('width' => 350, 'height' => 100, 'id_base' => 'badge');
		$name = __('SezWho Badge');
	
		$id = false;
		foreach ( array_keys($options) as $o ) {
			// Old widgets can have null values for some reason
			if ( !isset($options[$o]['email']) )
				continue;
			$id = "badge-$o"; // Never never never translate an id
			if(function_exists(wp_register_sidebar_widget))
				wp_register_sidebar_widget($id, $name, 'widget_sezwho_badge', $widget_ops, array( 'number' => $o ));
			if(function_exists(wp_register_widget_control))
				wp_register_widget_control($id, $name, 'widget_sezwho_badge_control', $control_ops, array( 'number' => $o ));
		}
	
		// If there are none, we register the widget's existance with a generic template
		if ( !$id ) {
			if(function_exists(wp_register_sidebar_widget))
				wp_register_sidebar_widget( 'badge-1', $name, 'widget_sezwho_badge', $widget_ops, array( 'number' => -1 ) );
			if(function_exists(wp_register_widget_control))
				wp_register_widget_control( 'badge-1', $name, 'widget_sezwho_badge_control', $control_ops, array( 'number' => -1 ) );
		}
	}
	else 
	{
		$options = get_option('sezwho_badge');
		$number = $options['sz_badge_number'];
	
		if ( $number < 1 ) $number = 1;
		if ( $number > 9 ) $number = 9;
		$dims = array('width' => 350, 'height' => 100);
		$class = array('classname' => 'sezwho_badge');
		for ($i = 1; $i <= 9; $i++) {
			$name = sprintf(__('SezWho Badge %d'), $i);
			$id = "SezWho-Badge-".$i; // Never never never translate an id
			if(function_exists(wp_register_sidebar_widget))
				wp_register_sidebar_widget($id, $name, $i <= $number ? 'widget_sezwho_badge_old' : '', $class, $i);
			if(function_exists(wp_register_widget_control))
				wp_register_widget_control($id, $name, $i <= $number ? 'widget_sezwho_badge_control_old' : '', $dims, $i);
		}
		add_action('sidebar_admin_setup', 'sezwho_badge_setup_old');
		add_action('sidebar_admin_page', 'sezwho_badge_page_old');
	}

	//Validates the plugin version and upgrades if neccessary.
	validateSezWhoPlugin();
}


//////////////////////////////////////////////
// badge functions for WP2.5 releases start
//////////////////////////////////////////////

function widget_sezwho_badge($args, $widget_args = 1) {
	global $sz_widgets_dynamic, $sz_global_script_string, $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	extract($args, EXTR_SKIP);
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widegt_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract($widget_args, EXTR_SKIP);

	$options = get_option('sezwho_badge');

	if ( !isset($options[$number]) )
		return;

	if ( isset($options[$number]['error']) && $options[$number]['error'] )
		return;

	$email = $options[$number]['email'];
	
	insert_header_js_code();
	dump_sz_global_params();
	if (!$GLOBALS['szBadgeDataVarDefined']){
		$sz_global_script_string .= "<script type='text/javascript'>";
		$sz_global_script_string .=  'var sz_badge_config_params = {sz_badge_data:[]};';
		$sz_global_script_string .=  "</script>";
		$GLOBALS['szBadgeDataVarDefined'] = 1;
	}

	echo $before_widget;
	echo "<DIV id='sezwho_badge_".$number."_placeholder'>";
	if($sz_widgets_dynamic)
	{
		$sz_global_script_string .=  "<script type='text/javascript'>";
		$sz_global_script_string .=  "var script = document.createElement('script');";
		$sz_global_script_string .=  "script.src = sz_global_config_params.cppluginurl+\"/cpbadge.php?badge_no=$number\";";
		$sz_global_script_string .=  "document.getElementsByTagName('head')[0].appendChild(script);";
		$sz_global_script_string .=  "</script>";
	}
	else
	{
		include(ABSPATH.'wp-content/plugins/sezwho/cpbadge.php');
	}
	echo "</DIV>";
	echo $after_widget;
}

function widget_sezwho_badge_control($widget_args) {
	global $wp_registered_widgets, $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	static $updated = false;

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract($widget_args, EXTR_SKIP);

	$options = get_option('sezwho_badge');
	if ( !is_array($options) )
		$options = array();

	$emails = array();
	foreach ( $options as $option )
		if ( isset($option['email']) )
			$emails[$option['email']] = true;

	if ( !$updated && 'POST' == $_SERVER['REQUEST_METHOD'] && !empty($_POST['sidebar']) ) {
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id ) {
			if ( 'widget_sezwho_badge' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if ( isset($_POST['widget-id']) && !in_array( "badge-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed.
					unset($options[$widget_number]);
			}
		}

		foreach( (array) $_POST['sezwho-badge'] as $widget_number => $widget_badge ) {
			$widget_badge = stripslashes_deep( $widget_badge );
			$email = $widget_badge['email'];
			$options[$widget_number] = wp_widget_badge_process( $widget_badge );
		}

		update_option('sezwho_badge', $options);
		$updated = true;
	}

	if ( -1 == $number ) {
		$email = '';
		$items = 10;
		$number = '%i%';
	} else {
		extract( (array) $options[$number] );
	}

	wp_widget_badge_form( compact( 'number', 'email', 'items' ) );
}

function wp_widget_badge_form( $args, $inputs = null ) {
	global $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	$default_inputs = array( 'email' => true );
	$inputs = wp_parse_args( $inputs, $default_inputs );
	extract( $args );
	$number = attribute_escape( $number );
	$email  = attribute_escape( $email );
	$items  = (int) $items;
	if ( $items < 1 || 20 < $items )
		$items  = 10;

	if ( $inputs['email'] ) :
?>
	<p>
		<label for="badge-email-<?php echo $number; ?>"><?php _e('Please provide email for the SezWho Badge widget here:'); ?>
			<input class="widefat" id="badge-email-<?php echo $number; ?>" name="sezwho-badge[<?php echo $number; ?>][email]" type="text" value="<?php echo $email; ?>" />
		</label>
	</p>
	<input type="hidden" name="sezwho-badge[<?php echo $number; ?>][submit]" value="1" />
<?php
	endif;
//	foreach ( array_keys($default_inputs) as $input ) :
//		if ( 'hidden' === $inputs[$input] ) :
//			$id = str_replace( '_', '-', $input );
?>
	<input type="hidden" id="badge-<?php echo $id; ?>-<?php echo $number; ?>" name="sezwho-badge[<?php echo $number; ?>][<?php echo $input; ?>]" value="<?php echo $$input; ?>" />
<?php
//		endif;
//	endforeach;
	
}

// Expects unescaped data
function wp_widget_badge_process( $widget_badge ) {
	global $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	$items = (int) $widget_badge['items'];
	if ( $items < 1 || 20 < $items )
		$items = 10;
	$email           = $widget_badge['email'];

	return compact( 'email', 'items' );
}

//////////////////////////////////////////////
// badge functions for WP2.5 releases end
//////////////////////////////////////////////


////////////////////////////////////////////////
// badge functions for pre WP2.5 releases start
////////////////////////////////////////////////


function widget_sezwho_badge_old($args, $number = 1) {
	global $sz_widgets_dynamic, $sz_global_script_string, $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	extract($args);
	insert_header_js_code();
	dump_sz_global_params();
	if (!$GLOBALS['szBadgeDataVarDefined']){
		$sz_global_script_string .=  "<script type='text/javascript'>";
		$sz_global_script_string .=  'var sz_badge_config_params = {sz_badge_data:[]};';
		$sz_global_script_string .=  "</script>";
		$GLOBALS['szBadgeDataVarDefined'] = 1;
	}

	echo $before_widget;
	echo "<DIV id='sezwho_badge_".$number."_placeholder'>";
	if($sz_widgets_dynamic)
	{
		$sz_global_script_string .=  "<script type='text/javascript'>";
		$sz_global_script_string .=  "var script = document.createElement('script');";
		$sz_global_script_string .=  "script.src = sz_global_config_params.cppluginurl+\"/cpbadge.php?badge_no=$number\";";
		$sz_global_script_string .=  "document.getElementsByTagName('head')[0].appendChild(script);";
		$sz_global_script_string .=  "</script>";
	}
	else
	{
		include(ABSPATH.'wp-content/plugins/sezwho/cpbadge.php');
	}
	echo "</DIV>";
	echo $after_widget;
}

function widget_sezwho_badge_control_old($number) {
	global $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	$options = $newoptions = get_option('sezwho_badge');
	if ( !is_array($options) )
		$options = $newoptions = array();
	if ( $_POST["sz_badge_email_$number"] )
		$newoptions[$number]['email'] = strip_tags(stripslashes($_POST["sz_badge_email_$number"]));

	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('sezwho_badge', $options);
	}
	$email = htmlspecialchars($options[$number]['email'], ENT_QUOTES);
	echo 'Please provide email for the SezWho Badge widget.';
	echo '<input style="width: 300px;" id="sz_badge_email_'.$number.'" name="sz_badge_email_'.$number.'" type="text" value="'.$email.'"';
}

function sezwho_badge_setup_old() {
	global $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	$options = $newoptions = get_option('sezwho_badge');
	if ( isset($_POST['sz_badge_number-submit']) ) {
		$number = (int) $_POST['sz_badge_nos'];
		if ( $number > 9 ) $number = 9;
		if ( $number < 1 ) $number = 1;
		$newoptions['sz_badge_number'] = $number;
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('sezwho_badge', $options);
		widget_sezwho_badge_register($options['sz_badge_number']);
	}
}

function sezwho_badge_page_old() {
	global $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	$options = $newoptions = get_option('sezwho_badge');

	echo '<div class="wrap">';
	echo '<form method="POST">';
	echo '<h2>Sezwho Badge widgets</h2>';
	echo '<p style="line-height: 30px;">How many SezWho badge widgets would you like?<select id="sz_badge_nos" name="sz_badge_nos" value="' . $options['sz_badge_number'] . '">';
	for ( $i = 1; $i < 10; ++$i )
		echo "<option value='$i' ".($options['sz_badge_number']==$i ? "selected='selected'" : '').">$i</option>";
	echo '</select>';
	echo '<span class="submit"><input type="submit" name="sz_badge_number-submit" id="sz_badge_number-submit" value="Save" /></span></p>';
	echo '</form>';
	echo '</div>';
}

//////////////////////////////////////////////
// badge functions for pre WP2.5 releases end
//////////////////////////////////////////////


function get_star_rating($rating, $widget = "normal") {
	$temp_score = $rating ;
	$ratings_images = "" ;
	$comment_score = $temp_score / 2  ;
	$display_score = number_format($comment_score, 1) ;

	if("comment" == $widget || "post" == $widget)
	{
		$title = "Comment Score: " . $display_score;
		$on_button_class = "cpEmbedScoreSquareImgon";
		$half_button_class = "cpEmbedScoreSquareImghalf";
		$off_button_class = "cpEmbedScoreSquareImgoff";
	}
	else if("post" == $widget)
	{
		$title = "Post Score: " . $display_score;
		$on_button_class = "cpEmbedScoreSquareImgon";
		$half_button_class = "cpEmbedScoreSquareImghalf";
		$off_button_class = "cpEmbedScoreSquareImgoff";
	}
	else if("badge" == $widget)	{
		$title = "Star Power: " . $display_score;
		$on_button_class = "cpPopupContentUserStarImgonBG";
		$half_button_class = "cpPopupContentUserStarImghalfBG";
		$off_button_class = "cpPopupContentUserStarImgoffBG";
	}
	else if("new_badge" == $widget)	{
		$title = "Star Power: " . $display_score;
		$on_button_class = "szBadgeRatingOn";
		$half_button_class = "szBadgeRatingHalf";
		$off_button_class = "szBadgeRatingOff";
	}
	else if("new_rc" == $widget)	{
		$title = "Star Power: " . $display_score;
		$on_button_class = "szRedCarpetRatingOn";
		$half_button_class = "szRedCarpetRatingHalf";
		$off_button_class = "szRedCarpetRatingOff";
	}
	else {
		$title = "Star Power: " . $display_score;
		$on_button_class = "szRCStarImgon";
		$half_button_class = "szRCStarImghalf";
		$off_button_class = "szRCStarImgoff";
	}
	$full_img = "<button title='". $title ."' type='button' class='".$on_button_class."'></button>";
	$half_img = "<button title='". $title ."' type='button' class='".$half_button_class."'></button>";
	$zero_img = "<button title='". $title ."' type='button' class='".$off_button_class."'></button>";

	if ($comment_score < 0.25) $ratings_images = $zero_img.$zero_img.$zero_img.$zero_img.$zero_img ;
	else if ($comment_score >= 0.25 && $comment_score < 0.75) $ratings_images = $half_img.$zero_img.$zero_img.$zero_img.$zero_img ;
	else if ($comment_score >= 0.75 && $comment_score < 1.25) $ratings_images = $full_img.$zero_img.$zero_img.$zero_img.$zero_img ;
	else if ($comment_score >= 1.25 && $comment_score < 1.75) $ratings_images = $full_img.$half_img.$zero_img.$zero_img.$zero_img ;
	else if ($comment_score >= 1.75 && $comment_score < 2.25) $ratings_images = $full_img.$full_img.$zero_img.$zero_img.$zero_img ;
	else if ($comment_score >= 2.25 && $comment_score < 2.75) $ratings_images = $full_img.$full_img.$half_img.$zero_img.$zero_img ;
	else if ($comment_score >= 2.75 && $comment_score < 3.25) $ratings_images = $full_img.$full_img.$full_img.$zero_img.$zero_img ;
	else if ($comment_score >= 3.25 && $comment_score < 3.75) $ratings_images = $full_img.$full_img.$full_img.$half_img.$zero_img ;
	else if ($comment_score >= 3.75 && $comment_score < 4.25) $ratings_images = $full_img.$full_img.$full_img.$full_img.$zero_img ;
	else if ($comment_score >= 4.25 && $comment_score < 4.75) $ratings_images = $full_img.$full_img.$full_img.$full_img.$half_img ;
	else if ($comment_score >= 4.75) $ratings_images = $full_img.$full_img.$full_img.$full_img.$full_img ;

	return $ratings_images ;
}

function get_formatted_badge_name($name)
{
	$formatted_name = '';
	$first_line_end_char = '-';
	$last_line_end_str = '..';
	$name_len = strlen($name);

	if ($name_len < 10) $formatted_name = $name;
	else
	{
		$space_pos = strpos($name, ' ');

		if ($space_pos && $space_pos < 10)
		{
			$space_pos2 = strpos($name, ' ', $space_pos);
			if ($space_pos2 && $space_pos2 < 10)
				$space_pos = $space_pos2;
			$formatted_name = substr($name, 0, $space_pos)."<BR />".(strlen(substr($name, $space_pos+1))<10?substr($name, $space_pos+1):substr($name, $space_pos+1,7).$last_line_end_str);
		}
		else
			$formatted_name = substr($name, 0, 8).$first_line_end_char."<BR />".(strlen(substr($name, 8))<10?substr($name, 8):substr($name, 8,7).$last_line_end_str);
	}
	return $formatted_name;
}

//Validates and upgrades the plugin if neccessary
function validateSezWhoPlugin()
{
	global $pluginversion, $existingpluginversion, $platformwrapper, $cpserverurl, $siteurl;
	global $sz_table_prefix;
	$blog_key = $platformwrapper->yk_get_blog_key();
	if(false === $blog_key)
		return;
	if(!$existingpluginversion)
	{
		$sz_plugin_installed = $platformwrapper->yk_get_option("sz_plugin_installed");
		if(false === $sz_plugin_installed)	//Variable not set, setting it now
			$platformwrapper->yk_update_option("sz_plugin_installed", "true");
		$existingpluginversion = $platformwrapper->yk_get_plugin_version(true);
	}
	
	$old_version = substr($existingpluginversion, 2);
	$new_version = substr($pluginversion, 2);
	if($new_version > $old_version)
	{
		if("2.2" == $new_version)
		{			
			$sitequery = "select site_key from sz_site ";
			$sitekey = $platformwrapper->yk_get_var($sitequery);
			$platformwrapper->yk_update_plugin_version($pluginversion, $sitekey, true);
			$existingpluginversion = $pluginversion;

			if("1.3" == $old_version){			
			        $sql_post = "CREATE TABLE IF NOT EXISTS sz_post (
		                  blog_id int(11) NOT NULL,
		                  posting_id int(11) NOT NULL,
		                  creation_date date NOT NULL,
		                  post_score float default NULL,
		                  raw_score float default NULL,
		                  rating_count int(8) default 0,
				  	  anon_raw_score float default NULL,
		                  anon_rating_count int(8) default 0,
		                  email_address varchar(255) NOT NULL,
				  	  exclude_flag VARCHAR(1),
		                  PRIMARY KEY  (blog_id,posting_id)
		                ) ENGINE=InnoDB CHARSET=utf8;";
		        	$platformwrapper->yk_maybe_create_table("sz_post", $sql_post);
		        	
		        	$sql_alter_comment = "ALTER TABLE sz_comment ADD COLUMN anon_raw_score float DEFAULT NULL AFTER rating_count";
		        	$platformwrapper->yk_query($sql_alter_comment);
		        	$sql_alter_comment = "ALTER TABLE sz_comment ADD COLUMN anon_rating_count int(8) DEFAULT 0 AFTER anon_raw_score";
		        	$platformwrapper->yk_query($sql_alter_comment);
			}
			
			if ( '' !== $sz_table_prefix) {
				$sql_alter_comment = "rename table sz_comment to ".$sz_table_prefix."sz_comment";
				$platformwrapper->yk_query($sql_alter_comment);
				$sql_alter_comment = "rename table sz_email to ".$sz_table_prefix."sz_email";
				$platformwrapper->yk_query($sql_alter_comment);
				$sql_alter_comment = "rename table sz_post to ".$sz_table_prefix."sz_post";
				$platformwrapper->yk_query($sql_alter_comment);
				$sql_alter_comment = "rename table sz_blog to ".$sz_table_prefix."sz_blog";
				$platformwrapper->yk_query($sql_alter_comment);
				$sql_alter_comment = "rename table sz_site to ".$sz_table_prefix."sz_site";
				$platformwrapper->yk_query($sql_alter_comment);
				$sql_alter_comment = "rename table sz_blog_user to ".$sz_table_prefix."sz_blog_user";
				$platformwrapper->yk_query($sql_alter_comment);
			}
		

			if("1.3" == $old_version){	

				$sz_enable_comment_rating = $platformwrapper->yk_get_option("sz_enable_comment_rating");
				if(false === $sz_enable_comment_rating) {       //Variable hasn't been set, setting now.
					$platformwrapper->yk_update_option("sz_enable_comment_rating", "1");
				}
				
				$sz_enable_post_rating = $platformwrapper->yk_get_option("sz_enable_post_rating");
				if(false === $sz_enable_post_rating) {  //Variable hasn't been set, setting now.
					$platformwrapper->yk_update_option("sz_enable_post_rating", "1");
				}
	
				$sz_enable_auto_layout = $platformwrapper->yk_get_option("sz_enable_auto_layout");
				if(false === $sz_enable_auto_layout) {  //Variable hasn't been set, setting now.
					$platformwrapper->yk_update_option("sz_enable_auto_layout", "1");
				}
	
				$sz_enable_post_cmo_link = $platformwrapper->yk_get_option("sz_enable_post_cmo_link");
				if(false === $sz_enable_post_cmo_link) {  //Variable hasn't been set, setting now.
					$platformwrapper->yk_update_option("sz_enable_post_cmo_link", "0");
				}
	
				$sz_cmo_text_anchor = $platformwrapper->yk_get_option("sz_cmo_text");
				if(false === $sz_cmo_text_anchor) {  //Variable hasn't been set, setting now.
					$platformwrapper->yk_update_option("sz_cmo_text", "Who am I?");
				}
			
				$sz_rt_text_anchor = $platformwrapper->yk_get_option("sz_rating_text");
				if(false === $sz_rt_text_anchor) {  //Variable hasn't been set, setting now.
					$platformwrapper->yk_update_option("sz_rating_text", "Rate this");
				}
			
				$sz_cmo_text_bracel = $platformwrapper->yk_get_option("sz_cmo_text_bracel");
				if(false === $sz_cmo_text_bracel) {  //Variable hasn't been set, setting now.
					$platformwrapper->yk_update_option("sz_cmo_text_bracel", "(");
				}
			
				$sz_cmo_text_bracer = $platformwrapper->yk_get_option("sz_cmo_text_bracer");
				if(false === $sz_cmo_text_bracer) {  //Variable hasn't been set, setting now.
					$platformwrapper->yk_update_option("sz_cmo_text_bracer", ")");
				}
			}
						
			$sz_enable_comments_template = $platformwrapper->yk_get_option("sz_enable_comments_template");
			if(false === $sz_enable_comments_template) {  //Variable hasn't been set, setting now.
				$platformwrapper->yk_update_option("sz_enable_comments_template", "0");		
			}

			$sz_enable_comment_filter = $platformwrapper->yk_get_option("sz_enable_comment_filter");
			if(false === $sz_enable_comment_filter || $sz_enable_comment_filter) {       //Variable hasn't been set or it is set to 1, setting now to 0
				$platformwrapper->yk_update_option("sz_enable_comment_filter", "0");
			}
			
			$sz_comment_theme = $platformwrapper->yk_get_option("sz_comment_theme");
			if(false === $sz_comment_theme) {  //Variable hasn't been set, setting now. 		
				$platformwrapper->yk_update_option('sz_comment_theme','square.css');
			}

			$sz_disable_profile_hover = $platformwrapper->yk_get_option("sz_disable_profile_hover");
			if(false === $sz_disable_profile_hover) {  //Variable hasn't been set, setting now.
				$platformwrapper->yk_update_option("sz_disable_profile_hover", "0");
			}

			$sz_use_local_css_js = $platformwrapper->yk_get_option("sz_use_local_css_js");
			if(false === $sz_use_local_css_js) {  //Variable hasn't been set, setting now.
				$platformwrapper->yk_update_option("sz_use_local_css_js", "0");
			}

			$sz_enable_auto_layout_post = $platformwrapper->yk_get_option("sz_enable_auto_layout_post");
			if(false === $sz_enable_auto_layout_post) {	//Variable hasn't been set, setting now.
				$platformwrapper->yk_update_option("sz_enable_auto_layout_post", "1");
			}

		}
                	
		$sz_sync_output_enabled = 0;
		$blogsyncurl = ABSPATH."/wp-content/plugins/sezwho/syncblog.php";
            include($blogsyncurl);
                  
		$response = cp_http_post("",  $cpserverurl, "/webservices/ykinstallplugin.php?site_key=$sitekey&blog_key=$blog_key&pluginversion=$pluginversion&remoteurl=".urlencode($siteurl), 80);

		//handling default RedCarpet images height, changing from 50 to 40
		$options = get_option('widget_sezwho_rc');
		if($options['sz_rc_img_height'] == 50)
		{
			$options['sz_rc_img_height'] = 40;
			update_option('widget_sezwho_rc', $options);
		}
	}
}

add_action('publish_post','yk_publish_post');
function yk_publish_post($post_id){
	// 1. Get site key and blog key and validate
	// 2. Call the webservice and send the post to sezwho
	// 3. Store the author's YK score
	// 4. Store the post in sz_post
	// --------------

	global  $platformwrapper;
	global $sz_table_prefix;

	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	global $cpserverurl, $sz_blog_id;
	$wp_prefix = $platformwrapper->yk_get_db_prefix() ;


	//Validates the plugin version and upgrades if neccessary.
	validateSezWhoPlugin();
	$query = "select blog_id, blog_key, site_key from ".$sz_table_prefix."sz_blog where blog_id = '$sz_blog_id'";
	$row = $platformwrapper->yk_get_row($query);
	$blog_id = $row->blog_id;
	$site_key = $row->site_key;
	$blog_key = $row->blog_key;
	$post_title = $platformwrapper->yk_get_post_title($post_id);
	$post_categories = $platformwrapper->yk_get_categories($post_id);
	$plugin_version = $platformwrapper->yk_get_plugin_version();
	$platform = substr($plugin_version, 0, 2);
	$version = substr($plugin_version, 2);
	$posting_intro = $platformwrapper->yk_get_post_intro($post_id, 1000);

	$post_url = $platformwrapper->yk_get_post_url($post_id);
	$post_date = $platformwrapper->yk_get_post_date($post_id);
	$blog_author_email = $platformwrapper->yk_get_post_author_email($post_id);

	// 2. Make a webservice call
	// New WS_API="postPost"
	$post_response = cp_http_post("", $cpserverurl, "/webservices/ykwebservice_front.php?method=postPost&site_key=$site_key&blog_id=$blog_id&blog_key=$blog_key&posting_id=$post_id&posting_title=".urlencode($post_title)."&posting_intro=".urlencode($posting_intro)."&posting_url=".urlencode($post_url)."&blog_author_email=$blog_author_email&plugin_version=$version&posting_tags=".urlencode($post_categories), 80);

	$post_response_values = explode(',',trim(substr($post_response,strpos($post_response,"CPRESPONSE")+10,strlen($post_response))));

	$resultArr = array();
	foreach($post_response_values as $item){
		list($key, $value) = explode('=',$item, 2);
		$resultArr[$key] = $value;
	}

	if ($resultArr["Success"] == 'Y'){
		$query_email = "select email_address from ".$sz_table_prefix."sz_email where email_address = '".$blog_author_email."'";
		$row_count = $platformwrapper->yk_num_rows($query_email);
		$encoded_email = $resultArr["encoded_email"];
		$global_name = $resultArr["Global_Name"];
		$yk_score = $resultArr["YKScore"];

		// 3. Store the YK score of the author
		if ($row_count == 1){
			$query_update_yk_score = "update ".$sz_table_prefix."sz_email set yk_score = $yk_score , global_name = '$global_name' where email_address = '$blog_author_email'";
			$platformwrapper->yk_query($query_update_yk_score);
		}else{
			$query_insert_yk_score = sprintf("insert into ".$sz_table_prefix."sz_email(email_address,yk_score,global_name, encoded_email) values('%s','%d','%s','%s')", $blog_author_email, $yk_score, $global_name, $encoded_email);
			$platformwrapper->yk_query($query_insert_yk_score);
		}

		$raw_score = ($yk_score - 5) * 10;
		$post_score;
		if ($raw_score >= 1)
			$post_score = log($raw_score, 5) +5;
		else if ($raw_score <= -1)
			$post_score = (-1*log(-1*$raw_score, 5))+5;
		else
			$post_score = 5;


		// 4. Save the post to sz_
		$query_insert_post = sprintf ("insert into ".$sz_table_prefix."sz_post (blog_id, posting_id, creation_date, post_score, raw_score, rating_count, email_address) values(%d,'%s','%s',%f,%f,%d,'%s')", $blog_id, $post_id, $post_date, $post_score, $raw_score, 0, $blog_author_email);

		$platformwrapper->yk_query($query_insert_post);
	} // success

}

// The voting buttons go here
// We do not apply the content filter as that is implicit if not excerpt is
// present
add_filter('the_content','bootstrap_post_voting_buttons_content');
add_filter('the_excerpt','bootstrap_post_voting_buttons_excerpt');
add_filter('the_author','bootstrap_post_author');

function bootstrap_post_voting_buttons_content($text){
	global $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return $text;
	
	if (is_admin() || is_feed()) return $text;
	$post_id = get_the_ID();
	$partial_post = false;
	if (preg_match('/class=\"more-link\"/',$text)){
		// See if we need to handle anything for the content part in the
		$partial_post=true;
	}else{
		$partial_post = false;
	}
		return bootstrap_post_voting_buttons($text, $partial_post);
}
function bootstrap_post_voting_buttons_excerpt($text){
	global $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return $text;
	
	if (is_admin() || is_feed()) return $text;
	// TODO: Rendered in the single page when except is present
	return bootstrap_post_voting_buttons($text, true);
}

function bootstrap_post_voting_buttons($text,$partial_post){
	global $wpdb, $platformwrapper, $sz_global_script_string;
	global $sz_options,$sz_post;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return $text;
	
	$post_id = get_the_ID();
	yk_post_data_load(false, $post_id);
	
	$wp_prefix = $sz_options->sz_wp_db_prefix;

	$blog_id = $sz_options->blog_wp_id;
	$site_key = $sz_options->sz_site_key;
	$blog_key = $sz_options->sz_blog_key;

	$blog_author_encoded_email = $sz_post->post_author_email_encoded;
	$sz_score = $sz_post->sz_post_author_score;
	$email_address = $sz_post->post_author_email;

	// No encoded email means no activation of post rating
	if (!$blog_author_encoded_email)
		return $text;

	$blog_author_name = $sz_post->post_author_display_name;
	$rating_count = $sz_post->sz_post_rating_count;
	$post_score = $sz_post->sz_post_score;
	insert_header_js_code();
	dump_sz_global_params();
		
	$sz_global_script_string .= '<script type="text/javascript"> if (!sz_post_config_params){var sz_post_config_params = new Array();} sz_post_config_params["'.$post_id.'"] = {post_id:"'.$post_id.'",blog_author_email:"'.$blog_author_encoded_email.'",md5email:"'.md5(strtolower($email_address)).'",sz_score:"'.$sz_score.'",post_score:"'.$post_score.'",blog_author_name:"'.rawurlencode($blog_author_name).'",rating_count:"'.$rating_count.'"}; </script>';


	if ( $sz_options->sz_enable_auto_layout_post && !$partial_post) $footer_html = cp_post_ratingbar($post_id, $sz_post->sz_post_score_for_display, $sz_post->sz_post_rating_count);
	else $footer_html = "";
	

	$text .= $footer_html;

	return $text;
}
function bootstrap_post_author($text){
	global $sz_options, $platformwrapper;
	
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return $text;
	
	if (is_admin() || is_feed() || !$sz_options->sz_enable_auto_layout_post) return $text;
	$text = cp_post_author_image($text).$text;
	if ($sz_options->sz_enable_post_cmo_link) 
		$text = $text.cp_post_author_link();
	return $text;
}

function szHandleQueryParams() {
	if ($_GET["sz_syncblog"] != null) {
		include(ABSPATH . '/wp-content/plugins/sezwho/syncblog.php');
		exit;
	} else if ($_GET["sz_postRating"] != null) {
		include(ABSPATH . '/wp-content/plugins/sezwho/cpratingsubmit.php');
		exit;
	} else if ($_GET["method"] == "UpdateComment" | $_GET["method"] == "UpdateRating") {
		include(ABSPATH . '/wp-content/plugins/sezwho/pluginservice.php');
		exit;
	}
	if ($_GET["sz_activatekey"] != null) {
		global $platformwrapper;
		if (!$platformwrapper->yk_get_blog_key()) {
			$blogkey = trim($_GET['key']);
			$is_sync = $_GET['sync'];
			if (!isset($is_sync) || $is_sync == null) $is_sync = 1;
			youkarma_activate ($blogkey, $is_sync);
		}
		else echo "Blog Key already active";
		exit;
	}
	if($_GET["cp"] !== null) {  // Paginated Comments
		global $sz_comment_page_number;
		$sz_comment_page_number = $_GET["cp"];
	}
}
add_action('template_redirect', 'szHandleQueryParams');
function sz_get_footer(){
	global $sz_global_script_string, $platformwrapper;
	if($platformwrapper->yk_get_blog_key() =="" || $platformwrapper->yk_get_blog_key() == NULL) return;
	
	if ($GLOBALS['szGlobalsFooterAlreadyAdded']) return;
	echo $sz_global_script_string.sz_dump_js_exec_command();
	sz_get_images();
	$GLOBALS['szGlobalsFooterAlreadyAdded'] = 1;
}

function sz_get_images() {
	global $sz_global_img_repo_arr;
	$total_img = count($sz_global_img_repo_arr);
	$count = 0;
	$max_img_in_call = 15;
	$img_repo_args = "";
	for($i=0; $i<$total_img; $i++) {
		$img_repo_args .= ( ($img_repo_args=="") ? "" : "," ) . $sz_global_img_repo_arr[$i];
		$count++;
		if( ($count>=$max_img_in_call) || ($i==$total_img-1) ) {
			echo "<script type='text/javascript' src='http://image.sezwho.com/getpic4.php?args=".$img_repo_args."'></script>";
			$img_repo_args = "";
			$count = 0;
		}
	}
	return;
}

add_action('get_footer', 'sz_get_footer');
add_action('wp_footer', 'sz_get_footer');

/* call it as echo timeDiff('2008-05-27 12:04:16') */
function timeDiff($timestr,$detailed=false, $max_detail_levels=8, $precision_level='second'){
	// All our profile dates are sent as GMT
	// hence, reset the timezone otherwise strtotime will mess it up
	date_default_timezone_set('GMT');
	$timestamp = strtotime($timestr);
	if ($timestamp <= 0){
		// Format here if you wanna output text to change
		return "sometime ago";
	}
    $now = time();

    if ($timestamp >= $now) $timestamp = $now;
    
    // We don't use away in the public profile but can be applied to 
    // admin screens when a draft is saved for sezwho enabled posts
    ($timestamp > $now) ? $action = 'away' : $action = 'ago';
   
    $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths = array(1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600);

    $diff = ($action == 'away' ? $timestamp - $now : $now - $timestamp);
   
    $prec_key = array_search($precision_level,$periods);
   
    // round diff to the precision_level
    $diff = round(($diff/$lengths[$prec_key]))*$lengths[$prec_key];
   
    // very small, display for ex "just seconds ago"
    if ($diff <= 10) {
        $periodago = max(0,$prec_key-1);
        $agotxt = $periods[$periodago].'s';
        
        // Format here if you wanna output text to change
        return "just $agotxt $action";
    }
   
    $time = "";
    for ($i = (sizeof($lengths) - 1); $i>0; $i--) {
        if($diff > $lengths[$i-1] && ($max_detail_levels > 0)) {        
            $val = floor($diff / $lengths[$i-1]);    
            $time .= $val ." ". $periods[$i-1].($val > 1 ? 's ' : ' ');  
            $diff -= ($val * $lengths[$i-1]);    
            if(!$detailed) { $i = 0; }  
            $max_detail_levels--;
        }
    }
 
    // Basic error checking.
    if($time == "") {
        return "";
    } else {
    	// Format here if you wanna output text to change
        return $time." ".$action;
    }
}

/**
 * NEW FUNCTION
 *
 * Function to output a thumbs up and down ratings button pair in a format
 * freindly to inline page display. Buttons with default text are overridden with CSS
 * 
 * SezWho may or may not want to replace some of this functionality to do hovers exactly how they want to
 */
function cp_comment_footer_thumb_rating($c_id=false) {
	global $sz_comment_iteration_num, $sz_comment_score, $sz_comment_rating_count, $platformwrapper, $comment_author_email_enc, $sz_comment_id, $sz_comment_score;
	global $sz_options;
	
	if($c_id !== null && $c_id !== false) {
		$reply_link = '<a href="#" class="sz_reply" onclick="sz_add_comment(\'comment-'.$c_id.'\', '.$c_id.', true); return false;">'.
		   			  __('Reply').'</a>';
	}
	
	if(!$sz_options->sz_enable_comment_rating){
		return '<p>'.$reply_link.'</p>';
	}

	yk_comment_data_gen();
	if( !$sz_comment_id || !$comment_author_email_enc || (0 == $sz_comment_score))
		return '<p>'.$reply_link.'</p>';
		
	$sz_rt_text_anchor = $sz_options->sz_rating_text_anchor;
	if(!$sz_rt_text_anchor)
		$sz_rt_text_anchor = __("Rate this");

	$width = ((float)$sz_comment_score);
	$width = (int)($width*10);
	$print_score = sprintf("%1.1f", $sz_comment_score/2);

	$result_str =  "<table class='cpEmbedPageTable' style='width:auto'>";
	$result_str .=  "<tr>";
	// reply text
	if($c_id !== null && $c_id !== false) {
		$result_str .= '<td class="cpEmbedPageTableCell sz_reply_footer_table_cell">'.$reply_link.' <span class="sz_footer_sep">|</span> </td>';
	}
	$result_str .=  "<td class='cpEmbedPageTableCell'>";
	$result_str .= "<span class='cpEmbedPageCommFooterCS'><a href='javascript:void(0);' onmousedown='SezWho.DivUtils.activateRatingsHelpDIV(event);'>$sz_rt_text_anchor</a>: </span>";
	$result_str .=  "</td>";	
// new formatting
	$result_str .= '<td class="cpEmbedPageTableCell sz_thumb_rating_cell">';
	$result_str .=  '<ul class="cpEmbedThumbRating">';
	$result_str .=  '<li class="cpEmbedRateDown" value="no" id="sz_rating_button:'.$sz_comment_iteration_num.'"  onmousedown="SezWho.Utils.szHandleRatingButtonClicks(event, 0,this)">Rate this down</li>';
	$result_str .=  '<li class="cpEmbedRateUp" value="yes" id="sz_rating_button:'.$sz_comment_iteration_num.'" onmousedown="SezWho.Utils.szHandleRatingButtonClicks(event, 10,this)">Rate this up</li>';
	$result_str .=  '</ul>';
//	$result_str .=  '<ul class="cpEmbedThumbRating"><li class="cpEmbedRateDown" value="no" id="sz_rating_button:'.$sz_comment_iteration_num.'"  onmousedown="SezWho.Utils.szHandleRatingButtonClicks(event, 0,this)">Rate this down</li> <li class="cpEmbedRateUp" value="yes" id="sz_rating_button:'.$sz_comment_iteration_num.'" onmousedown="SezWho.Utils.szHandleRatingButtonClicks(event, 10,this)">Rate this up</li></ul>';
	$result_str .= '</td>';
// end new formatting
	$result_str .= "<td class='cpEmbedPageTableCell'>";
	$result_str .= '<span id="cpEmbedCommScoreSpan:'.$sz_comment_iteration_num.'" class="cpEmbedCommScoreSpan" title="'.$print_score.'">'.$print_score.'</span>';

	if ($sz_comment_rating_count)
		if ($sz_comment_rating_count == "1")
		$result_str .= '<span id="cpEmbedCommCountSpan:'.$sz_comment_iteration_num.'" class="cpEmbedCommCountSpan" title="'.$sz_comment_rating_count.'"> ('.$sz_comment_rating_count.' person)</span>';
		else
		$result_str .= '<span id="cpEmbedCommCountSpan:'.$sz_comment_iteration_num.'" class="cpEmbedCommCountSpan" title="'.$sz_comment_rating_count.'"> ('.$sz_comment_rating_count.' people)</span>';
	else
		$result_str .= '<span id="cpEmbedCommCountSpan:'.$sz_comment_iteration_num.'" class="cpEmbedCommCountSpan" title="'.$sz_comment_rating_count.'"></span>';

	$result_str .=  "</td>";
	$result_str .= '</tr>';
	$result_str .= '</table>';
	return $result_str;
}


/**
 * NEW FUNCTION
 * add the threaded comments parent ID to comment
 */
function cp_comment_set_parent($id) {
	global $tablecomments, $wpdb;
	if( isset($_POST['sz_comment_parent']) && (int) $_POST['sz_comment_parent'] > 0) {
		$parent_id = $wpdb->escape( (int) $_POST['sz_comment_parent']);
		$q = $wpdb->query("UPDATE $tablecomments SET comment_parent='{$parent_id}' WHERE comment_ID='{$id}'");
	}
}
add_action('comment_post','cp_comment_set_parent'); // attach previous function

// add jquery in the right order
wp_enqueue_script('jquery');
wp_enqueue_script('jquerycolor','/wp-includes/js/jquery/jquery.color.js',array('jquery'),'1.0');
if (!function_exists('wp_prototype_before_jquery')) {
	function wp_prototype_before_jquery( $js_array ) {
		if ( false === $jquery = array_search( 'jquery', $js_array ) )
			return $js_array;
		if ( false === $prototype = array_search( 'prototype', $js_array ) )
			return $js_array;
		if ( $prototype < $jquery )
			return $js_array;
		unset($js_array[$prototype]);
		array_splice( $js_array, $jquery, 0, 'prototype' );
		return $js_array;
	}
    add_filter( 'print_scripts_array', 'wp_prototype_before_jquery' );
}

// add css
// this code designed to be removed by SezWho for delivery via Amazon
function cp_add_css_head() {
	global $sz_options;
	if($sz_options->sz_enable_comments_template == 1) {
		echo '<link rel="stylesheet" type="text/css" href="'.get_settings('siteurl').'/wp-content/plugins/sezwho/sezwho_comments/'.get_option('sz_comment_theme').'" media="screen" />'.'<script type="text/javascript" src="'.get_settings('siteurl').'/wp-content/plugins/sezwho/sezwho_comments/replyform.js"></script>';
	}
}
add_action('wp_head', 'cp_add_css_head');
add_filter('comments_template', 'sz_comments_template');
function sz_comments_template ($value) {
	global $sz_options;
	$GLOBALS['szGlobalsCommentTemplateOn'] = true;
	if($sz_options->sz_enable_comments_template == 1) {
		$template = ABSPATH.'/wp-content/plugins/sezwho/comments_template.php';
		if(is_file($template)){ return $template; }
	}
	return $value;
}

add_filter('get_sidebar', 'sz_sidebar_template');
add_filter('get_footer', 'sz_sidebar_template');
function sz_sidebar_template ($value) {
	$GLOBALS['szGlobalsCommentTemplateOn'] = false;
	return $value;
}

?>
