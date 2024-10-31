<?php
ini_set("display_errors" , false);
ini_set('allow_url_fopen', 'On');
if ( !defined('ABSPATH') )   {
	// include wp-config as it has the ABSPATH initialization
	$root = dirname(dirname(dirname(dirname(__FILE__))));
	if (file_exists($root.'/wp-load.php')) { 	// WP 2.6		
		require_once($root.'/wp-load.php');
	} else {	// Before 2.6		
		require_once($root.'/wp-config.php');
	}
}
require_once(ABSPATH.'wp-content/plugins/sezwho/platformwrapper.php');
include_once(ABSPATH.'wp-content/plugins/sezwho/cpconstants.php');
$platformwrapper = platformwrapper::getInstance();

$output = '';

	global $wpdb, $sz_widgets_dynamic, $sz_global_script_string;
	global $sz_table_prefix;
	$sz_comment_iteration_num = 0;
	$site_key;
	$rc_comments_info_arr='';
	$wp_prefix = $wpdb->prefix ;
	//default values
	$rc_limit_comments = 5;
	$sz_rc_img_width = 40;
	$sz_rc_img_height = 40;
	$sz_rc_width = "";
	$sz_rc_color = "transparent";

	// Each widget can store its own options. We keep strings here.
	$options = get_option('widget_sezwho_rc');
	if ( is_array($options) )
	{
		if($options['sz_rc_rows_num'])
			$rc_limit_comments = $options['sz_rc_rows_num'];
		if($options['sz_rc_img_width'])
			$sz_rc_img_width = $options['sz_rc_img_width'];
		if($options['sz_rc_img_height'])
			$sz_rc_img_height = $options['sz_rc_img_height'];
		if($options['sz_rc_width'])
			$sz_rc_width = $options['sz_rc_width'];
		if($options['sz_rc_color'])
			$sz_rc_color = $options['sz_rc_color'];
	}

	$query= "select blog_id,blog_key,site_key from ".$sz_table_prefix."sz_blog";
	$row = $platformwrapper->yk_get_row($query) ;
	$blog_id=$row->blog_id;
	$blog_key=$row->blog_key;
	$site_key = $row->site_key;
	$comment_filter_date = date("Y-m-d", time() - 180*86400);

	$commenters_query =
	"SELECT email_address, encoded_email, yk_score, global_name, (
			SELECT comment_author_url
			FROM ".$wp_prefix."comments
			WHERE comment_author_email = ".$sz_table_prefix."sz_email.email_address
			ORDER BY comment_date DESC
			LIMIT 1
) AS comment_author_url, (
			SELECT comment_author
			FROM ".$wp_prefix."comments
			WHERE comment_author_email = ".$sz_table_prefix."sz_email.email_address
			ORDER BY comment_date DESC
			LIMIT 1
		) AS comment_author, (
				SELECT comment_ID
				FROM ".$wp_prefix."comments
				WHERE comment_author_email = ".$sz_table_prefix."sz_email.email_address
				ORDER BY comment_date DESC
				LIMIT 1
			) AS comment_id, (
				SELECT comment_post_ID
				FROM ".$wp_prefix."comments
				WHERE comment_author_email = ".$sz_table_prefix."sz_email.email_address
				ORDER BY comment_date DESC
				LIMIT 1
			) AS comment_post_id FROM ".$sz_table_prefix."sz_email WHERE EXISTS (
		SELECT * FROM ".$sz_table_prefix."sz_comment
		WHERE ".$sz_table_prefix."sz_comment.blog_id = $blog_id
		AND ".$sz_table_prefix."sz_comment.email_address = ".$sz_table_prefix."sz_email.email_address
		AND ".$sz_table_prefix."sz_comment.creation_date > $comment_filter_date
	) ORDER BY yk_score DESC LIMIT $rc_limit_comments " ;

	$commenters_result = mysql_query($commenters_query);

	if(!$sz_widgets_dynamic)
	{
		$output .= "<table class='szRCEmbedTableClass' style='width:".$sz_rc_width."px;'>";
		$output .= "<thead><tr><th colspan='2'><span id='szRCEmbedTitleID'>Red Carpet</span></th></tr></thead>";
                $output .= "<tfoot><tr><td colspan='2' class='szRCEmbedBrandingCell' align='center'>";
		if(strstr($_SERVER['HTTP_USER_AGENT'],"MSIE 6.0") || strstr($_SERVER['HTTP_USER_AGENT'],"MSIE 5.5")) $output .= "<a class='cpEmbedPageTableCellBrandingLink cpEmbedPageTableCellBrandingLinkIE' href=\"javascript:void SezWho.Utils.openPage('home');\"></a>";
		else $output .=  "<a class='cpEmbedPageTableCellBrandingLink' href=\"javascript:void SezWho.Utils.openPage('home');\"></a>";

		$output .= "</td></tr></tfoot>";
		$output .= '<tbody>';
	}
	
	$sz_rc_img_repo_arr = array();

	while ($commenters_obj = mysql_fetch_object($commenters_result)) {
		$email_address = $commenters_obj->email_address ;
		$md5_email_address = md5(strtolower($email_address));
		$yk_score = $commenters_obj->yk_score ;
		$global_name = $commenters_obj->global_name ;
		$comment_author_url = $commenters_obj->comment_author_url ;
		$comment_author = $commenters_obj->comment_author ;
		$comment_id = $commenters_obj->comment_id ;
		$enc_comment_author_email = $commenters_obj->encoded_email;
		$entryId = $commenters_obj->comment_post_id;
		$post_url = get_settings('home').'/?p='.$entryId ;
		$comment_url = $post_url.'#comment-'.$comment_id ;
		if($sz_widgets_dynamic)
		{
			if($rc_comments_info_arr == '')
				$rc_comments_info_arr .= "{'comment_id':'".$comment_id."', 'comment_author':'".rawurlencode($comment_author)."', 'comment_author_url':'".$comment_author_url."', 'comment_author_email':'".$enc_comment_author_email."','sz_score':'$yk_score','comment_score':'0','md5_email':'$md5_email_address', 'comment_url':'".$comment_url."'}";
			else
				$rc_comments_info_arr .= ", {'comment_id':'".$comment_id."', 'comment_author':'".rawurlencode($comment_author)."', 'comment_author_url':'".$comment_author_url."', 'comment_author_email':'".$enc_comment_author_email."','sz_score':'$yk_score','comment_score':'0','md5_email':'$md5_email_address', 'comment_url':'".$comment_url."'}";
		}
		else
		{
			$output .= '<tr>';
			$output .= "<td><a href='".$comment_author_url."'>";
			$output .= "<img src='http://s3.amazonaws.com/sz_users_images/noimg.gif' alt='no image' title='no image' class='szRCEmbedImage' onerror=\"this.src='http://s3.amazonaws.com/sz_users_images/noimg.gif'\" id='sz_rc_image_link:".$comment_id."' style='width:".$sz_rc_img_width."px;height:".$sz_rc_img_height."px;' />";
			$val = $md5_email_address . ":" . "sz_rc_image_link:" . $comment_id;
			if (!in_array($val, $sz_rc_img_repo_arr))
				$sz_rc_img_repo_arr[] = $val;
			$output .= "</a></td>";
			$output .= "<td><a id='sz_profile_link:".$sz_comment_iteration_num."' class='szEmbedCommeterName' onmouseover='javascript:SezWho.Utils.cpProfileRCEventHandler(event)' onmousedown='javascript:SezWho.Utils.cpProfileRCEventHandler(event)' href='' onmouseout='javascript:SezWho.DivUtils.cancelPopUp();'>".$comment_author."</a><br>".get_star_rating($yk_score)."<br><a class='szEmbedCommetLink' href='' onclick='javascript:SezWho.Utils.szClick(\"CC\",\"".$comment_url."\",\"0\",\"0\");' >View Comment</a></td>";
			$rc_comments_info_arr .= "sz_rc_config_params.sz_rc_data[".$sz_comment_iteration_num."]= {comment_id:'".$comment_id."', comment_author:'".rawurlencode($comment_author)."', comment_author_url:'".$comment_author_url."', comment_author_email:'".$enc_comment_author_email."',sz_score:0,comment_score:0};";
			$output .= '</tr>';
			$output .= "<tr><td colspan='2' class='szRCEmbedTableSeperator'></td></tr>";
		}

		$sz_comment_iteration_num++;
	}

	if(!$sz_widgets_dynamic)
	{
		$output .= '</tbody>';
		$output .= '</table>';
	}


