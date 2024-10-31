<?php
/*if ( !defined('ABSPATH') )   {
	// include wp-config as it has the ABSPATH initialization
	$root = dirname(dirname(dirname(dirname(__FILE__))));
	if (file_exists($root.'/wp-load.php')) {	// WP 2.6		
		require_once($root.'/wp-load.php');
	} else {	// Before 2.6		
		require_once($root.'/wp-config.php');
	}
}*/
require_once(ABSPATH.'/wp-content/plugins/sezwho/cpconstants.php');
ini_set('display_errors', false);
error_reporting(1);
global $httppostdata;
if(!isset($sz_sync_output_enabled)) $sz_sync_output_enabled = 1;

sz_sync_data($sz_sync_output_enabled);

function sz_sync_data($sz_sync_output_enabled) {
	global $platformwrapper, $httppostdata, $cpserverurl, $wpdb;
	global $sz_table_prefix;
	$err = 0;
	$sz_blog_id = $platformwrapper->get_sz_blogID();
	$blogurl = $platformwrapper->yk_get_settings("home") ;
	$sz_sync_block_size = 150;
	$cpserver=substr($cpserverurl, 7 ,strlen($cpserverurl));
	$wp_prefix = $platformwrapper->yk_get_db_prefix() ;

	// Get Site Key
	$sitequery = "select site_key from ".$sz_table_prefix."sz_site ";
	$row = $platformwrapper->yk_get_row($sitequery) ;
	$sitekey = $row->site_key;
	if(!$sitekey){
		if ($sz_sync_output_enabled) echo("<div class='updated fade-ff0000'>Error , site key not found</div>");
		return;
	}

	// Get Blog Key
	$blogquery ="select blog_key from ".$sz_table_prefix."sz_blog where blog_id = '$sz_blog_id'";
	$row = $platformwrapper->yk_get_row($blogquery) ;
	$blogkey = $row->blog_key;
	if(!$blogkey) {
		if ($sz_sync_output_enabled) echo("<div class='updated fade-ff0000'>Error , blog key not found</div>");
		return;
	}

	//Get the number of posts to sync
	$total_posts_to_sync_query = "select a.ID, a.guid, b.user_email, a.post_title, a.post_date, a.post_content from $wpdb->users b, {$wp_prefix}posts a LEFT JOIN ".$sz_table_prefix."sz_post c ON a.ID = c.posting_id AND c.blog_id=$sz_blog_id WHERE c.posting_id IS NULL AND a.post_status = 'publish' AND a.post_author = b.ID AND a.post_type = 'post';";

	$total_posts_to_sync_result = mysql_query($total_posts_to_sync_query);
	$total_posts_to_sync_num = mysql_num_rows($total_posts_to_sync_result);
	if (!$total_posts_to_sync_num) {
		if ($sz_sync_output_enabled) echo("No posts to sync" );
		if ($sz_sync_output_enabled) echo("<br>");
	}
	
	if ($total_posts_to_sync_num)
	{
		//Get the existing emails in the comment table in an array
		$ee_id_q = "select email_address from ".$sz_table_prefix."sz_email;";
		$ee_id_q_r = mysql_query($ee_id_q);
		$e_db_data = array();
		while ($obj = mysql_fetch_object($ee_id_q_r)) {
			$e_db_data[$obj->email_address] = 1;
		}
		if ($sz_sync_output_enabled) echo("To Process: ".$total_posts_to_sync_num." posts" );
		if ($sz_sync_output_enabled) echo("<br>");
	}

	$tosync = $total_posts_to_sync_num;
	$from = 1 ;
	while ($tosync > 0) {
		$s_chunk = ($tosync > $sz_sync_block_size)? $sz_sync_block_size : $tosync;
		$email_num = 0;
		$httppostdata = array();

		if($tosync > $sz_sync_block_size) {
			if ($sz_sync_output_enabled) echo("Processing posts from ".$from." to ".($from + $sz_sync_block_size - 1));
			if ($sz_sync_output_enabled) echo("<br>");
			$from = $from + $sz_sync_block_size;
		} else {
			if ($sz_sync_output_enabled) echo("Processing ".$tosync. " post" );
		}
		if ($sz_sync_output_enabled) echo("<br>");
		for ($i=0 ; $i < $s_chunk ; $i++)
		{
			$obj = mysql_fetch_object($total_posts_to_sync_result);
			// Insert the comment
			$comment_query= "INSERT INTO ".$sz_table_prefix."sz_post (blog_id, posting_id, creation_date, post_score, raw_score, rating_count, email_address, exclude_flag) VALUES ($sz_blog_id,".$obj->ID.", '".$obj->post_date."' , NULL, NULL, NULL, '".$obj->user_email."','S');";

			mysql_query($comment_query);
			
			// add the comments to the http array
			sz_dump_post ($i, $obj->ID, $obj->guid, $obj->post_title, $obj->post_content, $obj->post_date, $obj->user_email);

			// See if the email needs to be inserted
			if ($obj->user_email != '' && !array_key_exists ($obj->user_email, $e_db_data)) {
				$httppostdata["EMAIL-".$email_num] = urlencode($obj->user_email);
				$e_db_data[$obj->user_email] = 1;
				$email_num++;
			}
		}
		if ($sz_sync_output_enabled) echo $email_num;
		if ($sz_sync_output_enabled) echo("<br>");
		$httppostdata["POSTCOUNT"] = $s_chunk ;
		$httppostdata["COMMENTCOUNT"] = 0;
		$httppostdata["EMAILCOUNT"] = $email_num;
		$httppostdata["sitekey"]= $sitekey;
		$httppostdata["blogkey"]= $blogkey;
		$httppostdata["blogid"]= $sz_blog_id;

		$response = post_httppostdata($cpserver, $debug_file);

		sz_process_response($response, 1);
		if (!$response) {$err=1;break;}
		$tosync = $tosync - $sz_sync_block_size;
	}
	if ($err) echo("Server error in processing. Aborting posts sync...<br/>");
	else if ($sz_sync_output_enabled && $total_posts_to_sync_num) echo("Processed ".$total_posts_to_sync_num." posts<br>");
	$err = 0;

	//Get the number of comments to sync
	$total_comments_to_sync_query = "select a.comment_ID, a.comment_post_ID, a.comment_author_url, a.comment_author_email, a.comment_content, a.comment_date  from {$wp_prefix}comments a LEFT JOIN ".$sz_table_prefix."sz_comment b ON a.comment_ID = b.comment_id AND b.blog_id=$sz_blog_id WHERE b.comment_id IS NULL AND a.comment_approved = '1' AND a.comment_author_email != ''" ;
	$total_comments_to_sync_result = mysql_query($total_comments_to_sync_query);
	$total_comments_to_sync_num = mysql_num_rows($total_comments_to_sync_result);
	if (!$total_comments_to_sync_num) {
		if ($sz_sync_output_enabled) echo("No comments to sync" );
		if ($sz_sync_output_enabled) echo("<br>");
		return;
	}

	if (!isset($e_db_data))
	{
		//Get the existing emails in the comment table in an array
		$ee_id_q = "select email_address from ".$sz_table_prefix."sz_email;";
		$ee_id_q_r = mysql_query($ee_id_q);
		$e_db_data = array();
		while ($obj = mysql_fetch_object($ee_id_q_r)) {
			$e_db_data[$obj->email_address] = 1;
		}
	}
	$tosync = $total_comments_to_sync_num;

	if ($sz_sync_output_enabled) echo("To Process: ".$total_comments_to_sync_num." comments" );
	if ($sz_sync_output_enabled) echo("<br>");

	$from = 1 ;
	while ($tosync > 0) {
		$s_chunk = ($tosync > $sz_sync_block_size)? $sz_sync_block_size : $tosync;
		$email_num = 0;
		$httppostdata = array();

		if($tosync > $sz_sync_block_size) {
			if ($sz_sync_output_enabled) echo("Processing comments from ".$from." to ".($from + $sz_sync_block_size - 1));
			$from = $from + $sz_sync_block_size;
		} else {
			if ($sz_sync_output_enabled) echo("Processing ".$tosync. " comment" );
		}
		if ($sz_sync_output_enabled) echo("<br>");
		for ($i=0 ; $i < $s_chunk ; $i++)
		{
			$obj = mysql_fetch_object($total_comments_to_sync_result);
			// Insert the comment
			$comment_query= "INSERT INTO ".$sz_table_prefix."sz_comment (blog_id, posting_id, comment_id, creation_date, comment_rating, comment_score, raw_score, rating_count, email_address, exclude_flag) VALUES ($sz_blog_id,".$obj->comment_post_ID.",".$obj->comment_ID.", '".$obj->comment_date."' , NULL, NULL, NULL, NULL , '".$obj->comment_author_email."','S');";
			mysql_query($comment_query);
			// add the comments to the http array
			sz_dump_comment($i, $obj, $blogurl);
			// See if the email needs to be inserted
			if ($obj->comment_author_email != '' && !array_key_exists ($obj->comment_author_email, $e_db_data)) {
				$httppostdata["EMAIL-".$email_num] = urlencode($obj->comment_author_email);
				$e_db_data[$obj->comment_author_email] = 1;
				$email_num++;
			}
		}

		$httppostdata["POSTCOUNT"] = 0;
		$httppostdata["COMMENTCOUNT"] = $s_chunk ;
		$httppostdata["EMAILCOUNT"] = $email_num;
		$httppostdata["sitekey"]= $sitekey;
		$httppostdata["blogkey"]= $blogkey;
		$httppostdata["blogid"]= $sz_blog_id;
		$response = post_httppostdata($cpserver, $debug_file);
		sz_process_response($response, 0, $sz_sync_output_enabled);
		if (!$response) {$err=1;break;}
		$tosync = $tosync - $sz_sync_block_size;
	}
	if ($err) echo("Server error in processing. Aborting comments sync...<br/>");
	else if ($sz_sync_output_enabled) echo("Processed ".$total_comments_to_sync_num." comments" );
}

