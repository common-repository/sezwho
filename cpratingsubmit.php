<?php
// class to handle DB stuff on the YK server side
require_once("platformwrapper.php");
require_once(ABSPATH.'/wp-content/plugins/sezwho/cpconstants.php');
$platformwrapper = platformwrapper::getInstance();
global $sz_msg;
global $sz_rating_count;
$sz_rating_count = 0;

$post_id = $_GET["postID"] ;
$comment_id = $_GET["commentID"]  ;
$rater_email = $_GET["emailID"]  ;
$rating = $_GET["ratingIncrement"]  ;
$rating_type = 'C';
$callback = $_GET["callback"];
if ($comment_id == "0") $rating_type = 'P';


// pass these parameters into the method that will call the postRating webservice
$rating_response = processRequestDataAndInvokePostRatingWebservice($post_id, $comment_id, $rater_email, $rating, $rating_type) ;
if (isset($callback))
	echo $callback.'("' . $rating_response . '");';
else
	echo 'SezWho.DivUtils.handleRating("'.$rating_response.'",'.$sz_rating_count.');';
//echo ("//".$sz_msg);
//echo ("// rating count = ".$sz_rating_count);

function processRequestDataAndInvokePostRatingWebservice ($post_id, $comment_id, $rater_email, $rating, $rating_type) {
	global  $platformwrapper;
	global  $cpserverurl;
	global  $sz_msg;
	global  $sz_rating_count;
	global  $sz_table_prefix;
	
	$sz_blog_id = $platformwrapper->get_sz_blogID();
	$sz_rating_count = 0;
	$query= "select blog_id, blog_key,site_key from ".$sz_table_prefix."sz_blog where blog_id = '$sz_blog_id'";

	$row = $platformwrapper->yk_get_row($query) ;
	$blog_id=$row->blog_id;
	$blog_key=$row->blog_key;
	$site_key = $row->site_key;
	$postrating_ws_result ;
	$site_verification_query = "select rating_verification from ".$sz_table_prefix."sz_site where site_key = '".$site_key."';";
	$site_verification_query_res = $platformwrapper->yk_get_var($site_verification_query) ;
	if ( $platformwrapper->yk_num_rows($site_verification_query) == 0) {
		// log and return "no site data found error"
		$postrating_ws_result = 'Status=Failure,ErrorMsgCode=NoSiteKey';
		return $postrating_ws_result ;
	}
	$blog_query = "select blog_key from ".$sz_table_prefix."sz_blog where blog_id = '".$blog_id."';";
	$blog_query_count = 0 ;
	if ($platformwrapper->yk_num_rows($blog_query) == 0) {
		// log and return "no blog key found error"
		$postrating_ws_result = 'Status=Failure,ErrorMsgCode=NoBlogKey';
		return $postrating_ws_result ;
	}
	// get plugin version
	$plugin_version_query = "SELECT plugin_version from ".$sz_table_prefix."sz_site; ";
	$plugin_version = $platformwrapper->yk_get_var($plugin_version_query);
	// assume plugin version is of the type MT1.0 or WP2.1
	$platform = substr($plugin_version, 0, 2) ;
	$version= substr($plugin_version, 2) ;
	if ($rating_type == 'P'){
		$email_query = "select email_address from ".$sz_table_prefix."sz_post where posting_id = '".$post_id."' and blog_id = '".$blog_id."';" ;
	}else{
		$email_query = "select email_address from ".$sz_table_prefix."sz_comment where comment_id = '".$comment_id."' and posting_id = '".$post_id."' and blog_id = '".$blog_id."';" ;
	}
	$email_res = $platformwrapper->yk_get_var($email_query) ;
	$email_query = "select encoded_email from ".$sz_table_prefix."sz_email where email_address = '".$email_res."';" ;
	$email_res = $platformwrapper->yk_get_var($email_query) ;
	if($email_res == $rater_email){
		// log and return "self rating error"
		$postrating_ws_result = 'Status=Failure,ErrorMsgCode=SelfRating';
		return $postrating_ws_result ;
	} else {
		$postrating_ws_result = cp_http_post("",  $cpserverurl, "/webservices/ykwebservice_front.php?method=postRating&site_key=$site_key&blog_id=$blog_id&blog_key=$blog_key&posting_id=$post_id&comment_id=$comment_id&rating=$rating&email_address=".rawurlencode($rater_email)."&plugin_version=$version&rating_type=$rating_type", 80);
		$sz_msg = $postrating_ws_result;
	}
	// Strip CPRESPONSE from the webservice returned response
	$returned_values = explode(',', $postrating_ws_result); // split at the commas
	$resultArr = array();
	foreach($returned_values as $item) {
		list($key, $value) = explode('=', $item, 2); // split at the =
		$resultArr[$key] = $value;
	}
	// update rater's yk score
	$rater_ykscore = $resultArr["YKScore"] ;
	if ($rater_ykscore != null) {
		$update_email_query = "update ".$sz_table_prefix."sz_email set yk_score = '".$rater_ykscore."' where encoded_email = '".$rater_email."';" ;
		$platformwrapper->yk_query($update_email_query);
	}
	// get the status for any further processing
	$status = $resultArr["Status"] ;
	if ($status == "Success") {
		if ($rater_ykscore == ''){
			$rater_ykscore = 5 ; // in this case setting rater yk-score to default score
		}
		// 1. Process Rating
		if ($rating_type == 'P'){
			$blogAuthorYKScore = $resultArr["BlogAuthorYKScore"];
			$blogAuthorEmail = $resultArr["BlogAuthorEmail"];
		
			if ($blogAuthorYKScore != null){
				$updateYkScore = "update ".$sz_table_prefix."sz_email set yk_score = $blogAuthorYKScore where email_address = '$blogAuthorEmail'";
				$platformwrapper->yk_query($updateYkScore);
			}
			
			// 2. Update rater score if available
			if ($rater_ykscore){
				$score_query = sprintf("select * from ".$sz_table_prefix."sz_post where blog_id = '%d' and posting_id = '%d' limit 1",$blog_id, $post_id);
				$score_res = $platformwrapper->yk_get_row($score_query);
				
				if("anonymous" == $rater_email) {
					$anon_raw_score = $score_res->anon_raw_score;
					$anon_rating_count = $score_res->anon_rating_count;
					if(!$anon_raw_score)
						$anon_raw_score = 0;
					if(!$anon_rating_count)
						$anon_rating_count = 0;
					$new_anon_raw_score= 5*($rating-5) + $anon_raw_score;
					$new_anon_rating_count = $anon_rating_count + 1;
					
					$post_score = $platformwrapper->applyAnonymousScore($new_anon_raw_score, $score_res->post_score);
						
					$update_rater_ykscore_query = "update ".$sz_table_prefix."sz_post set anon_raw_score=$new_anon_raw_score, anon_rating_count = $new_anon_rating_count where posting_id = '$post_id' and blog_id = '$blog_id';";
				}
				else {
					$raw_score = $score_res->raw_score;
					$new_raw_score = $rater_ykscore * ($rating - 5) + $raw_score;
					$new_rating_count = $score_res->rating_count + 1;
					$post_score;
					
					if ($new_raw_score >= 1)
						$post_score = 5 + log($new_raw_score,5);
					else if($new_raw_score <= -1)
						$post_score = -1 * log((-1*$new_raw_score),5) + 5;
					else
						$post_score = 5;
	
					$update_rater_ykscore_query = "update ".$sz_table_prefix."sz_post set post_score = $post_score , raw_score=$new_raw_score, rating_count = $new_rating_count where posting_id = '$post_id' and blog_id = '$blog_id'";
				}
				$sz_rating_count = $score_res->rating_count + $score_res->anon_rating_count + 1;

				$platformwrapper->yk_query($update_rater_ykscore_query );
				$postscore_formatted = number_format($post_score, "1");
				$postrating_ws_result = "Status=Success,Rating=$postscore_formatted" ;
			}else{
				$postrating_ws_result = "Status=Failure" ;
			} // rater
		
		}else{ //if (post rating)
			$commneterYkScore= $resultArr["CommenterYKscore"];
			$commneterEmail= $resultArr["CommenterEmail"];
			if($commneterYkScore != null)
			{
				$updateYkScore ="update ".$sz_table_prefix."sz_email set yk_score='$commneterYkScore' where email_address='$commneterEmail'";
				$platformwrapper->yk_query($updateYkScore);
			}
			if($rater_ykscore != null){
				$comment = $platformwrapper->yk_get_row("SELECT * FROM ".$sz_table_prefix."sz_comment WHERE comment_id='$comment_id' and posting_id='$post_id' and blog_id = '$blog_id' LIMIT 1;");
				
				if("anonymous" == $rater_email) {
					$anon_raw_score = $comment->anon_raw_score;
					$anon_rating_count = $comment->anon_rating_count;
					if(!$anon_raw_score)
						$anon_raw_score = 0;
					if(!$anon_rating_count)
						$anon_rating_count = 0;
					$new_anon_raw_score= 5*($rating-5) + $anon_raw_score;
					$new_anon_rating_count = $anon_rating_count + 1;
					
					$commentscore = $platformwrapper->applyAnonymousScore($new_anon_raw_score, $comment->comment_score);
						
					$update_comment_query = "update ".$sz_table_prefix."sz_comment set anon_raw_score='$new_anon_raw_score' , anon_rating_count = '$new_anon_rating_count' WHERE comment_id='$comment_id' and posting_id='$post_id' and blog_id = '$blog_id';";
				}
				else {
					$raw_score = $comment->raw_score;
					$new_raw_score= $rater_ykscore*($rating-5) + $raw_score;
					$new_rating_count = $comment->rating_count + 1 ;
					$commentscore ;
					
					if($new_raw_score >=1)
						$commentscore=5+ log($new_raw_score,5);
					else if($new_raw_score <= -1)
						$commentscore=-1*log((-1*$new_raw_score),5) +5;
					else
						$commentscore = 5;
					
					$update_comment_query = "update ".$sz_table_prefix."sz_comment set comment_score='$commentscore' , raw_score='$new_raw_score' , rating_count = '$new_rating_count' WHERE comment_id='$comment_id' and posting_id='$post_id' and blog_id = '$blog_id';";
				}
				
				$sz_rating_count = $comment->rating_count + $comment->anon_rating_count + 1;
				$platformwrapper->yk_query($update_comment_query);
				$commentscore_formatted = number_format($commentscore, "1");
				$postrating_ws_result = "Status=Success,Rating=$commentscore_formatted";
				
			} else {
				$postrating_ws_result = "Status=Failure" ;
			}
		} // if rating_type
	} else if ($status == 'Blocked' | $status == 'Denied') {
		$postrating_ws_result = "Status=$status" ;
	}

	return $postrating_ws_result ;
}
?>
