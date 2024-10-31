<?php

/**
 * Comments template for SezWho threaded commments integration
 *
 * Manual comments_template installation:
 * if you are manually installing this comments template, that means if you are NOT activating the SezWho Plugin
 * or your theme does NOT support the comments_template() function for loading the comments template then copy and
 * paste the code below into your theme's functions.php file. It can go anywhere in that file.
 *
 *//* ---- start manual loading code ----
	
	// function to load the required javascript and CSS for the SezWho custom comments template
	// when the comments template is to be used without activating the sezwho plugin
	if(!function_exists('cp_comment_footer_thumb_rating')) {
		if(!function_exists('cp_add_css_head')) {
			function cp_add_css_head() {
				echo '<link rel="stylesheet" type="text/css" href="'.get_settings('siteurl').'/wp-content/plugins/sezwho/sezwho_comments/comments_styles.css" media="screen" />'.
					 '<script type="text/javascript" src="'.get_settings('siteurl').'/wp-content/plugins/sezwho/sezwho_comments/replyform.js"></script>';
			}
			add_action('wp_head', 'cp_add_css_head');
		}
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquerycolor','/wp-includes/js/jquery/jquery.color.js',array('jquery'),'1.0');
	}

 *//* ---- end manual loading code ---- */

if ('wp-comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('Please do not load this page directly. Thanks!');

include(ABSPATH . WPINC . "/version.php");

$is_new = (isset($wp_version) && $wp_version > '1.2');

if (($is_new) or ($withcomments) or ($single)) {

do_action('comment_form', $post->ID);

        if (!empty($post->post_password)) { // if there's a password
            if ($_COOKIE['wp-postpass_'.$cookiehash] != $post->post_password) {  // and it doesn't match the cookie
?>
<p><?php _e("Enter your password to view comments."); ?><p>
<?php
				return;
            }
        }

 		$comment_author = (isset($_COOKIE['comment_author_'.$cookiehash])) ? trim($_COOKIE['comment_author_'.$cookiehash]) : '';
        $comment_author_email = (isset($_COOKIE['comment_author_email_'.$cookiehash])) ? trim($_COOKIE['comment_author_email_'.$cookiehash]) : '';
 		$comment_author_url = (isset($_COOKIE['comment_author_url_'.$cookiehash])) ? trim($_COOKIE['comment_author_url_'.$cookiehash]) : '';

		if(!$tablecomments && $wpdb->comments) // this makes it work in both 1.2 and 1.3
			$tablecomments = $wpdb->comments;
			$comments = $wpdb->get_results("SELECT * FROM $tablecomments WHERE comment_post_ID = '$id' AND comment_approved = '1' ORDER BY comment_date");

	// add JS & CSS to page if we're running without a plugin
	global $sz_options;
	if(!function_exists('cp_comment_footer_thumb_rating')) { $sz_comments_standalone = true; }
	else { $sz_comments_standalone = false; }
?>
<!-- edit below this line only -->
<?php
$GLOBALS['threaded_comments'] = array();
$GLOBALS['sz_comments_standalone'] = $sz_comments_standalone;
function write_comment(&$c, $deepest_id = -1,$depth=1, $is_comment=true) {
	global $post, $sz_comments_standalone, $sz_options;
	// find out if we're the post author
	if($post->post_author == $c->user_id) { $author = true; }
	else { $author = false; }
?>
			<li id="comment-<?php echo $c->comment_ID ?>" class="sz_comment sz_l<?php echo $depth; if($author) { echo ' comment_author'; }?>" rel="child_of_<?php echo $c->comment_parent; ?>">
				<div class="sz_slider">&nbsp;</div>
				<div class="sz_head<?php if($author) { echo ' author'; } ?>">
					<div class="sz_head_wrapper">
						<div id = "sz_comment_collapse_divC:<?php echo $c->comment_ID ?>" style = "display:none; visibility: hidden; float:left;"><button class = "cpEmbedCommentImgPlus" onclick = "javascript:SezWho.Utils.CommentFilterProcessing.manageComment(<?php echo $c->comment_ID ?> ,1)"></button>
						</div>
						<?php 
							if ($is_comment && function_exists('cp_comment_user_image') && isset($sz_options) && $sz_options->sz_enable_comment_rating == 1) { echo cp_comment_user_image(); } 
							elseif($is_comment) { echo preg_replace("/class='(.*)'/",'class="cpEmbedImageUserh"',get_avatar($email,48)); }
						?>						<h5 title="<?php echo get_comment_author(); ?>"><?php comment_author_link(); ?>
							<span class="sz_head_aux"><?php if ($is_comment && function_exists('cp_comment_profile_link')) { echo cp_comment_profile_link(); } ?>
							<?php edit_comment_link(__('Edit This'), ' | '); ?></span></h5> 
							<p class="sz_post_date">
							<?php 
								if(function_exists('timeDiff')) { echo __(timeDiff($c->comment_date_gmt)); } 
								else { echo $c->comment_date.' '.$c->comment_time; } 
							?>
							</p>
					</div>
				</div>
				<div class="body" id = "sz_comment_body:<?php echo $c->comment_ID ?>">
					<div class='content'>
					<?php comment_text() ?><?php 
						if(preg_match('|<Pingback />|', $c->comment_content)) {
							echo '<small>'.__('Read the rest at').' '.comment_author_link().'</small>';
						}
					?>
					</div>
					<div class="reply sz_footer">
<?php 
	if($is_comment) {
		if( $post->comment_status == 'open' ) {
			global $user_ID; 
			if ( get_option("comment_registration") && !$user_ID ) { 
				echo '<a href="'.get_option('siteurl').'/wp-login.php?redirect_to='.get_permalink().'">'.__('Log in to Reply').'</a>'; 
				$reply_comment_id = false;
			}
			else { $reply_comment_id = $c->comment_ID; }
		} 

		if (function_exists('cp_comment_footer_content')) { 
			//echo cp_comment_footer_thumb_rating($reply_comment_id); 
			echo cp_comment_footer_content($reply_comment_id);		
		}
		elseif($sz_comments_standalone) { 
			echo '<p><a href="#addcommentanchor" class="sz_reply" onclick="sz_add_comment(\'comment-'.$c->comment_ID.'\', '.
			     $c->comment_ID.', true); return false;">'.__('Reply').'!</a></p>'; 
		}
		else { echo '<p><a href="#addcommentanchor" class="sz_reply">'.__('Reply').'</a></p>'; }
	}
		echo '		<div style="clear:both;"></div>	
				</div><!-- / comment footer -->
			</div><!-- / comment body -->';
		
		// if we have nested comments then recurse
		if($GLOBALS['threaded_comments'][$c->comment_ID]) {
			foreach($GLOBALS['threaded_comments'][$c->comment_ID] as $c) {
				write_comment($c, $c->comment_ID,$depth+1);
			}
		}
echo '</li><!-- / comment -->';
	}// end write_comment function
?>
<div id="commentwrap">
	
<h2 id="comments"><?php comments_number(__("Comments"), __("1 Comment"), __("% Comments")); __('so far'); ?></h2>
<p><?php
	if ($post->comment_status == 'open') {
		echo '<span class="post_a_comment"><a href="#addcommentanchor">'.__('Post a comment').'</a></span> &nbsp; | &nbsp; ';
	}
	if ('open' == $post->ping_status) {
		echo '<span><a href="'.get_trackback_url().'">'.__('Trackback').' <acronym title="'.__('Uniform Resource Identifier').'">'.__('URI').'</acronym></a></span> &nbsp; | &nbsp; ';
 	}
?><span class="comments_feed_icon"><?php echo comments_rss_link(__('Comments').' <abbr title="'.__('Really Simple Syndication').'">'.__('RSS').'</abbr> '.__('feed')); ?></span></p>
<div class="sz_commentlist" id="sz_comments">
<?php if($sz_options->sz_enable_comment_filter == 1) { ?>
	<div id="cpCommentFilter">
		<div class="cpCommentFilterCorner cpCommentFilterTL"></div>
		<div class="cpCommentFilterCorner cpCommentFilterTR"></div>
		<h4>Filter Comments</h4>
<?php if (count($comments) > 10){ ?>
		<div id="cpCommentFilterLists" style="display: block;">		
<?php } else { ?>
		<div id="cpCommentFilterLists" style="display: none;">
<?php } ?>
			<p>Filter by: 
				<select name="cpCommentFilterBy" id="cpCommentFilterBy">
					<option value="none">None</option>
					<option value="rating">Comment Rating</option>
					<option value="date">Comment Date</option>
					<option value="username">User Name</option>
				</select>
				is
				<select name="cpCommentFilterByValue" id="cpCommentFilterByValue"></select>
				&nbsp;
				<button name="cpCommentFilterApply" id="cpCommentFilterApply">Apply</button>
			</p>
		</div>
		<div class="cpCommentFilterCorner cpCommentFilterBL"></div>
		<div class="cpCommentFilterCorner cpCommentFilterBR"></div>
	</div>
<?php } ?>
	<script type="text/javascript">
		var cpCommentRatings;
		var cpCommentDates;
		var cpUserNames = '';
		jQuery(window).ready(function() {
				// walk thorugh and harvest usernames
				var cpUserNameList = new Array();
				var bUserNameList = new Array();
				for(var i=0, sz_counter=0; i<sz_comment_config_params.sz_comment_data.length; i++) {
					if( !bUserNameList[sz_comment_config_params.sz_comment_data[i].comment_author] ) {
						cpUserNameList[sz_counter] = decodeURIComponent(sz_comment_config_params.sz_comment_data[i].comment_author);
						bUserNameList[sz_comment_config_params.sz_comment_data[i].comment_author] = true;
						sz_counter++;
					}
				}
				cpUserNameList.sort();
				for(i=0; i<cpUserNameList.length; i++) {
					cpUserNames += '<option value="'+cpUserNameList[i]+'">'+cpUserNameList[i]+'</option>';
				}
				// set default rating filters
				cpCommentRatings = '<option value="0">All Comments</option>'+
								   '<option value="6.0">Good Comments (3+)</option>'+
								   '<option value="8.0">Great Comments (4+)</option>';
				// set default date filters
				cpCommentDates = '<option value="0">Today</option>'+
								 '<option value="1">Yesterday</option>'+
								 '<option value="2">Less than 1 week ago</option>'+
								 '<option value="3">Less than 1 month ago</option>'+
								 '<option value="4">Less than 2 months ago</option>'+
								 '<option value="5">Less than 6 months ago</option>';
				jQuery('#cpCommentFilterBy').change(cpChangeFilterOptions).trigger('change');
				jQuery('#cpCommentFilter h4').click(cpToggleCommentFilter);
				jQuery('#cpCommentFilterApply').click(cpApplyCommentFilter);
			});
			
		var cpChangingFilterTypes = false;
		function cpChangeFilterOptions(){
			var val = jQuery(this).val();
			var tgt = jQuery('#cpCommentFilterByValue');
			cpChangingFilterTypes = true;
			switch(val) {
				case 'none':
					tgt.html(' ');
					tgt.attr('disabled',true);
					break;
				case 'rating':
					tgt.html(cpCommentRatings);
					tgt.attr('disabled',false);
					break;
				case 'date':
					tgt.html(cpCommentDates);
					tgt.attr('disabled',false);
					break;
				case 'username':
					tgt.html(cpUserNames);
					tgt.attr('disabled',false);
					break;
			}
			cpChangingFilterTypes = false;
		}
		
		function cpApplyCommentFilter() {
			if('Comment Rating' == jQuery('#cpCommentFilterBy option:selected').text()){
				javascript:SezWho.Utils.CommentFilterProcessing.filterCommentsByScore(document.getElementById('cpCommentFilterByValue').value);
			}
			else if('Comment Date' == jQuery('#cpCommentFilterBy option:selected').text()){
				javascript:SezWho.Utils.CommentFilterProcessing.filterCommentsByDate(document.getElementById('cpCommentFilterByValue').value);
			}
			else if('User Name' == jQuery('#cpCommentFilterBy option:selected').text()) {				
				javascript:SezWho.Utils.CommentFilterProcessing.filterCommentsByCommentAuthor(encodeURIComponent(document.getElementById('cpCommentFilterByValue').value));
			}
			else if('None' == jQuery('#cpCommentFilterBy option:selected').text()) {
				javascript:SezWho.Utils.CommentFilterProcessing.filterCommentsByNone(document.getElementById('cpCommentFilterByValue').value);
			}

		}
		
		var cpFilterToggle;
		function cpToggleCommentFilter() {
			if(!cpFilterToggle) { cpFilterToggle = jQuery('#cpCommentFilter h4'); }
			cpFilterToggle.next('div').slideToggle('normal',function(){
					if(jQuery(this).css('display') == 'block') { cpFilterToggle.addClass('open'); }
					else { cpFilterToggle.removeClass('open'); }
				});
		}
	</script>
<?php 
	if($comments) {
		foreach($comments as $comment) {
			$GLOBALS['threaded_comments'][$comment->comment_parent][] = $comment;
		}
		$comment = $comments[0];
		if( is_array($GLOBALS['threaded_comments'][0]) ) {
			echo "<ul class='sz_comment_list'>";
			foreach($GLOBALS['threaded_comments'][0] as $comment) {
				if ( get_comment_type() == "comment" ) {
					$GLOBALS['comment'] = &$comment;
					write_comment($GLOBALS['comment']);
				}
			}
			echo "</ul>";
		}
	}
	else { echo '<p>'._e('No comments yet.').'</p>'; }
?>
</div>
<?php if ('open' == $post->comment_status) : ?>
<?php
 // this line is WordPress' motor, do not delete it.
$comment_author = (isset($_COOKIE['comment_author_' . COOKIEHASH])) ? trim($_COOKIE['comment_author_'. COOKIEHASH]) : '';
$comment_author_email = (isset($_COOKIE['comment_author_email_'. COOKIEHASH])) ? trim($_COOKIE['comment_author_email_'. COOKIEHASH]) : '';
$comment_author_url = (isset($_COOKIE['comment_author_url_'. COOKIEHASH])) ? trim($_COOKIE['comment_author_url_'. COOKIEHASH]) : '';
?>

<h2 class="trackbacks"><?php echo __('Trackbacks').'/'.__('Pings'); ?></h2>
<div class="sz_trackbacks_pings">
<?php 
		$sz_no_trackbacks=0;
		echo "<ul>";
		foreach ($comments as $comment) {
			if (get_comment_type() !== 'comment') {
				$sz_no_trackbacks++;
				$sz_edit_comment_link = get_edit_comment_link();
				if($sz_edit_comment_link) {
					$sz_edit_comment_link_markup = '&nbsp;&nbsp;<a href="'.$sz_edit_comment_link.'" title="Edit comment">Edit This</a>';
				}
				echo '<li class="trackback_item">
						<div class="trackback_link">'.get_comment_author_link().'</div> 
						'.get_comment_text().'
						<div class="trackback_time" style = "font-size:10px;"><a href="" title="">'.get_comment_date('F j, Y').' at '.get_comment_time().'</a>'.$sz_edit_comment_link_markup.'</div>
						</li><br>';
			}
		}		
		if($sz_no_trackbacks == 0)
			echo '<li><p>'.__('No trackbacks or pings yet').'</p></li>';
		echo '</ul>';
?>
</div>

<h2 id="addcommentanchor"><?php _e('Leave a Comment'); ?></h2>

<?php
if ( get_option("comment_registration") && !$user_ID ) { 
	echo 'You must be <a href="'.get_option('siteurl').'/wp-login.php?redirect_to='.get_permalink().'">'.__('logged in').'</a> to post a comment.'; 
}
else {
?>
			
<div id="sz_comment_form" class="sz_comment_form">	
<form action="<?php echo get_settings('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
<?php do_action('sz_comment_text', $post->ID); ?>
<?php if ( $user_ID ) : ?>
<p><?php _e('Logged in as'); ?> <a href="<?php echo get_option("siteurl"); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option("siteurl"); ?>/wp-login.php?action=logout" title="Log out of this account">Logout &raquo;</a></p>
<?php else : ?>
	<label for="author">
		<input type="text" name="author" id="author" class="textarea" value="<?php echo $comment_author; ?>" size="28" tabindex="1" />
		<span><?php _e('Name'); ?></span> <?php if ($req) { _e('<span class="required">(required)</span>'); } ?>
	</label>

	<label for="email">
		<input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="28" tabindex="2" />
		<span><?php _e('E-mail'); ?></span> <?php if ($req) _e('<em>(will not be published)</em>'); ?>
		<?php if($req) { _e('<span class="required">(required)</span>'); } ?>
	</label>

	<label for="url">
		<input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="28" tabindex="3" />
		<span><?php _e('Website <acronym title="Web Address of your Website">URI</acronym>'); ?></span>
	</label>
<?php endif; ?>
	<label for="comment">
		<span><?php _e('Your Comment'); ?></span> <span class="required">(<?php _e('required'); ?>)</span>
		<textarea name="comment" id="comment" cols="30" rows="14" tabindex="4" style="width: 98%;"></textarea>
	</label>
	<?php /* <p><small><?php _e('You may use'); ?> <?php echo allowed_tags();?> <?php _e('in your comment'); ?>.</small></p> */ ?>
	<input type="hidden" name="comment_post_ID" value="<?php echo $post->ID; ?>" />
	<input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" />
	<input type="hidden" name="sz_comment_parent" id="sz_comment_parent" value="0" />

	<div>
		<input name="addcommentbutton" class="addcommentbutton" type="submit" id="submit" tabindex="5" value="<?php _e('Submit Comment'); ?>" onclick="sz_submit_comment(this,'sz_comment_form')" />
	</div>
<?php do_action('comment_form', $post->ID); ?>
</form>
</div>

<?php
}
?>
<?php else : // Comments are closed ?>
<p><?php _e('Sorry, Comments are closed at this time.'); ?></p>
<?php endif; ?>
<?php } ?>
<p id="sz_comment_credit"><span><?php _e('Comment template by'); ?></span> <a href="http://www.sezwho.com">SezWho</a></p>
</div>

<div id="sz_comment_form_inline" class="sz_comment_form" style="display: none;"><div class="sz_inline_spacer">	
	<form action="<?php echo get_settings('siteurl'); ?>/wp-comments-post.php" method="post" name="commentforminline" id="commentforminline">
		<a href="javascript:void(0)" class="sz_cancel sz_cancel_icon" onclick="sz_cancel_add(); return false;"><?php _e('cancel'); ?></a>
	<?php if ( $user_ID ) : ?>
		<p><?php _e('Logged in as'); ?> <a href="<?php echo get_option("siteurl"); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option("siteurl"); ?>/wp-login.php?action=logout" title="Log out of this account"><?php _e('Logout'); ?> &raquo;</a></p>
	<?php else : ?>
		<label for="author_inline">
			<input type="text" name="author" id="author_inline" class="textarea" value="<?php echo $comment_author; ?>" size="28" tabindex="10" />
			<span><?php _e('Name'); ?></span> <?php if ($req) { _e('<span class="required">(required)</span>'); } ?>
		</label>

		<label for="email_inline">
			<input type="text" name="email" id="email_inline" value="<?php echo $comment_author_email; ?>" size="28" tabindex="11" />
			<span><?php _e('E-mail'); ?></span> <?php if ($req) { _e('<em>(not published)</em>'); } ?>
		</label>

		<label for="url_inline">
			<input type="text" name="url" id="url_inline" value="<?php echo $comment_author_url; ?>" size="28" tabindex="12" />
			<span><?php _e('Website <acronym title="Web Address of your Website">URI</acronym>'); ?></span>
		</label>
	<?php endif; ?>
		<label for="comment_inline" id="comment_inline_label">
			<span><?php _e('Your Comment'); ?></span> <span class="required">(<?php _e('required'); ?>)</span>
			<textarea name="comment" id="comment_inline" cols="70" rows="14" tabindex="14"></textarea>
		</label>
		<input type="hidden" name="comment_post_ID" value="<?php echo $post->ID; ?>" />
		<input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" />
		<input type="hidden" name="sz_comment_parent" id="sz_comment_parent_inline" value="0" />
		<div>
			<input name="addcommentbutton" class="addcommentbutton" type="submit" id="submit_inline" tabindex="15" value="<?php _e('Submit Comment'); ?>" onclick="sz_submit_comment(this,'sz_comment_form_inline')" /> &nbsp;
			<a href="javascript:void(0)" class="sz_cancel" onclick="sz_cancel_add(); return false;"><?php _e('cancel'); ?></a>
		</div>
	<?php do_action('comment_form', $post->ID); ?>
	</form>
</div></div>