function sz_dump_comment($index, $comment_obj, $blogurl)
{
	global $httppostdata;
	$httppostdata["COMMENTPOSTID-".$index] = $comment_obj->comment_post_ID ;
	$httppostdata["COMMENT-".$index] = $comment_obj->comment_ID ;
	$httppostdata["COMMENTDATE-".$index] = urlencode($comment_obj->comment_date);
	$httppostdata["COMMENTAUTHORURL-".$index] = urlencode($comment_obj->comment_author_url);
	$httppostdata["COMMENTAUTHOREMAIL-".$index] = urlencode($comment_obj->comment_author_email);
	$httppostdata["COMMENTURL-".$index] = urlencode($blogurl.'/?p='.$comment_obj->comment_post_ID."#".$comment_obj->comment_ID);

	//$chars_tobe_replaced = array("\n","\r");
	//$replace_with = " ";
	$httppostdata["COMMNETINTRO-".$index] = urlencode(substr($comment_obj->comment_content, 0, 2000));
}

function sz_dump_post($index, $pid, $blogurl, $post_title, $post_content, $post_date, $post_email)
{
	global $platformwrapper, $httppostdata ;

	$httppostdata["POSTID-".$index] = $pid ;
	$httppostdata["POSTURL-".$index] = urlencode($blogurl);
	$httppostdata["POSTTITLE-".$index] = urlencode(substr($post_title, 0, 200));
	$httppostdata["POSTEMAIL-".$index] = urlencode($post_email);
	//$chars_tobe_replaced = array("\n","\r");
	//$replace_with = " ";
	$httppostdata["POSTINTRO-".$index] = urlencode(substr($post_content, 0, 1000));
	$httppostdata["POSTDATE-".$index] = urlencode($post_date);
	// Handle the categories
	$httppostdata["POSTTAGS-".$index] = urlencode($platformwrapper->yk_get_categories($pid));
}

