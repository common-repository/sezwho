<?php
if ( !defined('ABSPATH') )   {
	// include wp-config as it has the ABSPATH initialization
	$root = dirname(dirname(dirname(dirname(__FILE__))));
	if (file_exists($root.'/wp-load.php')) { 	// WP 2.6		
		require_once($root.'/wp-load.php');
	} else {	// Before 2.6		
		require_once($root.'/wp-config.php');
	}
}
include_once(ABSPATH.'/wp-content/plugins/sezwho/platformwrapper.php');
include_once(ABSPATH.'/wp-content/plugins/sezwho/cpconstants.php');
global $sz_table_prefix;

$wpwrapper = platformwrapper::getInstance();
$method = $_GET["method"] ;
if ($method == "UpdateComment") {
	$blog_id = $_GET["blog_id"];
	$posting_id = $_GET["posting_id"];
	$comment_id = $_GET["comment_id"];
	$raw_score = $_GET["raw_score"];
	$temp_exclude_flag = $_GET["exclude_flag"];
	$exclude_flag = ($temp_exclude_flag == "") ? "NULL" : $temp_exclude_flag ;
	$commenter_yk_score = $_GET["commenter_yk_score"] ;
	$commenter_global_name = $_GET["commenter_global_name"] ;
	$encoded_email = urldecode($_GET["encoded_email"]);
	$email_address = urldecode($_GET["email_address"]);

	if($raw_score >= 1){
		$comment_score =log($raw_score,5)+5;
	}
	else if($raw_score <= -1) {
		$comment_score = (-1*log(-1*$raw_score, 5)) + 5;
	}
	else {
		$comment_score = 5;
	}

	$comment_update_query = "update ".$sz_table_prefix."sz_comment set comment_score = '$comment_score' , raw_score = '$raw_score' , rating_count = 0 , exclude_flag = '$exclude_flag' where comment_id = '$comment_id' and posting_id = '$posting_id' and blog_id = '$blog_id';";
	$comment_update_result = $wpwrapper->yk_query($comment_update_query);
	if ($commenter_yk_score != null || $commenter_global_name != null) {
		//updating Email table
		updateEmailTable($email_address, $commenter_global_name, $commenter_yk_score, $encoded_email);
	}
} else if ($method == "UpdateRating") {
	$blog_id = $_GET["blog_id"];
	$posting_id = $_GET["posting_id"];
	$comment_id = $_GET["comment_id"];
	$rating = $_GET["rating"];
	$rater_ykscore = $_GET["RaterYKScore"];
	$rater_email_address = $_GET["RaterEmail"];
	$encoded_email = urldecode($_GET["encoded_email"]);
	$commenter_email_address = $_GET["CommenterEmail"];
	$commenter_ykscore = $_GET["CommenterYKScore"];
	// assume that the yk score is that of the rater, then compute values and set onto the plugin db
	// see what TO DO about updating the YK score !!!
	if($rater_ykscore != null){
		$comment = $wpwrapper->yk_get_row("SELECT * FROM ".$sz_table_prefix."sz_comment WHERE comment_id='$comment_id' and posting_id='$posting_id' and blog_id = '$blog_id' LIMIT 1;");
		$raw_score = $comment->raw_score;
		$new_raw_score= $rater_ykscore*($rating-5) + $raw_score;
		$new_rating_count = $comment->rating_count + 1 ;
		$commentscore ;
		if($new_raw_score >=1) {
			$commentscore=5+ log($new_raw_score,5);
		} else if($new_raw_score <= -1) {
			$commentscore=-1*log((-1*$new_raw_score),10) +5;
		} else {
			$commentscore = 5;
		}
		$update_comment_query = "update ".$sz_table_prefix."sz_comment set comment_score='$commentscore' , raw_score='$new_raw_score' , rating_count = '$new_rating_count' WHERE comment_id='$comment_id' and posting_id='$posting_id' and blog_id = '$blog_id';";
		$wpwrapper->yk_query($update_comment_query);

		//updating Email table for commenter
		updateEmailTable($commenter_email_address, '', $commenter_ykscore, $encoded_email);
		//updating Email table for rater
		updateEmailTable($rater_email_address, '', $rater_ykscore, $encoded_email);
	}
} else if ($method == "UpdateEmail") {
	// do the encrypt / decrypt business later
	$global_name = $_GET["global_name"];
	$yk_score = $_GET["yk_score"];
	$encoded_email = urldecode($_GET["encoded_email"]);
	$email_address = $_GET["email_address"];

	//updating Email table
	updateEmailTable($email_address, $global_name, $yk_score, $encoded_email);
}

function updateEmailTable($email_address, $global_name, $yk_score, $encoded_email) {
	global $sz_table_prefix;
	$wpwrapper = platformwrapper::getInstance();
	$email_count_query = "select * from ".$sz_table_prefix."sz_email where email_address = '$email_address' ; ";
	$count = $wpwrapper->yk_num_rows($email_count_query);
	if ($count == 1) { // update
		if($global_name == '') {
			$email_update_query = "update ".$sz_table_prefix."sz_email set yk_score = '$yk_score' where email_address = '$email_address' ; " ;
		}
		else {
			$email_update_query = "update ".$sz_table_prefix."sz_email set global_name = '$global_name' , yk_score = '$yk_score' where email_address = '$email_address' ; " ;
		}
		$email_update_result = $wpwrapper->yk_query($email_update_query);
	} else {// if the update has failed, try an insertion
		if($global_name == '') {
			$email_insert_query = "insert into ".$sz_table_prefix."sz_email (email_address , yk_score, encoded_email) values ('$email_address', '$yk_score', '$encoded_email') ;" ;
		}
		else {
			$email_insert_query = "insert into ".$sz_table_prefix."sz_email (email_address , global_name, yk_score, encoded_email) values ('$email_address' , '$global_name' , '$yk_score', '$encoded_email') ;" ;
		}
		$wpwrapper->yk_query($email_insert_query);
	}
}
?>