if($sz_widgets_dynamic)
	$sz_global_script_string .=  'SezWho.DivUtils.handleRedCarpetData (['.$rc_comments_info_arr.'], {"sz_rc_rows":"'.$sz_comment_iteration_num.'", "sz_rc_img_width":"'.$sz_rc_img_width.'","sz_rc_img_height":"'.$sz_rc_img_height.'","sz_rc_color":"'.$sz_rc_color.'","sz_rc_width":"'.$sz_rc_width.'"});';
else {
	$sz_global_script_string .= "<script type='text/javascript'> ".$rc_comments_info_arr."</script>" ;
	echo $output;
}
sz_get_images_for_rc($sz_rc_img_repo_arr, $sz_widgets_dynamic);

function sz_get_images_for_rc($sz_rc_img_repo_arr, $sz_widgets_dynamic) {
	global $sz_global_script_string;
	$total_img = count($sz_rc_img_repo_arr);
	$count = 0;
	$max_img_in_call = 15;
	$img_repo_args = "";
	for($i=0; $i<$total_img; $i++) {
		$img_repo_args .= ( ($img_repo_args=="") ? "" : "," ) . $sz_rc_img_repo_arr[$i];
		$count++;
		if( ($count>=$max_img_in_call) || ($i==$total_img-1) ) {
			if($sz_widgets_dynamic)
			{
				$sz_global_script_string .= "var script = document.createElement('script');";
				$sz_global_script_string .= "script.src = 'http://image.sezwho.com/getpic4.php?args=".$img_repo_args."';";
				$sz_global_script_string .= "document.getElementsByTagName('head')[0].appendChild(script);";
			}
			else 
			{
				$sz_global_script_string .= "<script type='text/javascript' src='http://image.sezwho.com/getpic4.php?args=".$img_repo_args."'></script>";
			}
			$img_repo_args = "";
			$count = 0;
		}
	}
	return;
}
?>