function post_httppostdata($cpserver, $debug_file)
{
	global $httppostdata, $wp_version, $platformwrapper, $pluginversionminor, $pluginrevision;
	$plugin_version = $platformwrapper->yk_get_plugin_version();
	if (isset($pluginversionminor)) $plugin_version .= '.'.$pluginversionminor;
	if (isset($pluginrevision)) $plugin_version .= '.'.$pluginrevision;

	$keys = array_keys($httppostdata) ;
	$key ;
	$data;
	for($i =0 ; $i < count($keys) ; $i++)
	{
		$key = $keys[$i];
		$data = $data.$key."=".$httppostdata[$key]."&";
	}
	$eol = "\r\n";
	$errno = 0;
	$errstr = '';
	$fid = fsockopen($cpserver, 80, $errno, $errstr, 90);
	if ($fid)
	{
		$http_request  = "POST /webservices/yksyncblogservice.php HTTP/1.0\r\n";
		$http_request .= "Host: ".$cpserver."\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded; \r\n";
		$http_request .= "Content-Length: ".strlen($data)."\r\n";
		$http_request .= "User-Agent: WordPress/$wp_version | SezWho/$plugin_version\r\n";
		$http_request .= "\r\n";
		$http_request .= $data;
		
		fwrite($fid, $http_request);
		$content = "";
		while (!feof($fid)) {
			$content .= fgets($fid, 1160);
		}
		fclose($fid);
		if (!strpos($content,"CPRESPONSE")) return null;
		return $content;
	}
	return null;
}

