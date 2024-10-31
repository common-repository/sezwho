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
if(isset($_GET['badge_no']))
	$number = $_GET['badge_no'];

$output = '';

	global $wpdb, $sz_widgets_dynamic, $sz_global_script_string;
	global $sz_table_prefix;
	$wp_prefix = $wpdb->prefix;
	$options = get_option('sezwho_badge');
	$email_address = $options[$number]['email'];
	if ( empty($email_address) )
		$email_address = '&nbsp;';

	$badge_query = "SELECT encoded_email, yk_score, global_name, (
				SELECT comment_author
				FROM ".$wp_prefix."comments
				WHERE comment_author_email = ".$sz_table_prefix."sz_email.email_address
				ORDER BY comment_date DESC
				LIMIT 1
			) AS comment_author FROM ".$sz_table_prefix."sz_email WHERE email_address='$email_address'";

	if($badge_obj = $platformwrapper->yk_get_row($badge_query))
	{
		$md5_email_address = md5(strtolower($email_address));
		$yk_score = $badge_obj->yk_score;
		$global_name = $badge_obj->global_name;
		$comment_author = $badge_obj->comment_author;
		$enc_comment_author_email = $badge_obj->encoded_email;

		if(!$sz_widgets_dynamic)
		{
			if(strstr($_SERVER['HTTP_USER_AGENT'],"MSIE 6.0") || strstr($_SERVER['HTTP_USER_AGENT'],"MSIE 5.5")) echo "<table class='szBadgeBody szBadgeBodyIE'>";
			else echo "<table class='szBadgeBody'>";
?>
				<tr><td class='szBadgeBodyFiller'></td></tr>
				<tr><td class='szBadgeBodyDataName'><a class='szBadgeBodyDataNameLink' id='sz_badge_profile_link:<?php echo $number; ?>' onmouseover='javascript:SezWho.Utils.cpProfileBadgeEventHandler(event)' onmousedown='javascript:SezWho.Utils.cpProfileBadgeEventHandler(event)' href='' onmouseout='javascript:SezWho.DivUtils.cancelPopUp();'><?php echo get_formatted_badge_name($comment_author); ?></a></td></tr>
				<tr><td class='szBadgeBodyDataSP'>Star Power:</td></tr>
				<tr><td class='szBadgeBodyDataButtons'><?php echo get_star_rating($yk_score, 'badge'); ?></td></tr>
				<tr><td class='szBadgeBodyLogo'><a class='szBadgeBodyLogoLink' href="javascript:void SezWho.Utils.openPage('home');"></a></td></tr>
			</table>
	<?php
			$badge_info_arr .= "sz_badge_config_params.sz_badge_data[".$number."]= {comment_author:'".rawurlencode($comment_author)."', comment_author_email:'".$enc_comment_author_email."'};";
			$sz_global_script_string .=  "<script type='text/javascript'>" . $badge_info_arr . "</script>";
		}
		else
		{
			$sz_global_script_string .=  'SezWho.DivUtils.handleBadgeData ({"widget_no":"'.$number.'","comment_author":"'.rawurlencode($comment_author).'", "comment_author_email":"'.$enc_comment_author_email.'", "formatted_name":"'.get_formatted_badge_name($comment_author).'", "sz_score":"'.$yk_score.'"});';
		}
	}
	?>