function sz_process_response($resp, $is_post, $sz_sync_output_enabled)
{
	global $sz_table_prefix;
	$response =trim(substr($resp,strpos($resp,"CPRESPONSE")+10,strlen($resp)));
	$returned_values = explode('|', $response) ; // split at the commas
	if(!empty($resp) && trim($returned_values[0]) != "SUCCESS=N") {
		foreach($returned_values as $item)
		{
			$row = explode(',',$item);
			$cols = array();
			foreach($row as $item2) {
				list($key, $value) =  explode('=', $item2);
				$cols[$key] = $value;
			}
			$email =$cols['EMAIL'];
			$ykscore =$cols['YKSCORE'];
			$globalname =$cols['GLOBALNAME'];
			$encoded_email = $cols['ECRYPTED_EMAIL'];
			if($email != '') {
				$insquery ="Insert into ".$sz_table_prefix."sz_email (email_address,yk_score,global_name, encoded_email) values ('".$email."','".$ykscore."','".$globalname."', '".$encoded_email."')";
				mysql_query($insquery);
			}
		}
		//Compute and update comment score
		if ($is_post)
		{
			$raw_score_query ="update ".$sz_table_prefix."sz_post ,".$sz_table_prefix."sz_email set ".$sz_table_prefix."sz_post.raw_score=(".$sz_table_prefix."sz_email.yk_score-5)*10 where ".$sz_table_prefix."sz_post.exclude_flag='S' and ".$sz_table_prefix."sz_post.email_address=".$sz_table_prefix."sz_email.email_address;";
			mysql_query($raw_score_query);
			$post_score_query = "update ".$sz_table_prefix."sz_post set post_score = log(5, raw_score) + 5  where exclude_flag='S' and ".$sz_table_prefix."sz_post.raw_score > 1 ";
			mysql_query($post_score_query);
			$post_score_query = "update ".$sz_table_prefix."sz_post set post_score = -1 * log(5, raw_score*-1) + 5  where exclude_flag='S' and ".$sz_table_prefix."sz_post.raw_score < 1 ";
			mysql_query($post_score_query);
			$post_score_query = "update ".$sz_table_prefix."sz_post set post_score=5  where exclude_flag='S' and ".$sz_table_prefix."sz_post.raw_score >= -1 and ".$sz_table_prefix."sz_post.raw_score <= 1";
			mysql_query($post_score_query);
			// Fix the exclude flag
			$exculde_flag_update_query ="update ".$sz_table_prefix."sz_post set exclude_flag = NULL where  exclude_flag ='S' ";
			mysql_query($exculde_flag_update_query);
		}
		else
		{
			$raw_score_query ="update ".$sz_table_prefix."sz_comment ,".$sz_table_prefix."sz_email set ".$sz_table_prefix."sz_comment.raw_score=(".$sz_table_prefix."sz_email.yk_score-5)*10 where ".$sz_table_prefix."sz_comment.exclude_flag='S' and ".$sz_table_prefix."sz_comment.email_address=".$sz_table_prefix."sz_email.email_address;";
			mysql_query($raw_score_query);
			$comment_score_query = "update ".$sz_table_prefix."sz_comment set comment_score = log(5, raw_score) + 5  where exclude_flag='S' and ".$sz_table_prefix."sz_comment.raw_score > 1 ";
			mysql_query($comment_score_query);
			$comment_score_query = "update ".$sz_table_prefix."sz_comment set comment_score = -1 * log(5, raw_score*-1) + 5  where exclude_flag='S' and ".$sz_table_prefix."sz_comment.raw_score < 1 ";
			mysql_query($comment_score_query);
			$comment_score_query = "update ".$sz_table_prefix."sz_comment set comment_score=5  where exclude_flag='S' and ".$sz_table_prefix."sz_comment.raw_score >= -1 and ".$sz_table_prefix."sz_comment.raw_score <= 1";
			mysql_query($comment_score_query);
			// Fix the exclude flag
			$exculde_flag_update_query ="update ".$sz_table_prefix."sz_comment set exclude_flag = NULL where  exclude_flag ='S' ";
			mysql_query($exculde_flag_update_query);
		}
	} else {
		$msgarr = explode('=',$returned_values[1]);
		$errmsg ;
		if(trim($msgarr[1]) =='SITEKEYERR')
		{
			$msgerr = 'Wrong site key !';
		}
		if(trim($msgarr[1]) =='BLOGKEYERR')
		{
			$msgerr = 'Wrong blog key !';
		}
		if(trim($msgarr[1]) =='NOCOMMENT')
		{
			$msgerr = 'No comment to sync !';
		}
		if(trim($msgarr[1]) == 'SYSERR')
		{
			$msgerr = ' System error ';
		}
		if ($sz_sync_output_enabled) echo("<div class='updated fade-ff0000'>".$msgerr."</div>");

		if ($is_post)
			$delete_comment_query ="delete from ".$sz_table_prefix."sz_post where  exclude_flag ='S'";
		else
			$delete_comment_query ="delete from ".$sz_table_prefix."sz_comment where  exclude_flag ='S'";

		mysql_query($delete_comment_query);
	}
}
?>
