var show_up_level = 3 ;var header_up_level = 2 ;var is_debug_log_enabled = 0; var sz_is_auto_layout = 1; var sz_high_sibling = 0; var sz_low_sibling = 0; var sz_custom_ratings_text = {1:"it\'s bad!", 2:"it\'s so so!", 3:"it\'s good!", 4:"it\'s great!", 5:"it rocks!"}; 

var sz_ratings_display = {
		number_values:2,
		li_style_in_process_display:"",
		ul_style_in_process_display:"",
		hover_handle_type:"value", //other values - width
		ratings_text:{1:"it's bad!", 2:"it rocks!"},
		ul_hover_ratings_style:1,
		li_hover_ratings_style:0,
		ratings_value:{1:0,2:10} };
SezWho = {
	Version: '2.2'
} ;

SezWho.Utils = {} ;

// class for rendering the sezwho UI for each comment
SezWho.PluginCore = new function () {
	this.szManageEmbedUI = function () {
		SezWho.Utils.log("info", "called SezWho.PluginCore.szManageEmbedUI ");
		if (window.sz_global_config_params == null || document.getElementById('cpEmbedPageTableid') || window.sz_comment_config_params == null || window.sz_comment_config_params.comment_number == null || ((window.sz_comment_config_params.sz_enable_comment_rating != null) && (0 === sz_comment_config_params.sz_enable_comment_rating)) ) {
			return;
		}
		var post_id = sz_comment_config_params.post_id ;
		var sortScore = sz_comment_config_params.sortOrder ;
		var isErrorShown = "false" ;
		if (sortScore == '') sortScore = 0 ;
		if (sz_comment_config_params.comment_number != sz_comment_config_params.sz_comment_data.length) {
			sz_comment_config_params.comment_number = sz_comment_config_params.sz_comment_data.length;
		}
		if (!sz_comment_config_params.sz_enable_comment_filter && SezWho.BlogPlatformCallbackJS.get_platform() != "blogger") return;

		if (sz_comment_config_params.sz_auto_option_bar && sz_comment_config_params.sz_enable_comment_filter) {
			var fb = get_show_comment_dropdown_div();
			var fbph = document.getElementById('szOptionBarPlaceHolder');
			fbph.parentNode.replaceChild(fb, fbph);
		}
		else if (sz_comment_config_params.sz_enable_comment_filter){
			var cn = SezWho.BlogPlatformCallbackJS.get_top_of_first_comment(sz_comment_config_params.sz_comment_data[0].comment_id);
			var showCommentDiv = get_show_comment_dropdown_div();
			try {
				if (cn && cn.parentNode) {
					cn.parentNode.insertBefore(showCommentDiv, cn);
				}
			} catch (e) {
				if (isErrorShown == "false" ) {
					SezWho.Utils.log("error", "We could not handle this theme, please use the manual modification of theme as outlined in "+sz_global_config_params.cpserverurl+"/FAQ.php.");
					isErrorShown = "true" ;
					return ;
				}
			}
		}
		// loop over the comment score information passed and populate the comment header and footer
		var hn, fn, ccd;
		for (var co2 = 0 ; co2 < sz_comment_config_params.comment_number; co2++) {
			if (typeof(sz_comment_config_params.sz_comment_data[co2]) == "undefined" || sz_comment_config_params.sz_comment_data[co2].comment_score == 0)
				continue;
			hn = null;
			fn = null;
			ccd = null;

			if (SezWho.BlogPlatformCallbackJS.get_platform() == "blogger"){//insert the header etc.
				var at = null;
				var url = unescape(sz_comment_config_params.sz_comment_data[co2].comment_author_url);
				if (url != "") at= SezWho.BlogPlatformCallbackJS.get_comment_author_name(sz_comment_config_params.sz_comment_data[co2].comment_id, url);
				if (!at) continue;
				if (sz_comment_config_params.sz_comment_data[co2].comment_author == "") 
					sz_comment_config_params.sz_comment_data[co2].comment_author = escape(at.innerHTML);
				var im = cp_comment_user_image(co2, sz_comment_config_params.sz_comment_data[co2].encoded_email, at.innerHTML, 0);
				var ln = cp_comment_profile_link(co2, 0);
				if (ln && im && at.parentNode){
					at.parentNode.insertBefore(im, at);
					at.parentNode.insertBefore(ln, at.nextSibling);
				}
				// now the ratings bar
				var bb = SezWho.BlogPlatformCallbackJS.get_comment_bottom_block(sz_comment_config_params.sz_comment_data[co2].comment_id);
				if (!bb) continue;
				var rb = cp_comment_footer_content(co2, sz_comment_config_params.sz_comment_data[co2].comment_score, sz_comment_config_params.sz_comment_data[co2].rating_count);
				if (rb) bb.insertBefore(rb, null);
			}


			if (sz_comment_config_params.sz_enable_comment_filter &&
			    SezWho.BlogPlatformCallbackJS.get_platform() != "blogger"){// filtering not supported on blogger
				var comment_node = SezWho.BlogPlatformCallbackJS.get_comment_node(sz_comment_config_params.sz_comment_data[co2].comment_id);
				if (!comment_node)
					comment_node = SezWho.BlogPlatformCallbackJS.get_comment_node_by_calc(sz_comment_config_params.sz_comment_data[co2].comment_id,1);
				if (!comment_node) continue;
				try {
					hn = get_comment_header_div(co2);
					// Next create the composite Div and insert it
					ccd = get_composite_div(co2);
					while (comment_node.firstChild) {
						ccd.appendChild(comment_node.firstChild);
					}
					comment_node.appendChild(ccd);
					// Last insert the comment header node.
					comment_node.insertBefore(hn,ccd);
				} catch (e) {
					if (isErrorShown == "false") {
						SezWho.Utils.log("error", "We could not handle this theme, please use the manual modification of theme as outlined in "+sz_global_config_params.cpserverurl+"/FAQ.php .");
						isErrorShown = "true" ;
						return ;
					}
				}
			}
		}
	};


	this.szManagePostUI = function(){
		if (window.sz_global_config_params == null ||
		   'undefined' == typeof sz_post_config_params ||
		   window.sz_post_config_params == null)
			return;
	
		if (SezWho.BlogPlatformCallbackJS.get_platform() == "blogger") {
			for (id in sz_post_config_params) {
				var pn = SezWho.BlogPlatformCallbackJS.get_post_node(id);
				if (sz_post_config_params[id].blog_author_name == "") sz_post_config_params[id].blog_author_name = "Author";
				var fb = cp_post_footer_content(
					sz_post_config_params[id].post_id, 
					sz_post_config_params[id].post_score, 
					sz_post_config_params[id].rating_count, 
					sz_post_config_params[id].blog_author_name, 
					sz_post_config_params[id].md5email);
				if (!fb || !pn) continue;
				pn.insertBefore(fb, null);
			}
		}
		else {
			for (id in sz_post_config_params) {
			       var pn;
			       var sn;
			       var sid = document.getElementById('sz_image_link_post:'+id);
			       if (sid && sid.parentNode.tagName && sid.parentNode.tagName == 'A'){
				       pn = sid.parentNode.parentNode;
				       sn = sid.parentNode;
				       pn.insertBefore(sid, sn);
			       }
			       var sid = document.getElementById('sz_author_span_post:'+id);
			       if (sid && sid.parentNode.tagName && sid.parentNode.tagName == 'A'){
				       pn = sid.parentNode.parentNode;
				       sn = sid.parentNode;
				       sn.removeChild(sid);
				       pn.insertBefore(sid, sn.nextSibling);
			       }
			}
		}
	};

	function get_show_comment_dropdown_div() {
		if ((window.sz_comment_config_params.sz_enable_comment_filter != null) && (1 === sz_comment_config_params.sz_enable_comment_filter)){
			iHtml = "<table class='cpEmbedPageTable' id='cpEmbedPageTableid'><tr><td  class='cpEmbedPageTableCell'><p class='cpEmbedSelectParagraph'>Filter: <SELECT onchange=\"javascript:SezWho.Utils.CommentFilterProcessing.cpshowFilterOptions(this)\"><OPTION value=\"0\">All Comments</OPTION><OPTION value=\"5.5\">Good Comment(3+)</OPTION> <OPTION value=\"7.5\">Great Comments(4+)</OPTION></SELECT> <a href=\"javascript:void SezWho.Utils.openPage('help');\">What is this?</a> </p></td><td class='cpEmbedPageTableCellBranding cpEmbedPageFilterBranindingCellCustom'>";
		}
		else {
			iHtml = "<table class='cpEmbedPageTable cpEmbedPagewithoutFilterBarCustom' id='cpEmbedPageTableid'><tr><td class='cpEmbedPageTableCellBranding cpEmbedPageFilterBranindingCellCustom'>";
		}
		if (navigator.userAgent.match(/msie (5\.5|6)/i)&&navigator.platform=="Win32") { //PNG fix
			iHtml = iHtml+"<a href=\"javascript:void SezWho.Utils.openPage('home');\"  class='cpEmbedPageTableCellBrandingLinkIE'/>";
		} else {
			iHtml = iHtml+"<a href=\"javascript:void SezWho.Utils.openPage('home');\" class='cpEmbedPageTableCellBrandingLink'/>";
		}
		iHtml = iHtml+"</td></tr></table>";
		var div = document.createElement("div");
		div.innerHTML = iHtml;
		return div;
	};

	function get_commenter_display(i) {
		var comment_author_anchor ;
		var comment_author = unescape(sz_comment_config_params.sz_comment_data[i].comment_author);
		var comment_author_url = sz_comment_config_params.sz_comment_data[i].comment_author_url;
		if (comment_author_url && comment_author_url.length > 0 && comment_author_url != "http://") {
			comment_author_anchor = "<a class='cpEmbedPageCommentHeaderAuthorLinkCustom' href='javascript:SezWho.Utils.szClick(\"UW\", \""+comment_author_url+"\", \"0\", \"0\")' rel='nofollow'>"+comment_author+"</a>" ;
		} else {
			comment_author_anchor = comment_author ;
		}
		return comment_author_anchor;
	};

	function cp_comment_profile_link(comment_iteration_num, h) {
		var ih = " (<a class='cpEmbedPageProfileLinkCustom' id='sz_profile_link:"+comment_iteration_num+"' onmouseover='javascript:SezWho.Utils.cpProfileEventHandler(event);' onmousedown='javascript:SezWho.Utils.cpProfileEventHandler(event);' href='javascript:void(0);' onmouseout='javascript:SezWho.DivUtils.cancelPopUp();'>Check me out!</a>)";
		if (h) return ih;
		var ls = document.createElement("span") ;
		ls.innerHTML = ih;
		return ls;
	};

	function cp_comment_footer_content(comment_iteration_num, cs, rc) {
		var rb = document.createElement("div") ;
		var to_return;
		var width = Math.round(cs*10) ;
		to_return  =  "<table class='cpEmbedPageTable' style='width:auto'>";
		to_return +=  "<tr>";
		to_return +=  "<td class='cpEmbedPageTableCell'>";
		to_return += "<span class='cpEmbedPageCommFooterCS'><a href='javascript:void(0);' onmousedown='SezWho.DivUtils.activateRatingsHelpDIV(event);'>Rate this</a>: </span>";
		to_return +=  "</td><td class='cpEmbedPageTableCell'>";
		to_return += '<ul id="cpEmbedCommScore:'+comment_iteration_num+'" class="cpEmbedCommScoreUl" onmousedown="SezWho.Utils.ScoreDisplay.update(event)" onmousemove="SezWho.Utils.ScoreDisplay.cur(event)" title="Rate This!"><li id="cpEmbedCommScoreLi:'+comment_iteration_num+'" class="cpEmbedCommScoreLi" title="'+cs/2+'" style="width:'+width+'%"></li></ul>';
		to_return +=  "</td><td class='cpEmbedPageTableCell'>";
		to_return += '<span id="cpEmbedCommScoreSpan:'+comment_iteration_num+'" class="cpEmbedCommScoreSpan">'+cs/2+'</span>';
		if (rc!= "" && parseInt(rc))
			if (rc == '1')
			to_return += '<span id="cpEmbedCommCountSpan:'+comment_iteration_num+'" class="cpEmbedCommCountSpan" title="'+rc+'"> ('+rc+' person)</span>';
			else
			to_return += '<span id="cpEmbedCommCountSpan:'+comment_iteration_num+'" class="cpEmbedCommCountSpan" title="'+rc+'"> ('+rc+' people)</span>';
		else
			to_return += '<span id="cpEmbedCommCountSpan:'+comment_iteration_num+'" class="cpEmbedCommCountSpan" title="0"></span>';
		to_return +=  "</td>";
		to_return += '</tr>';
		to_return += '</table>';
		rb.innerHTML = to_return;
		return rb;
	};
	function cp_post_footer_content(pid, ps, rc, aname, md5email) {
		var rb = document.createElement("div") ;
		var to_return;
		var width = Math.round(ps*10) ;
		to_return  =  "<table class='cpEmbedPageTable' style='width:auto'>";
		to_return +=  "<tr>";
		to_return +=  "<td class='cpEmbedPageTableCell'>";
		to_return += "<span class='cpEmbedPagePostFooterCS'><a href='javascript:void(0);' onmousedown='SezWho.DivUtils.activateRatingsHelpDIV(event);'>Rate this</a>: </span>";
		to_return +=  "</td><td class='cpEmbedPageTableCell'>";
		to_return += '<ul id="cpEmbedPostScore:'+pid+'" class="cpEmbedPostScoreUl" onmousedown="SezWho.Utils.ScoreDisplay.update(event)" onmousemove="SezWho.Utils.ScoreDisplay.cur(event)" title="Rate This!"><li id="cpEmbedPostScoreLi:'+pid+'" class="cpEmbedPostScoreLi" title="'+ps/2+'" style="width:'+width+'%"></li></ul>';
		to_return +=  "</td><td class='cpEmbedPageTableCell'>";
		to_return += '<span id="cpEmbedPostScoreSpan:'+pid+'" class="cpEmbedPostScoreSpan">'+ps/2+'</span>';
		if (rc!= "" && parseInt(rc))
			if (rc == '1')
			to_return += '<span id="cpEmbedPostCountSpan:'+pid+'" class="cpEmbedPostCountSpan" title="'+rc+'"> ('+rc+' person)</span>';
			else
			to_return += '<span id="cpEmbedPostCountSpan:'+pid+'" class="cpEmbedPostCountSpan" title="'+rc+'"> ('+rc+' people)</span>';
		else
			to_return += '<span id="cpEmbedPostCountSpan:'+pid+'" class="cpEmbedPostCountSpan" title="0"></span>';
		to_return +=  "</td>";
		to_return +=  "<td class='cpEmbedPageTableCell'>";
		to_return +=  "<span class='cpEmbedPagePostFooterCS cpEmbedPostAuthorIdentifier'>";
		to_return +=  "<img src='http://s3.amazonaws.com/sz_users_images/noimg.gif' onmouseout='javascript:SezWho.DivUtils.cancelPopUp();' onmousedown='javascript:void SezWho.Utils.cpProfileImgClickHandler(\""+pid+"\", \"P\");' onmouseover='javascript:SezWho.Utils.cpProfilePostEventHandler(event)' class='cpEmbedImageAuthor' onerror=\"this.src='http://s3.amazonaws.com/sz_users_images/noimg.gif'\" id='sz_image_link_post:"+pid+"' alt='User Image' title='No image'/>";
		to_return +=  "<script type='text/javascript' src='http://image.sezwho.com/getpic.php?md5id="+md5email+"&return_js=true&aname="+escape(aname)+"&element_id=sz_image_link_post:"+pid+"'></script> ";
		to_return +=  "<a onmouseout='javascript:SezWho.DivUtils.cancelPopUp();' onmousedown='javascript:void SezWho.Utils.cpProfileImgClickHandler(\""+pid+"\", \"P\")' href='javascript:void(0);' onmouseover='javascript:SezWho.Utils.cpProfilePostEventHandler(event)' class='cpEmbedPageProfileLink' id='sz_author_link_post:"+pid+"' >"+aname+" (Check me out!)</a></span>";
		to_return +=  "</td>";
		to_return += '</tr>';
		to_return += '</table>';
		rb.innerHTML = to_return;
		return rb;
	};


	function cp_comment_user_image(comment_iteration_num, md5_email, aname, h){
		var ih= "<img src='http://s3.amazonaws.com/sz_users_images/noimg.gif' alt='no image' title='no image' class='cpEmbedImageUserh' onmouseover='javascript:SezWho.Utils.cpProfileEventHandler(event);' onmousedown='javascript:SezWho.Utils.cpProfileEventHandler(event);' onmouseout='javascript:SezWho.DivUtils.cancelPopUp();' onerror=\"this.src='http://s3.amazonaws.com/sz_users_images/noimg.gif'\" id='sz_image_link:"+comment_iteration_num+"'/><script type='text/javascript' src='http://image.sezwho.com/getpic.php?md5id="+md5_email+"&return_js=true&element_id=sz_image_link:"+comment_iteration_num+"&aname="+encodeURI(aname)+"'></script>";
		if (h) return ih;
		var img_span = document.createElement("span") ;
		img_span.innerHTML = ih;
		return img_span;
	};

	function get_comment_header_div(i) {
		var iHtml = "<table class='cpEmbedPageTable' id=\"sz_comment_header:"+i+"\" style='display:none;visibility:hidden'><tr><td class='cpEmbedPageTablePMCell'><button id=\"sz_comment_collapse:"+i+"\" onclick=\"javascript:SezWho.Utils.CommentFilterProcessing.manageComment('"+i+"',2)\" class='cpEmbedCommentImgMinus'></button></td>";
		iHtml += "<td class='cpEmbedPageTableIMGCell'>"+cp_comment_user_image(i, sz_comment_config_params.sz_comment_data[i].encoded_email, unescape(sz_comment_config_params.sz_comment_data[i].comment_author), 1)+"</td>" ;
		iHtml += "<td class='cpEmbedPageTableCell'>"+cp_comment_profile_link(i, 1)+"</td>" ;
		iHtml += "</tr></table>" ;
		var div = document.createElement("div");
		div.innerHTML = iHtml;
		return div;
	};

	function get_composite_div(i) {
		SezWho.Utils.log("info" , "get_composite_div called with i = "+i);
		var commentCompositeDiv = document.createElement("div") ;
		commentCompositeDiv.id = "sz_comment_collapse_div:"+i ;
		commentCompositeDiv.className = "cpEmbedPageCommentBodyCollapseDivCustom";
		if (sz_comment_config_params.sz_show_commenter_pic==1)
		commentCompositeDiv.innerHTML = "<div class='cpEmbedImageLinkContainer'><a class='cpEmbedImageUserLink' href='javascript:void(0);' onclick='javascript:SezWho.Utils.szClick(\"IC\",\""+sz_comment_config_params.sz_user_link_repo+(sz_comment_config_params.sz_user_link_repo_use_enc==1?sz_comment_config_params.sz_comment_data[i].encoded_email:sz_comment_config_params.sz_comment_data[i].comment_author_email)+"\",\"0\",\"0\");' title='"+sz_comment_config_params.sz_commenter_pic_title+unescape(sz_comment_config_params.sz_comment_data[i].comment_author)+"'><img src='"+sz_comment_config_params.sz_user_image_repo+sz_comment_config_params.sz_comment_data[i].encoded_email+sz_comment_config_params.sz_user_image_repo_post+"' class='cpEmbedImageUser' alt='"+sz_comment_config_params.sz_commenter_pic_title+unescape(sz_comment_config_params.sz_comment_data[i].comment_author)+"' onerror=\"this.style.display = 'none'\" /></a></div>";
		return commentCompositeDiv ;
	};
};
/* End of SezWho.PluginCore class */

/* Start PopupWindow class */
function PopupWindow() {
	if (!window.popupWindowIndex) { window.popupWindowIndex = 0; }
	if (!window.popupWindowObjects) { window.popupWindowObjects = new Array(); }
	if (!window.listenerAttached) {
		window.listenerAttached = true;
		PopupWindow_attachListener();
	}
	this.index = popupWindowIndex++;
	popupWindowObjects[this.index] = this;
	this.divName = null;
	this.popupWindow = null;
	this.event = new function() {this.type=null,this.ClientX=0,this.ClientY=0;ScreenX=0;ScreenY=0;};
	this.visible = false;
	this.autoHideEnabled = false;
	this.contents = "";
	if (arguments.length>0) {
		this.type="DIV";
		this.divName = arguments[0];
	}
	this.use_gebi = false;
	this.use_css = false;
	this.use_layers = false;
	if (document.getElementById) { this.use_gebi = true; }
	else if (document.all) { this.use_css = true; }
	else if (document.layers) { this.use_layers = true; }
	else { this.type = "WINDOW"; }
	// Method mappings
	this.refresh = PopupWindow_refresh;
	this.showPopup = PopupWindow_showPopup;
	this.hidePopup = PopupWindow_hidePopup;
	this.isClicked = PopupWindow_isClicked;
	this.autoHide = PopupWindow_autoHide;
	this.hideIfNotClicked = PopupWindow_hideIfNotClicked;
	this.updateEvent = PopupWindow_updateEvent;

	// Refresh the displayed contents of the popup
	function PopupWindow_refresh() {
		if (this.divName != null) {
			// refresh the DIV object
			document.getElementById(this.divName).innerHTML = this.contents;
		}
	};
	// Position and show the popup, relative to an anchor object
	function PopupWindow_showPopup() {
			this.x = this.event.ScreenX + this.offsetX;
			this.y = this.event.ScreenY + this.offsetY;
		if (!this.populated && (this.contents != "")) {
			this.populated = true;
			this.refresh();
		}
		if (this.divName != null) {
			// Show the DIV object
			document.getElementById(this.divName).style.left = this.x + "px";
			document.getElementById(this.divName).style.top = this.y + "px";
			document.getElementById(this.divName).style.visibility = "visible";
			document.getElementById(this.divName).style.display = "block";
		}
	};
	// Hide the popup
	function PopupWindow_hidePopup() {
		if (this.divName != null) {
			SezWho.DivUtils.closediv(this.divName);
		}
	};

	// Check an onMouseDown event to see if we should hide
	function PopupWindow_hideIfNotClicked(e) {
		if (this.autoHideEnabled && !this.isClicked(e)) {
			this.hidePopup();
		}
	};


	// Call this to make the DIV disable automatically when mouse is clicked outside it
	function PopupWindow_autoHide() {
		this.autoHideEnabled = true;
	};

	// Pass an event and return whether or not it was the popup DIV that was clicked
	function PopupWindow_isClicked(e) {
		if (this.divName != null) {
			var t = sz_cp_proto.element(e);
			while (t && t.parentNode) {
				if (t.id==this.divName)
					return true;
				t = t.parentNode;
			}
			return false;
		}
		return false;
	};

	// This global function checks all PopupWindow objects onmouseup to see if they should be hidden
	function PopupWindow_hidePopupWindows(e) {
		for (var i=0; i<popupWindowObjects.length; i++) {
			if (popupWindowObjects[i] != null) {
				var p = popupWindowObjects[i];
				//p.hidePopup();
				p.hideIfNotClicked(e);
			}
		}
	};

	// Run this immediately to attach the event listener
	function PopupWindow_attachListener() {
		sz_cp_proto.observe(document, "mouseup", PopupWindow_hidePopupWindows);
	};

	function PopupWindow_updateEvent(e) {
		if (this.divName != null) {
			if (!e) e = window.event;
			this.event.type = e.type;
			this.event.ClientX = e.clientX;
			this.event.ClientY = e.clientY;
			this.event.ScreenX = sz_cp_proto.pointerX(e);
			this.event.ScreenY = sz_cp_proto.pointerY(e);
		}
	};

} ;
/* end of popupwindow constructor */

/* This global function checks all PopupWindow objects to locate if an existing popup with a divName already exists. */
PopupWindow.findPopupWindow = function(divName) {
	if (!window.popupWindowIndex) { window.popupWindowIndex = 0; return null; }
	if (!window.popupWindowObjects) { window.popupWindowObjects = new Array(); return null;}
	if (!window.listenerAttached) {
		window.listenerAttached = true;
		PopupWindow_attachListener();
	}
	for (var i=0; i<popupWindowObjects.length; i++) {
		if (popupWindowObjects[i] != null) {
			var p = popupWindowObjects[i];
			if (divName==p.divName) {return p;}
		}
	}
	return null;
} ;
/* End PopupWindow class */

/* End PopupPositionUtils class */
SezWho.PopupPositionUtils = new function() {

	this.get_offsets = function(isRatings, div_element, w) {
		var div_height = sz_cp_proto.getHeight(div_element) ;
		var div_width = sz_cp_proto.getWidth(div_element) ;
		var vp_height = getViewportHeight() ;
		var vp_width = getViewportWidth() ;
		var shift_offset_x = -40;
		var shift_offset_y = -10;
		if (isRatings){
			shift_offset_x = -(div_width/2);
			shift_offset_y = -(div_height/2);
		}
		var viewport_rel_x = w.event.ClientX;
		var viewport_rel_y = w.event.ClientY;
		// This addresses Safari browser issues
		if((/Safari|Konqueror|KHTML/gi).test(navigator.userAgent) && !(/Safari\/5/gi).test(navigator.userAgent)) {
                        viewport_rel_x = viewport_rel_x - window.pageXOffset;
                        viewport_rel_y = viewport_rel_y - window.pageYOffset;
                }
		var start_top_x = viewport_rel_x + shift_offset_x;
		var start_top_y = viewport_rel_y + shift_offset_y;
		var top_viewport_x = start_top_x;
		var top_viewport_y  = start_top_y;
		if (top_viewport_y < 0) {
			top_viewport_y = 1;
		}
		if (top_viewport_y > vp_height - div_height) {
			top_viewport_y = vp_height - div_height - 1;
		}
		if (top_viewport_x < 0) {
			top_viewport_x = 1;
		}
		if (top_viewport_x  > vp_width - div_width) {
			top_viewport_x = vp_width - div_width - 1;
		}
		var co_ords = new Object();
		co_ords[0] = shift_offset_x + (top_viewport_x - start_top_x);
		co_ords[1]  = shift_offset_y + (top_viewport_y - start_top_y);
		SezWho.Utils.log("info" , "SezWho.PopupPositionUtils.get_offsets returning co-ordinates : x = "+co_ords[0]+" : y = "+co_ords[1]);
		return co_ords ;
	};

	function getViewportHeight() {
		var h=-1;
		var m=document.compatMode;
		if((m || SezWho.Utils.isIE()) && ! SezWho.Utils.isOpera())
		{
			switch(m)
			{
				case "CSS1Compat":h=document.documentElement.clientHeight;
				break;
				default:h=document.body.clientHeight;
			}
		}
		else
		{
			//h=self.innerHeight;
			h=self.innerHeight>document.documentElement.clientHeight ? document.documentElement.clientHeight : self.innerHeight ;
		}
		return h;
	};

	function getViewportWidth() {
		var w=-1;
		var m=document.compatMode;
		if(m || SezWho.Utils.isIE())
		{
			switch(m)
			{
				case "CSS1Compat":w=document.documentElement.clientWidth;
				break;
				default:w=document.body.clientWidth;
			}
		}
		else
		{
			w=self.innerWidth;
		}
		return w;
	};
} ;
/* end PopupPositionUtils class */

/* start SezWho.DivUtils class */
SezWho.DivUtils = new function () {

	this.createRatingsDiv = function () {
		var ratingDiv = document.createElement("div");
		var htmlStr;
		ratingDiv.id = "ratingDiv";
		ratingDiv.className = "cpPopupWrapper";

		htmlStr = "<div class='cpPopupBody cpPopupBodyRating'>";
		htmlStr += "<div class='cpPopupHeaderClose'><a class='cpPopupHeaderCloseButton' href=\"javascript:SezWho.DivUtils.closediv('ratingDiv');\" title='Close'></a></div>";
		htmlStr += "<div class='cpPopupHeaderTitle'><p class='cpPopupHeaderTitleText' id='cpPopupHeaderTitleRatingId'>Rate Content</p><a class='cpPopupHeaderTitleLogo' href='javascript:void SezWho.Utils.openPage(\"home\");' title='"+sz_global_config_params.cpserverurl+"'></a></div>";
		htmlStr += "<div class='cpPopupContentBody'>";
		htmlStr += "<form class='cpPopupContentForm' id='cpEmailForm' action=\"javascript:SezWho.DivUtils.submitRatingForm(document.getElementById('cpEmailForm'),1);SezWho.DivUtils.closediv('ratingDiv');\">";
		htmlStr += "<div><input type='hidden' name='postID' value='3'/><input type='hidden' name='emailID' value=''/><input type='hidden' name='commentID' value='2'/><input type='hidden' name='ratingType' value='C'/><input type='hidden' name='ratingIncrement' value='10'/><span id='popuperror' class='cpPopupErrorSpanHidden'></span></div>";
		htmlStr += "<table class='cpPopupContentTable'>";
		htmlStr += "<tr>";
		htmlStr += "<td class='cpPopupContentRatingLabelCell'><input type='radio' name='sz_confirm_radio' value='login' id='sz_radio_login_reg' checked></td>";
		htmlStr += "<td class='cpPopupContentRatingInputCell'>Login/Register to rate &nbsp;</td>";
		htmlStr += "</tr>";
		htmlStr += "<tr>";
		htmlStr += "<td class='cpPopupContentRatingLabelCell'><input type='radio' name='sz_confirm_radio' id='sz_radio_anon' value='anonymous'></td>";
		htmlStr += "<td class='cpPopupContentRatingInputCell'>Rate anonymously<br />(anonymous votes are weighted less)</td>";
		htmlStr += "</tr>";
		htmlStr += "<tr><td colspan='2' class='cpPopContentTableSeperator'></td></tr>";
		htmlStr += "<tr>";
		htmlStr += "<td class='cpPopupContentRatingButtonsCell' colspan='2'>";
		htmlStr += "<button type='button' id='Submit' onclick=\"SezWho.DivUtils.submitRatingForm(document.getElementById('cpEmailForm'),1);SezWho.DivUtils.closediv('ratingDiv');\" class='cpPopupContentRatingSubmitButton'></button>";
		htmlStr += "<button type='button' onclick=\"SezWho.DivUtils.closediv('ratingDiv');\" class='cpPopupContentRatingCancelButton'></button>";
		htmlStr += "</td>";
		htmlStr += "</tr>";
		htmlStr += "</table>";
		htmlStr += "</form>";
		htmlStr += "</div>";
		htmlStr += "</div>";

		ratingDiv.innerHTML = htmlStr;
		var bodyTag = document.getElementsByTagName("body")[0];
		bodyTag.appendChild(ratingDiv);
		return ratingDiv;

	};
	this.createRatingsHelpDiv = function () {
                var ratingDiv = document.createElement("div");
                var htmlStr;
                ratingDiv.id = "sz_ratingHelpDiv";
                ratingDiv.className = "cpPopupWrapper";

                htmlStr = "<div class='cpPopupBody cpPopupBodyRating'>";
                htmlStr += "<div class='cpPopupHeaderClose'><a class='cpPopupHeaderCloseButton' href=\"javascript:SezWho.DivUtils.closediv('sz_ratingHelpDiv');\" title='Close'></a></div>";
                htmlStr += "<div class='cpPopupHeaderTitle'><p class='cpPopupHeaderTitleText' id='cpPopupHeaderTitleRatingId'>Rate Content</p><a class='cpPopupHeaderTitleLogo' href='javascript:void SezWho.Utils.openPage(\"home\");' title='"+sz_global_config_params.cpserverurl+"'></a></div>";
                htmlStr += "<div class='cpPopupContentBody'>";
                htmlStr += "<p style='margin:10px;text-align:justify;font-size:10pt'>Click on the score icons to rate this content.<br/><br/>By rating the content you are not only expressing your opinion but you also helping the community!<br/><br/>Registered and logged-in user votes count a lot more than anonymous votes - your identity as a rater is always kept private and never shown or transmitted to others.</p>";
                htmlStr += "</div>";
                htmlStr += "</div>";

                ratingDiv.innerHTML = htmlStr;
                var bodyTag = document.getElementsByTagName("body")[0];
                bodyTag.appendChild(ratingDiv);
                return ratingDiv;

        };

        this.activateRatingsHelpDIV = function(ev) {
                // create the popup irrespective of whether the cookie exists or not. This is to display show error messages
                var divName = "sz_ratingHelpDiv";
                var ratingsHelpDiv =  document.getElementById(divName);
                if (ratingsHelpDiv == null) {
					ratingsHelpDiv = SezWho.DivUtils.createRatingsHelpDiv();
                }

                var divPopUp = PopupWindow.findPopupWindow(divName);
                if (!divPopUp) {
					divPopUp= new PopupWindow(divName);
                }
                divPopUp.updateEvent(ev);

                divPopUp.autoHide();
                var co_ordinates = SezWho.PopupPositionUtils.get_offsets(1, divName, divPopUp);
                divPopUp.offsetX = co_ordinates[0];
                divPopUp.offsetY = co_ordinates[1];

                var mainDiv = document.getElementById(divName) ;
                mainDiv.style.display = 'block' ;
                mainDiv.style.visibility = 'visible' ;

                divPopUp.showPopup();
		sz_cp_proto.stop(ev);
        };

	this.createProfileDiv = function () {
		var profileDiv = document.createElement("div");
		profileDiv.id = "profilepopup";
		profileDiv.className = "cpPopupWrapper";
		profileDiv.innerHTML = "<div class='cpPopupBody cpPopupBodyProfile'><div class='cpPopupHeaderClose'><a class='cpPopupHeaderCloseButton' href=\"javascript:SezWho.DivUtils.closediv('profilepopup');\" title='Close'></a></div><div class='cpPopupHeaderTitle'><p class='cpPopupHeaderTitleText' id='cpPopupHeaderTitleTextId'>Public Profile</p><a class='cpPopupHeaderTitleLogo' href='javascript:void SezWho.Utils.openPage(\"home\");' title='"+sz_global_config_params.cpserverurl+"'></a></div><div id='cpPopupContentloadingId' class='cpPopupContentloading'><img src='"+sz_global_config_params.cpserverurl+"/img/wait.gif' alt='loading' class='cpPopupWaitingImage' /></div></div>";
		var bodyTag = document.getElementsByTagName("body")[0];
		bodyTag.appendChild(profileDiv);
		return profileDiv;
	};

	this.submitRatingForm = function(form, setcookieflag)  {
		SezWho.Utils.log("info" , "submitRatingForm called with : cppluginurl = "+sz_global_config_params.cppluginurl);
		var req = null;
		var ratingIncrement= form.ratingIncrement.value ;
		var postID= form.postID.value;
		var i= form.commentID.value;
		var rt = form.ratingType.value;
		var cid = (rt == 'C')?sz_comment_config_params.sz_comment_data[i].comment_id:0;
		var emailID= form.emailID.value;
		var formParam = 'ratingIncrement='+escape(ratingIncrement)+'&blogID='+escape(sz_global_config_params.blogid)+'&siteKEY='+escape(sz_global_config_params.sitekey)+'&postID='+escape(postID)+'&commentID='+escape(cid)+'&rating_type='+rt+'&msec='+SezWho.Utils.getTimeStamp()+'&__mode=cp_comment_rating_submit';

		if(document.getElementById('sz_radio_anon').checked)
			emailID = "anonymous";

		if (document.getElementById('sz_radio_login_reg').checked && emailID == "") {
			var url;
			url = sz_global_config_params.cpserverurl + '/login_pp.php';
			url += '?ratingsubmitpath=' + encodeURIComponent(sz_global_config_params.rating_submit_path);
			url += '&cppluginurl=' + sz_global_config_params.cppluginurl;
			url += '&';
			url += formParam;
			url += '&sz_postRating=true&return_js=true';
			SezWho.Utils.openPage('login_reg', url);
			return ;
		}
		if (rt == 'C')
			SezWho.Utils.ScoreDisplay.setWait("cpEmbedCommScore:"+i);
		else
                  SezWho.Utils.ScoreDisplay.setWait("cpEmbedPostScore:"+postID);
		// now set cookie
		if(setcookieflag && (emailID != "anonymous")) SezWho.Utils.setCookie("SZ_EMAIL_ENC" , emailID, 100);

		SezWho.Utils.makeJSCall(sz_global_config_params.rating_submit_path+"?sz_postRating=true&return_js=true&"+formParam+"&emailID="+encodeURIComponent(emailID));

	};

	this.handleBadgeData = function(sz_badge_data_params) {
		var html_output;
		if (navigator.userAgent.match(/msie (5\.5|6)/i)&&navigator.platform=="Win32") //PNG fix
                        html_output = "<table class='szBadgeBody szBadgeBodyIE'>";
                else
			html_output = "<table class='szBadgeBody'>";

		var widget_placeholder = "sezwho_badge_" + sz_badge_data_params['widget_no'] + "_placeholder";
		var currentLength = sz_badge_data_params['widget_no'];
		sz_badge_config_params.sz_badge_data[currentLength] = sz_badge_data_params;
		html_output = html_output + "<tr><td class='szBadgeBodyFiller'></td></tr>";
		html_output = html_output + "<tr><td class='szBadgeBodyDataName'><a class='szBadgeBodyDataNameLink' id='sz_badge_profile_link:" + sz_badge_config_params.sz_badge_data[currentLength]['widget_no'] + "' href='javascript:void(0);' onmousedown='javascript:SezWho.Utils.cpProfileBadgeEventHandler(event)' onmouseover='javascript:SezWho.Utils.cpProfileBadgeEventHandler(event)' onmouseout='javascript:SezWho.DivUtils.cancelPopUp();'>" + sz_badge_config_params.sz_badge_data[currentLength]['formatted_name'] + "</a></td></tr>";
		html_output = html_output + "<tr><td class='szBadgeBodyDataSP'>Star Power:</td></tr>";
		html_output = html_output + "<tr><td class='szBadgeBodyDataButtons'>" + SezWho.Utils.get_star_rating(sz_badge_config_params.sz_badge_data[currentLength]['sz_score'], "badge") + "</td></tr>";
		html_output = html_output + "<tr><td class='szBadgeBodyLogo'><a class='szBadgeBodyLogoLink' href=\"javascript:void SezWho.Utils.openPage('home');\"></a></td></tr>";
		html_output = html_output + "</table>";

		document.getElementById(widget_placeholder).innerHTML = html_output;
	};

	this.handleRedCarpetData = function(sz_rc_data_params, sz_rc_input_params) {
		var html_output = '<table class="szRCEmbedTableClass szRCEmbedTableClassCustom" style="width:' + sz_rc_input_params['sz_rc_width'] + 'px;">';
		sz_rc_config_params.sz_rc_data = sz_rc_data_params;

		html_output = html_output + "<thead><tr><th colspan='2'><span id='szRCEmbedTitleID'>Red Carpet</span></th></tr></thead>";
		if (navigator.userAgent.match(/msie (5\.5|6)/i)&&navigator.platform=="Win32") //PNG fix
			html_output = html_output + "<tfoot><tr><td colspan='2' class='szRCEmbedBrandingCell' align='center'><a class='cpEmbedPageTableCellBrandingLink cpEmbedPageTableCellBrandingLinkIE' href=\"javascript:void SezWho.Utils.openPage('home');\"></a></td></tr></tfoot><tbody>";
                else
			html_output = html_output + "<tfoot><tr><td colspan='2' class='szRCEmbedBrandingCell' align='center'><a class='cpEmbedPageTableCellBrandingLink' href=\"javascript:void SezWho.Utils.openPage('home');\"></a></td></tr></tfoot><tbody>";

		for(var i=0; i<sz_rc_input_params['sz_rc_rows']; i++)
		{
			html_output = html_output + "<tr>";
			if (sz_rc_input_params['sz_use_blog_catalog_for_rc']==1)
			{
				if (sz_rc_config_params.sz_rc_data[i]['comment_author_url'] && sz_rc_config_params.sz_rc_data[i]['comment_author_url'].length > 0)
					html_output = html_output + "<td><a class='cpEmbedImageUserLink' href='javascript:void(0);' onclick='javascript:SezWho.Utils.szClick(\"IC\",\""+sz_rc_config_params.sz_rc_data[i]['comment_author_url']+"\",\"0\",\"0\");' title='"+sz_rc_input_params['sz_commenter_pic_title']+unescape(sz_rc_config_params.sz_rc_data[i]['comment_author'])+"'><img class='szRCEmbedImage szRCEmbedImageCustom' src='"+sz_rc_input_params['sz_user_image_repo']+sz_rc_config_params.sz_rc_data[i]['md5_email']+sz_rc_input_params['sz_user_image_repo_post']+"' onerror=\"this.src='http://s3.amazonaws.com/sz_users_images/noimg.gif'\"/></a></td>";
				else
					html_output = html_output + "<td><a class='cpEmbedImageUserLink' href='javascript:void(0);' title='"+sz_rc_input_params['sz_commenter_pic_title']+unescape(sz_rc_config_params.sz_rc_data[i]['comment_author'])+"'><img class='szRCEmbedImage szRCEmbedImageCustom' src='"+sz_rc_input_params['sz_user_image_repo']+sz_rc_config_params.sz_rc_data[i]['md5_email']+sz_rc_input_params['sz_user_image_repo_post']+"' onerror=\"this.src='http://s3.amazonaws.com/sz_users_images/noimg.gif'\"/></a></td>";
			}
			else
			{
				if (sz_rc_config_params.sz_rc_data[i]['comment_author_url'] && sz_rc_config_params.sz_rc_data[i]['comment_author_url'].length > 0)
					html_output = html_output + "<td><a class='cpEmbedImageUserLink' href='javascript:void(0);' onclick='javascript:SezWho.Utils.szClick(\"IC\",\""+sz_rc_config_params.sz_rc_data[i]['comment_author_url']+"\",\"0\",\"0\");'><img class='szRCEmbedImage szRCEmbedImageCustom' src='http://s3.amazonaws.com/sz_users_images/" + sz_rc_config_params.sz_rc_data[i]['md5_email'] + "_t' onerror=\"this.src='http://s3.amazonaws.com/sz_users_images/noimg.gif'\" style='width:" + sz_rc_input_params['sz_rc_img_width'] + "px;height:" + sz_rc_input_params['sz_rc_img_height'] + "px;'></a></td>";
				else
					html_output = html_output + "<td><a class='cpEmbedImageUserLink' href='javascript:void(0);'><img class='szRCEmbedImage szRCEmbedImageCustom' src='http://s3.amazonaws.com/sz_users_images/" + sz_rc_config_params.sz_rc_data[i]['md5_email'] + "_t' onerror=\"this.src='http://s3.amazonaws.com/sz_users_images/noimg.gif'\" style='width:" + sz_rc_input_params['sz_rc_img_width'] + "px;height:" + sz_rc_input_params['sz_rc_img_height'] + "px;'></a></td>";
			}

			html_output = html_output + "<td><a id='sz_profile_link:" + i + "' class='szEmbedCommeterName szEmbedCommeterNameCustom' onmouseover='javascript:SezWho.Utils.cpProfileRCEventHandler(event)' onmousedown='javascript:SezWho.Utils.cpProfileRCEventHandler(event)' href='javascript:void(0);' onmouseout='javascript:SezWho.DivUtils.cancelPopUp();'>" + unescape(sz_rc_config_params.sz_rc_data[i]['comment_author']).replace(/\+/g, " ") + "</a><br>" + SezWho.Utils.get_star_rating(sz_rc_config_params.sz_rc_data[i]['sz_score'], "redcarpet") + "<br><a class='szEmbedCommetLink szEmbedCommetLinkCustom' href='javascript:void(0);' onclick='javascript:SezWho.Utils.szClick(\"CC\",\"" + sz_rc_config_params.sz_rc_data[i]['comment_url'] + "\",\"0\",\"0\");' >View Comment</a></td>";

			html_output = html_output + "</tr>";
			html_output = html_output + "<tr><td colspan='2' class='szRCEmbedTableSeperator'></td></tr>";
		}
		html_output = html_output +"</tbody></table>";
		document.getElementById('cp_rc_placeholder').innerHTML = html_output;
	};

	this.handleRating = function(response, count) {
		var form = document.getElementById('cpEmailForm');
		var ratingIncrement= form.ratingIncrement.value ;
		var i= form.commentID.value;
		var rt = form.ratingType.value;
		var postID = form.postID.value;
		var responseArr = response.split(',');

		var status ;
		if (responseArr[0]) status = responseArr[0].split('=')[1];
		if (status == 'Failure' || status == 'N') {
			var errorMsgCode = responseArr[1].split('=')[1];
			var errorMsg = "Failure in processing!";
			if (errorMsgCode == "DuplicateRatingInsertion") errorMsg = "You can not rate same content again!" ;
			if (errorMsgCode == "NoSiteKey") errorMsg = "No Site Key found!" ;
			if (errorMsgCode == "InvalidSiteKey" || errorMsgCode == "SITEKEYERR") errorMsg = "Invalid Site Key found!" ;
			if (errorMsgCode == "EmailInsertionFailure") errorMsg = "Failure inserting your email ID!" ;
			if (errorMsgCode == "RatingInsertionFailure") errorMsg = "Failure inserting your rating!" ;
			if (errorMsgCode == "NoBlogKey") errorMsg = "No Blog Key found!" ;
			if (errorMsgCode == "SelfRating") errorMsg = "You can not rate your own content!" ;
			if (rt == 'C')
				SezWho.Utils.ScoreDisplay.setScore("cpEmbedCommScore:"+i,sz_comment_config_params.sz_comment_data[i].comment_score,0);
			else
				SezWho.Utils.ScoreDisplay.setScore("cpEmbedPostScore:"+postID,sz_post_config_params[postID].post_score,0);
			SezWho.DivUtils.activateRatingsDIV(errorMsg);
		} else {
			var score = responseArr[1].split('=')[1];
			if (rt == 'C') {
				SezWho.Utils.ScoreDisplay.setScore("cpEmbedCommScore:"+i,score,count);
				sz_comment_config_params.sz_comment_data[i].comment_score = score;
			} else {
				SezWho.Utils.ScoreDisplay.setScore("cpEmbedPostScore:"+postID,score,count);
				sz_post_config_params[postID].post_score = score;
                        }
		}
	};


        this.activateRatingsDIV = function(errorMsg) {
                // create the popup irrespective of whether the cookie exists or not. This is to display show error messages
                var divName = "ratingDiv";

                if (errorMsg != ""){
                        var errorDiv = document.getElementById("popuperror") ;
                        errorDiv.className = "cpPopupErrorSpanDisplayed";
                        errorDiv.innerHTML = errorMsg;
                        document.getElementById('popuperror').style.visibility='visible';
                }
                var divPopUp = PopupWindow.findPopupWindow(divName);
                divPopUp.autoHide();
                var co_ordinates = SezWho.PopupPositionUtils.get_offsets(1, divName, divPopUp);
                divPopUp.offsetX = co_ordinates[0] ;
                divPopUp.offsetY = co_ordinates[1] ;

                var mainDiv = document.getElementById(divName) ;
                mainDiv.style.display = 'block' ;
                mainDiv.style.visibility = 'visible' ;

                divPopUp.showPopup();
        };


	this.displayProfileData = function (name,data){
		sz_cp_proto.replace(document.getElementById('cpPopupContentloadingId'), data);
		if (name != '') document.getElementById('cpPopupHeaderTitleTextId').innerHTML = decodeURIComponent(name);

		var divPopUp = PopupWindow.findPopupWindow("profilepopup");
		var co_ordinates = SezWho.PopupPositionUtils.get_offsets(0, "profilepopup", divPopUp);
		divPopUp.offsetX = co_ordinates[0] ;
		divPopUp.offsetY = co_ordinates[1] ;
		divPopUp.showPopup();
	};

	this.loadProfilePopUp = function(url, divName, author_name)
	{
		SezWho.Utils.log("info" , "SezWho.DivUtils.loadProfilePopUp called with cpserverurl = "+sz_global_config_params.cpserverurl+" : cppluginurl = "+sz_global_config_params.cppluginurl+" : url = "+url+" : divName = "+divName+" : author_name = "+author_name);
		var profileDiv =  document.getElementById(divName);
		if (profileDiv == null) profileDiv = this.createProfileDiv();
		else {
			if (document.getElementById('cpPopupContentBodyID'))
			sz_cp_proto.replace(document.getElementById('cpPopupContentBodyID'), "<div id='cpPopupContentloadingId' class='cpPopupContentloading'><img src='"+sz_global_config_params.cpserverurl+"/img/wait.gif' alt='loading' class='cpPopupWaitingImage' /></div>");
		}
		document.getElementById('cpPopupHeaderTitleTextId').innerHTML = decodeURIComponent(author_name);

		var divPopUp = PopupWindow.findPopupWindow(divName);
		if (!divPopUp) divPopUp= new PopupWindow(divName);
		divPopUp.autoHide();
		var co_ordinates = SezWho.PopupPositionUtils.get_offsets(0, divName, divPopUp);
		divPopUp.offsetX = co_ordinates[0] ;
		divPopUp.offsetY = co_ordinates[1] ;
		divPopUp.showPopup();
		SezWho.Utils.makeJSCall(url);

	};

	var divpopuptimerid = 0 ;
	this.loadMyProfileDiv = function(url, div, author_name, delay, ev){
		SezWho.Utils.log("info" , "SezWho.DivUtils.loadMyProfileDiv called with cpserverurl = "+sz_global_config_params.cpserverurl+" : cppluginurl = "+sz_global_config_params.cppluginurl+" : url = "+url+" : div = "+div+" : author_name = "+unescape(author_name)+" : delay = "+delay+" : ev = "+ev);
		var w = PopupWindow.findPopupWindow(div);
		if (!w) w = new PopupWindow(div);
		w.updateEvent(ev);
		divpopuptimerid= setTimeout("SezWho.DivUtils.loadProfilePopUp('"+url+"','"+div+"','"+author_name+"')", delay);
	};

	this.closediv = function(divid) {
		SezWho.Utils.log("info" ,"SezWho.DivUtils.closediv called with divid = "+divid);
		document.getElementById(divid).style.visibility='hidden';
		//document.getElementById(divid).style.display='none';
		if (divid == "ratingDiv") {
			document.getElementById('popuperror').style.visibility='hidden';
			SezWho.Utils.ScoreDisplay.revert();
		}
	};

	this.cancelPopUp = function() {
		SezWho.Utils.log("info" ,"SezWho.DivUtils.cancelPopUp called ");
		if(divpopuptimerid) clearTimeout(divpopuptimerid);

	};
} ;
/* end SezWho.DivUtils */


/* This following code has been adapted from the fabulous prototype JS framework.*/
SezWho.Utils.Prototype = function () {

	this.getElementsByClassName = function (node_id, className) {
		SezWho.Utils.log("info" ,"SezWho.Utils.Prototype.getElementsByClassName called with node_id = "+node_id+" : className = "+className);
		var node ;
		var els ;
		/*
		if (node_id == null) {
		node = document.body ;
		} else {
		node = document.getElementById(node_id);
		}*/
		node = document.body ;
		var els = node.getElementsByTagName("*");
		var a = [];
		var re = new RegExp("(^|\\s)" + className + "(\\s|$)") ;
		for(var i=0,j=els.length; i<j; i++) {
			if(re.test(els[i].className)) {
				a.push(els[i]);
			}
		}
		return a;
	};

	observers = false ;

	this.observe = function (element, name, observer, useCapture) {
		SezWho.Utils.log("info" ,"SezWho.Utils.Prototype.observe called with element = "+element+" : name = "+name);
		if (!this.observers) this.observers = [];
		if (element.addEventListener) {
			this.observers.push([element, name, observer, useCapture]);
			element.addEventListener(name, observer, useCapture);
		} else if (element.attachEvent) {
			this.observers.push([element, name, observer, useCapture]);
			element.attachEvent('on' + name, observer);
		}
	};

	this.replace = function (element, html){
		if(element == null)
			return;
		if (element.outerHTML) {
			element.outerHTML = html;
		} else {
			var range = element.ownerDocument.createRange();
			range.selectNodeContents(element);
			element.parentNode.replaceChild(
			range.createContextualFragment(html), element);
		}
		return element;
	};
	this.unloadCache = function() {
		SezWho.Utils.log("info" ,"SezWho.Utils.Prototype.unloadCache called");
		if (!this.observers) return;
		for (var i = 0, length = this.observers.length; i < length; i++) {
			this.stopObserving.apply(this, this.observers[i]);
			this.observers[i][0] = null;
		}
		this.observers = false;
	};


	this.stopObserving = function(element, name, observer, useCapture) {
		SezWho.Utils.log("info" ,"SezWho.Utils.Prototype.stopObserving called with element = "+element+" : name = "+name);
		if (element.removeEventListener) {
			element.removeEventListener(name, observer, useCapture);
		} else if (element.detachEvent) {
			try {
				element.detachEvent('on' + name, observer);
			} catch (e) {}
		}
	};

	this.element = function(event) {
		return event.target || event.srcElement;
	};

	this.stop = function(event) {
		if (SezWho.Utils.isIE())
			window.event.cancelBubble = true;
		else
			event.stopPropagation();
	};

	this.getHeight = function(element) {
		return this.getDimensions(element).height;
	};

	this.getWidth = function(element) {
		return this.getDimensions(element).width;
	};

	this.pointerX = function(event) {
		return event.pageX || (event.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft));
	};

	this.pointerY = function(event) {
		return event.pageY || (event.clientY + (document.documentElement.scrollTop || document.body.scrollTop));
	};
	this.hasAttribute = function(element, attr) {
		return typeof element.attributes[attr] != "undefined";
	};


	this.getDimensions = function (element_id) {
		var element = document.getElementById(element_id);
		var display = element.style.display;
		if (display != 'none' && display != null) // Safari bug
		return {width: element.offsetWidth, height: element.offsetHeight};

		// All *Width and *Height properties give 0 on elements with display none,
		// so enable the element temporarily
		var els = element.style;
		var originalVisibility = els.visibility;
		var originalPosition = els.position;
		var originalDisplay = els.display;
		els.visibility = 'hidden';
		els.position = 'absolute';
		els.display = 'block';
		var originalWidth = element.clientWidth;
		var originalHeight = element.clientHeight;
		els.display = originalDisplay;
		els.position = originalPosition;
		els.visibility = originalVisibility;
		return {width: originalWidth, height: originalHeight};
	};
} ;

var sz_cp_proto = new SezWho.Utils.Prototype() ;

/*  start SezWho.Utils - class for general purpose utility methods */
SezWho.Utils = new function () {

	this.get_star_rating = function(score, type) {
		SezWho.Utils.log("info" ,"SezWho.Utils.get_star_rating called with score = "+score);
		var ratings_images = "" ;
		var title = "";
		var full_button_class, half_button_class, zero_button_class;
		score = score / 2;
		score = score.toString().substr(0, score.toString().indexOf(".")+3);

		if("comment" == type)
		{
			title = "Comment Score" + ": " + score;
			full_button_class = "cpEmbedScoreSquareImgon cpEmbedScoreSquareImgonCustom";
			half_button_class = "cpEmbedScoreSquareImghalf cpEmbedScoreSquareImghalfCustom";
			zero_button_class = "cpEmbedScoreSquareImgoff cpEmbedScoreSquareImgoffCustom";
		}
		else if("post" == type)
		{
			title = "Post Score" + ": " + score;
			full_button_class = "cpEmbedScoreSquareImgon cpEmbedScoreSquareImgonCustom";
			half_button_class = "cpEmbedScoreSquareImghalf cpEmbedScoreSquareImghalfCustom";
			zero_button_class = "cpEmbedScoreSquareImgoff cpEmbedScoreSquareImgoffCustom";
		}
		else if("redcarpet" == type)
		{
			title = "Star Power" + ": " + score;
			full_button_class = "szRCStarImgon szRCStarImgonCustom";
			half_button_class = "szRCStarImghalf szRCStarImghalfCustom";
			zero_button_class = "szRCStarImgoff szRCStarImgoffCustom";
		}
		else if("badge" == type)
		{
			title = "Star Power" + ": " + score;
			full_button_class = "cpPopupContentUserStarImgonBG";
			half_button_class = "cpPopupContentUserStarImghalfBG";
			zero_button_class = "cpPopupContentUserStarImgoffBG";
		}
		else
		{
			return "";
		}
		var full_img = "<button title='" + title + "' type='button' class='" + full_button_class + "'></button>";
		var half_img = "<button title='" + title + "' type='button' class='" + half_button_class + "'></button>";
		var zero_img = "<button title='" + title + "' type='button' class='" + zero_button_class + "'></button>";

		if (score < 0.25) ratings_images = zero_img+zero_img+zero_img+zero_img+zero_img ;
		else if (score >= 0.25 && score < 0.75) ratings_images = half_img+zero_img+zero_img+zero_img+zero_img ;
		else if (score >= 0.75 && score < 1.25) ratings_images = full_img+zero_img+zero_img+zero_img+zero_img ;
		else if (score >= 1.25 && score < 1.75) ratings_images = full_img+half_img+zero_img+zero_img+zero_img ;
		else if (score >= 1.75 && score < 2.25) ratings_images = full_img+full_img+zero_img+zero_img+zero_img ;
		else if (score >= 2.25 && score < 2.75) ratings_images = full_img+full_img+half_img+zero_img+zero_img ;
		else if (score >= 2.75 && score < 3.25) ratings_images = full_img+full_img+full_img+zero_img+zero_img ;
		else if (score >= 3.25 && score < 3.75) ratings_images = full_img+full_img+full_img+half_img+zero_img ;
		else if (score >= 3.75 && score < 4.25) ratings_images = full_img+full_img+full_img+full_img+zero_img ;
		else if (score >= 4.25 && score < 4.75) ratings_images = full_img+full_img+full_img+full_img+half_img ;
		else if (score >= 4.75) ratings_images = full_img+full_img+full_img+full_img+full_img ;
		return ratings_images;
	};

        this.setLocalCookie = function(cookieName,cookieValue,nDays) {
                SezWho.Utils.log("info" ,"SezWho.Utils.setLocalCookie called with cookieName = "+cookieName);
                var today = new Date();
                var expire = new Date();
                if (nDays==null || nDays==0) nDays=1;
                expire.setTime(today.getTime() + 3600000*24*nDays);
                document.cookie = cookieName+"="+cookieValue+ ";expires="+expire.toGMTString()+";path=/";
        };

        this.setCookie = function(cookieName,cookieValue,nDays) {
                SezWho.Utils.setLocalCookie(cookieName,cookieValue,nDays);
                SezWho.Utils.makeJSCall(sz_global_config_params.cpserverurl+"/SetCookieEnc/SZ_EMAIL_ENC/"+escape(cookieValue)+"/"+86400*nDays+"/"+SezWho.Utils.getTimeStamp());
                //SezWho.Utils.makeJSCall(sz_global_config_params.cpserverurl+"/cookiehandler.php?op=SetCookie&user="+escape(cookieValue)+"&nDays="+nDays+"&time="+SezWho.Utils.getTimeStamp());
        };

        this.makeJSCall = function(url) {
                        var script = document.createElement('script');
                        script.src = url;
                        document.getElementsByTagName('head')[0].appendChild(script);
        };

        this.readSZCookie  = function(email) {
                if (email != ""){
                        var szForm = document.getElementById('cpEmailForm');
                        SezWho.Utils.setLocalCookie("SZ_EMAIL_ENC" , email, 100);
                        szForm.emailID.value = email ;
                        SezWho.DivUtils.submitRatingForm(szForm, 0);
                }
                else
                        SezWho.DivUtils.activateRatingsDIV("");
        };

        this.getTimeStamp = function(name){
                var d = new Date();
                return d.getTime();
        };

        this.readCookie = function(name) {
                var cookieVal = null;
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for(var i=0;i < ca.length;i++) {
                        var c = ca[i];
                        while (c.charAt(0)==' ')
                                c = c.substring(1,c.length);
                        if (c.indexOf(nameEQ) == 0) {
                                cookieVal = c.substring(nameEQ.length,c.length);
                                return cookieVal;
                        }
                }
                return cookieVal;
        };

	this.cpProfileEventHandler = function(ev) {
		SezWho.Utils.log("info" ,"SezWho.Utils.cpProfileEventHandler called with event = "+ev);
		var elm = sz_cp_proto.element(ev);
		var a = elm.id.split(":");
		var i = elm.id;
		var delay=0;
		if (ev.type == "mouseover") delay=500;
		SezWho.DivUtils.loadMyProfileDiv(sz_global_config_params.cpserverurl+"/webservices/ykgetprofile.php?plugin_version="+sz_global_config_params.plugin_version+"&site_key="+sz_global_config_params.sitekey+"&blog_key="+sz_global_config_params.blogkey+"&blog_id="+sz_global_config_params.blogid+"&commenter_email="+encodeURIComponent(sz_comment_config_params.sz_comment_data[a[1]].comment_author_email)+"&isplugin=true&source=comment&comment_id="+sz_comment_config_params.sz_comment_data[a[1]].comment_id+"&posting_id="+sz_comment_config_params.post_id, 'profilepopup', sz_comment_config_params.sz_comment_data[a[1]].comment_author, delay, ev);
	};

		this.cpProfileImgClickHandler = function(i, type) {
		if (type == 'P')
                	SezWho.Utils.openPage("pic",sz_global_config_params.cpserverurl+"/mypublicprofile.php?commenter_email="+encodeURIComponent(sz_post_config_params[i].blog_author_email)+"&isplugin=true&viewid=0");
		else
                	SezWho.Utils.openPage("pic",sz_global_config_params.cpserverurl+"/mypublicprofile.php?commenter_email="+encodeURIComponent(sz_comment_config_params.sz_comment_data[i].comment_author_email)+"&isplugin=true&viewid=0");
        };

        this.cpProfilePostEventHandler = function(ev) {
                var elm = sz_cp_proto.element(ev);
                var a = elm.id.split(":");
                var i = elm.id;
                var delay=0;
                if (ev.type == "mouseover") delay=500;
                SezWho.DivUtils.loadMyProfileDiv(sz_global_config_params.cpserverurl+"/webservices/ykgetprofile.php?plugin_version="+sz_global_config_params.plugin_version+"&site_key="+sz_global_config_params.sitekey+"&blog_key="+sz_global_config_params.blogkey+"&blog_id="+sz_global_config_params.blogid+"&commenter_email="+encodeURIComponent(sz_post_config_params[a[1]].blog_author_email)+"&isplugin=true&source=post", 'profilepopup', sz_post_config_params[a[1]].blog_author_name, delay, ev);
        };

	this.cpProfileRCEventHandler = function(ev) {
		SezWho.Utils.log("info" ,"SezWho.Utils.cpProfileEventHandler called with event = "+ev);
		var elm = sz_cp_proto.element(ev);
		var a = elm.id.split(":");
		var i = elm.id;
		var delay=0;
		if (ev.type == "mouseover") delay=500;
		SezWho.DivUtils.loadMyProfileDiv(sz_global_config_params.cpserverurl+"/webservices/ykgetprofile.php?plugin_version="+sz_global_config_params.plugin_version+"&site_key="+sz_global_config_params.sitekey+"&blog_key="+sz_global_config_params.blogkey+"&blog_id="+sz_global_config_params.blogid+"&commenter_email="+encodeURIComponent(sz_rc_config_params.sz_rc_data[a[1]].comment_author_email)+"&isplugin=true&source=red_carpet", 'profilepopup', sz_rc_config_params.sz_rc_data[a[1]].comment_author, delay, ev);
	};

	this.cpProfileBadgeEventHandler = function(ev) {
			SezWho.Utils.log("info" ,"SezWho.Utils.cpProfileBadgeEventHandler called with event = "+ev);
			var elm = sz_cp_proto.element(ev);
			var a = elm.id.split(":");
			var i = elm.id;
			var delay=0;
			if (ev.type == "mouseover") delay=500;
			SezWho.DivUtils.loadMyProfileDiv(sz_global_config_params.cpserverurl+"/webservices/ykgetprofile.php?plugin_version="+sz_global_config_params.plugin_version+"&site_key="+sz_global_config_params.sitekey+"&blog_key="+sz_global_config_params.blogkey+"&blog_id="+sz_global_config_params.blogid+"&commenter_email="+encodeURIComponent(sz_badge_config_params.sz_badge_data[a[1]].comment_author_email)+"&isplugin=true&source=badge", 'profilepopup', sz_badge_config_params.sz_badge_data[a[1]].comment_author, delay, ev);
	};

        this.szHandleRatingButtonClicks =  function(ev, value, elm){
                var a = elm.id.split(":");
                var divName = 'ratingDiv';
                var ratingsDiv =  document.getElementById(divName);
                if (ratingsDiv == null) {
                                ratingsDiv = SezWho.DivUtils.createRatingsDiv ();
                }
                var szForm = document.getElementById('cpEmailForm');
		if (a[0] == "cpEmbedPostScore"){
			szForm.postID.value = a[1];
			szForm.commentID.value = 0;
			szForm.ratingType.value = 'P';
		}
		else {
			szForm.postID.value = sz_comment_config_params.post_id;
			szForm.commentID.value = a[1];
			szForm.ratingType.value = 'C';
		}
                szForm.ratingIncrement.value = value;
		document.getElementById('sz_radio_login_reg').checked = 1;
		document.getElementById('sz_radio_anon').checked = 0;

                // create the popup irrespective of whether the cookie exists or not.
                var divPopUp = PopupWindow.findPopupWindow(divName);
                if (!divPopUp) {
                                divPopUp= new PopupWindow(divName);
                }
                divPopUp.updateEvent(ev);

                //First read the local cookie
                var cookieVal = SezWho.Utils.readCookie("SZ_EMAIL_ENC");
			if (!cookieVal)
				cookieVal = SezWho.Utils.readCookie("SZ_MS_USER");
                if (cookieVal) {
                        szForm.emailID.value = cookieVal ;
                        SezWho.DivUtils.submitRatingForm(szForm,0);
                } else  // Read the SezWho cookie
                        SezWho.Utils.makeJSCall(sz_global_config_params.cpserverurl+"/cookiehandler.php?op=GetCookie&plugin_version="+sz_global_config_params.plugin_version+"&time="+SezWho.Utils.getTimeStamp());

                elm.blur();
        };

	this.openPage = function(type, url) {
		if('undefined' == typeof url) url = null;
		var name, options;
		name="";
		options="";
		if (type == "home") url = sz_global_config_params.cpserverurl;
		else if (type == "help") {
			url = sz_global_config_params.cpserverurl+"/popup_help.php";
			name = "Help";
			options = "height=820,width=600,toolbar=no,scrollbars,resizable";
		}
		else if (url == null) url = sz_global_config_params.cpserverurl;
		else if (type == "login_reg") {
			//name = "Login/Register into SezWho";
			options = "height=355,width=400,toolbar=no,status=no,location=no,menubar=no,resizable=no";
		}
		var newWindow = window.open(url, name, options);
	};

	this.addCSS = function(url) {
		if (!document.getElementById("szProfAndEmbedStyleSheet")){
                        var stylesheet = document.createElement("link");
                        stylesheet.rel = "stylesheet";
                        stylesheet.type = "text/css";
			if (typeof(url) == "undefined" || url == ""){
				if (sz_global_config_params.sz_use_local_css_js)
					stylesheet.href = sz_global_config_params.cppluginurl+"sezwho.css";
				else                        		
					stylesheet.href = sz_global_config_params.cpserverurl+"/widgets/profile/css_output/"+sz_global_config_params.platform+"/"+sz_global_config_params.theme+"/"+sz_global_config_params.plugin_version+"/"+sz_global_config_params.js_tag_name+"/"+sz_global_config_params.sitekey+"/"+sz_global_config_params.blogkey+".css";
			}
			else
				stylesheet.href = url;
                        stylesheet.media = "screen";
                        stylesheet.id = "szProfAndEmbedStyleSheet";
                        document.lastChild.firstChild.appendChild(stylesheet);
                }
	};

	this.callJSFramework = function() {
		SezWho.Utils.log("info" ,"SezWho.Utils.callJSFramework called ");
		if (typeof(sz_global_config_params) == "undefined" && SezWho.BlogPlatformCallbackJS.get_platform() == "blogger" && !sz_inproc_blogger) {
			sz_inproc_blogger = 1;
			var blog_data = SezWho.BlogPlatformCallbackJS.get_blog_data();
			var blogID = blog_data["blogID"] ;
			var postID = blog_data["postID"] ;
			var firstCommentID = blog_data["firstCommentID"] ;
			var blog_key = blog_data["blogKey"]  ;
            var u = blog_data["serverURL"];
			var js_param_url = u+"/js_params2.php?blogid="+blogID+"&blogkey="+blog_key+"&postid="+postID+"&firstCommentID="+firstCommentID ;
			var css_param_url = u+"/widgets/profile/css_output/BG/default/2.0/2.0/blogger/"+blog_key+".css";
                        SezWho.Utils.makeJSCall(js_param_url);
                        SezWho.Utils.addCSS(css_param_url);
                }
                if(!(typeof (sz_global_config_params) == 'undefined') && !sz_UI_rendering_done){
			sz_UI_rendering_done = 1;
                        SezWho.Utils.addCSS("");
                        SezWho.PluginCore.szManageEmbedUI();
                        SezWho.PluginCore.szManagePostUI();
			if (location.hash != ""){
				var id = location.hash;
				var n = SezWho.BlogPlatformCallbackJS.get_comment_node(id.substr(1));
				if (!n) return;
				var p=SezWho.Utils.ScoreDisplay.abPos(n);
				window.scroll(0,p.Y); // horizontal and vertical scroll targets
			}
                }

		if (typeof (sz_global_config_params) == 'undefined')
			window.sz_presence_processed = 0;
		if(!(typeof (sz_global_config_params) == 'undefined') && !window.sz_presence_processed){
			window.sz_presence_processed = 1;
        		SezWho.Utils.processPresence();
                }

	};
	this.processPost = function(id) {
                SezWho.Utils.addCSS("");
                SezWho.PluginCore.szManagePostUI(id);
        };

	this.isSafari = function(){ return (/Safari|Konqueror|KHTML/gi).test(navigator.userAgent) ; };

	this.isIE = function() { return (!SezWho.Utils.isSafari() &&!navigator.userAgent.match(/opera/gi) && navigator.userAgent.match(/msie (5\.5|6|7)/gi)) ; };

	this.isOpera = function() { return (navigator.userAgent.match(/Opera/gi)) ; };

	this.log = function(type, msg) {
		if ((type == 'info' || type == 'debug') && !window.is_debug_log_enabled) return ;
		var self = arguments.callee;
		if (window.console) (window.console[type] || window.console.log )(msg);
		else if (window.opera) window.opera.postError(msg);
		else if ( window.Log) {
			self._logger = self._logger || new Log(Log.INFO, Log.popupLogger);
			self._logger[type](msg);
		}
	};
	this.szClick  = function (t,url,vid,key) {window.open(sz_global_config_params.cpserverurl+'/clickhandler.php?referrer='+escape(document.location)+'&linktype='+t+'&viewid='+vid+'&key='+key+'&linkurl='+escape(url));};

	this.szImageSource = function(name, imgsrc){
                var altt = "";
                imgsrc = imgsrc.toLowerCase();
                if (name != null && name != "") name = name+"'s ";
                if (imgsrc == 'http://s3.amazonaws.com/sz_users_images/noimg.gif') altt = "no image";
                else if (imgsrc.match("gravatar")) altt = name + "image from Gravatar";
                else if (imgsrc.match("blogcatalog")) altt = name + "image from Blogcatalog";
                else if (imgsrc.match("sz_users_images")) altt = name + "image from SezWho";
                else if (imgsrc.match("yahoo")) altt = name + "image from MyBlogLog";
                else altt = "no image";
                return altt;
        };

	this.szImageSourceById = function(id){
		var a = id.split(":");
		var elm = document.getElementById(id);
		elm.alt = SezWho.Utils.szImageSource(unescape(sz_comment_config_params.sz_comment_data[a[1]].comment_author),elm.src);
		elm.title = elm.alt;
        };

	this.processPresence = function() {
		try{
			var d=new Date();
			var u=escape(document.location.href);
			var e=document.referrer;
			var e_ref="";
			if(e!="" && e) 
				e_ref='&e='+escape(e);			
			var p= (typeof(sz_custom_ratings_text) == "undefined")?"": sz_global_config_params.person_id;
			var p_ref = "";
			if(p!="" && p) 
				p_ref='&p='+escape(p);
			var t = "?b="+ sz_global_config_params.blogkey+ "&u=" +u+ "&a=" + escape(navigator.userAgent)+e_ref+p_ref+ "&n=" +d.valueOf();
			var x = document.createElement('IMG');			
			x.src=sz_global_config_params.cpserverurl+'/co.php'+t;
		}
		catch(err){}
			return true;
	};


	/*
	function _error(){ log("error" , msg) ; }
	function _warn(){ log("warn" , msg) ; }
	function _debug(){ log("debug" , msg) ; }
	function _info(){ log("info" , msg) ; }
	*/
};
/* End SezWho.Utils class */
var sz_inproc_blogger = 0;
var sz_UI_rendering_done = 0;

/*  start SezWho.Utils.CommentFilterProcessing -  class for comment filter processing */
SezWho.Utils.CommentFilterProcessing = new function () {
	//hide = 0, show = 1, toggle = 2
	this.manageComment = function(i, type) {
			SezWho.Utils.log("info" ,"SezWho.Utils.CommentFilterProcessing.toggleComment called with i = "+i);
			var hideCommCell = document.getElementById("sz_comment_collapse_divC:"+i);
			var commDiv = document.getElementById("sz_comment_body:"+i);
			if (commDiv) {
					if (type == 0) {
							commDiv.style.display = "none" ;
							commDiv.style.visibility = "hidden" ;
							hideCommCell.style.display = "inline" ;
							hideCommCell.style.visibility = "visible" ;

					}
					else if (type == 1) {
							commDiv.style.display = "inline" ;
							commDiv.style.visibility = "visible" ;
							hideCommCell.style.display = "none" ;
							hideCommCell.style.visibility = "hidden" ;
					}
			}
	};

	this.cpshowFilterOptions = function(list) {
			SezWho.Utils.log("info" ,"SezWho.Utils.CommentFilterProcessing.cpshowFilterOptions called with list = "+list);
			SezWho.Utils.CommentFilterProcessing.filterCommentsByScore(list.options[list.selectedIndex].value);
	};

	this.filterCommentsByScore = function(score) {
			SezWho.Utils.log("info" ,"SezWho.Utils.CommentFilterProcessing.filterCommentsByScore called with score = "+score);
			var objectCount = sz_comment_config_params.comment_number ;
			for (var co2 = 0 ; co2 < objectCount; co2++) {
					if(score==0 || sz_comment_config_params.sz_comment_data[co2].comment_score >= score)
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,1);
					else
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,0);
			}
			sz_comment_config_params.sortOrder = score;
	};

	this.filterCommentsByCommentAuthor = function(comment_author_name) {
		SezWho.Utils.log("info" ,"SezWho.Utils.CommentFilterProcessing.filterCommentsByCommentAuthor called with score = "+comment_author_name);
		var objectCount = sz_comment_config_params.comment_number ;
		for (var co2 = 0 ; co2 < objectCount; co2++) {
			if(sz_comment_config_params.sz_comment_data[co2].comment_author == comment_author_name)
				SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,1);
			else
				SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,0);
		}
		sz_comment_config_params.sortOrder = comment_author_name;
	};

	this.filterCommentsByNone = function(val) {
		SezWho.Utils.log("info" ,"SezWho.Utils.CommentFilterProcessing.filterCommentsByNone called with score = "+val);
		var objectCount = sz_comment_config_params.comment_number ;
		for (var co2 = 0 ; co2 < objectCount; co2++) {                        
			SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,1);                        
		}
	};

	this.filterCommentsByDate = function(dateVal) {
		SezWho.Utils.log("info" ,"SezWho.Utils.CommentFilterProcessing.filterCommentsByDate called with score = "+dateVal);
		var objectCount = sz_comment_config_params.comment_number ;
		for (var co2 = 0 ; co2 < objectCount; co2++) {
			if('0' == dateVal)
				if(SezWho.Utils.CommentFilterProcessing.commentDateDiff(sz_comment_config_params.sz_comment_data[co2].creation_date) == '0')
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,1);
				else
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,0);
			else if ('1' == dateVal)
				if(SezWho.Utils.CommentFilterProcessing.commentDateDiff(sz_comment_config_params.sz_comment_data[co2].creation_date) <= '1')
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,1);
				else
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,0);
			else if ('2' == dateVal)
				if(SezWho.Utils.CommentFilterProcessing.commentDateDiff(sz_comment_config_params.sz_comment_data[co2].creation_date) <= '6')
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,1);
				else
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,0);
			else if ('3' == dateVal)
				if(SezWho.Utils.CommentFilterProcessing.commentDateDiff(sz_comment_config_params.sz_comment_data[co2].creation_date) <= '30')
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,1);
				else
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,0);
			else if ('4' == dateVal)
				if(SezWho.Utils.CommentFilterProcessing.commentDateDiff(sz_comment_config_params.sz_comment_data[co2].creation_date) <= '60')
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,1);
				else
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,0);
			else if ('4' == dateVal)
				if(SezWho.Utils.CommentFilterProcessing.commentDateDiff(sz_comment_config_params.sz_comment_data[co2].creation_date) <= '180')
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,1);
				else
					SezWho.Utils.CommentFilterProcessing.manageComment(sz_comment_config_params.sz_comment_data[co2].comment_id,0);

		}
		sz_comment_config_params.sortOrder = dateVal;
	};
	
	this.commentDateDiff = function(commDate) {
		var commdateYear=  commDate.substring(0,4);  
		var commdateMonth=  commDate.substring(5,7) - 1; 
		var commdateDate=  commDate.substring(8,10);

		var commdateformat=new Date(commdateYear, commdateMonth, commdateDate); //Month is 0-11 in JS
		var curdate = new Date();
		var one_day=1000*60*60*24;

		var dayDiff = Math.floor((curdate.getTime() - commdateformat.getTime())/(one_day));
		return dayDiff;
	};

};
/*  end SezWho.Utils.CommentFilterProcessing */
function populate_comment_star_rating(comment_score, comment_id){
	var comment_ratings_images = SezWho.Utils.get_star_rating(comment_score, "comment");
	document.getElementById("yk_comment-"+comment_id+"-commentscore").innerHTML = comment_ratings_images;
};

SezWho.Utils.ScoreDisplay = new function (){
    this.abPos=function(o) {
	var z={X:0,Y:0};
	while(o!=null) {
		z.X+=o.offsetLeft;
		z.Y+=o.offsetTop;
		o=o.offsetParent;
	}
	return(z);
    };
    this.getLiClass = function(id) {
	var a = id.split(":");
	var lid = "";
	if (a[0] == "cpEmbedPostScore"){
	    lid = "cpEmbedPostScoreLi";
        } else {
	    lid = "cpEmbedCommScoreLi";
	}
	return lid;
    };
    this.getLi = function(id) {
	var a = id.split(":");
	var lid = "";
	if (a[0] == "cpEmbedPostScore"){
	    lid = "cpEmbedPostScoreLi:"+a[1];
        } else {
	    lid = "cpEmbedCommScoreLi:"+a[1];
	}
	return document.getElementById(lid);
    };
    this.getScoreSpan = function(id) {
	var a = id.split(":");
	var lid = "";
	if (a[0] == "cpEmbedPostScore"){
	    lid = "cpEmbedPostScoreSpan:"+a[1];
        } else {
	    lid = "cpEmbedCommScoreSpan:"+a[1];
	}
	return document.getElementById(lid);
    };
    this.getCountSpan = function(id) {
	var a = id.split(":");
	var lid = "";
	if (a[0] == "cpEmbedPostScore"){
	    lid = "cpEmbedPostCountSpan:"+a[1];
        } else {
	    lid = "cpEmbedCommCountSpan:"+a[1];
	}
	return document.getElementById(lid);
    };
    this.getClass = function(id) {
	var a = id.split(":");
	var lid = "";
	if (a[0] == "cpEmbedPostScore"){
	    lid = "cpEmbedPostScoreUl";
        } else {
	    lid = "cpEmbedCommScoreUl";
	}
	return lid;
    };
    this.getDisplay = function(s) {
	var a = {1:"it's bad!", 2:"it's so so!", 3:"it's good!", 4:"it's great!", 5:"it rocks!"};
	if (typeof(sz_ratings_display.ratings_text) == "undefined")
		return a[s];
	else
		return sz_ratings_display.ratings_text[s];
    };

    /* Mouse Events */

    this.cur=function(e) {
	o = sz_cp_proto.element(e);
	if (o.id != sz_utils_display_id) sz_utils_display_stop=1;
	if(sz_utils_display_stop) {
		sz_utils_display_stop=0;
		if (o.tagName == "LI") o = o.parentNode;
		sz_utils_display_id=o.id;
		sz_cp_proto.observe(document,"mousemove", SezWho.Utils.ScoreDisplay.mousemove);
		if (sz_ratings_display.hover_handle_type == "width") SezWho.Utils.ScoreDisplay.getLi(sz_utils_display_id).className=SezWho.Utils.ScoreDisplay.getLiClass(sz_utils_display_id)+"Hover";
		//SezWho.Utils.ScoreDisplay.getCountSpan(sz_utils_display_id).style.visibility = 'hidden';
    	}
    };
    this.mousemove=function(e) {
	    var n=sz_utils_display_id;
	    var p=SezWho.Utils.ScoreDisplay.abPos(document.getElementById(n));
	    var x={X:sz_cp_proto.pointerX(e),Y:sz_cp_proto.pointerY(e)};
	    var dimensions = sz_cp_proto.getDimensions(n);
	    var oX=x.X-p.X;
	    var oY=x.Y-p.Y;

	    if(oX<1 || oX>dimensions.width || oY<0 || oY>dimensions.height) {
		SezWho.Utils.ScoreDisplay.revert();
	    } else {
		var s = Math.round((oX/dimensions.width*sz_ratings_display.number_values)+0.5);
		s=s>sz_ratings_display.number_values?sz_ratings_display.number_values:s;
		s=s<1?1:s;
		if (sz_ratings_display.hover_handle_type == "width") {
			SezWho.Utils.ScoreDisplay.getLi(sz_utils_display_id).className=SezWho.Utils.ScoreDisplay.getLiClass(sz_utils_display_id)+"Hover";
			SezWho.Utils.ScoreDisplay.getLi(n).style.width=oX+'px';
		}
		else if (sz_ratings_display.hover_handle_type == "value"){
			if (sz_ratings_display.ul_hover_ratings_style)
				document.getElementById(n).className=SezWho.Utils.ScoreDisplay.getClass(n)+"Hover"+s;
			if (sz_ratings_display.li_hover_ratings_style)
				SezWho.Utils.ScoreDisplay.getLi(sz_utils_display_id).className=SezWho.Utils.ScoreDisplay.getLiClass(sz_utils_display_id)+"Hover"+s;
		}
		SezWho.Utils.ScoreDisplay.getScoreSpan(n).title=sz_ratings_display.ratings_value[s];
		SezWho.Utils.ScoreDisplay.getScoreSpan(n).innerHTML=SezWho.Utils.ScoreDisplay.getDisplay(s);
	    }
	};
    this.update=function(e) {
	var n=sz_utils_display_id;
	var v=parseInt(SezWho.Utils.ScoreDisplay.getScoreSpan(n).title);
	sz_utils_display_stop=0;
	sz_cp_proto.stopObserving (document, "mousemove", SezWho.Utils.ScoreDisplay.mousemove);
	SezWho.Utils.szHandleRatingButtonClicks(e, v, document.getElementById(n));
    };
    this.setWait=function(id) { //To get of wait state call setScore function
	var o = document.getElementById(id);
	var oli= SezWho.Utils.ScoreDisplay.getLi(id);
	sz_utils_display_id = id;
	if (o && sz_ratings_display.ul_style_in_process_display != "") o.className = sz_ratings_display.ul_style_in_process_display;
	if (oli && sz_ratings_display.li_style_in_process_display != "") oli.className= sz_ratings_display.li_style_in_process_display;
	if (sz_ratings_display.hover_handle_type == "width") oli.style.width='0px';
	SezWho.Utils.ScoreDisplay.getScoreSpan(id).innerHTML = "Processing";
	//SezWho.Utils.ScoreDisplay.getCountSpan(sz_utils_display_id).style.visibility = 'hidden';
	sz_utils_display_wait=1;
    };
    this.setScore=function(n, score, count) {
        var v=Math.ceil(parseFloat(score)*10/2)/10;
	var oli = SezWho.Utils.ScoreDisplay.getLi(n);
	var o = document.getElementById(n);
	if (oli && o){
	oli.title=v;
	o.className = SezWho.Utils.ScoreDisplay.getClass(n);
	}
	SezWho.Utils.ScoreDisplay.getScoreSpan(n).title = v;
	sz_utils_display_stop=1;
	sz_utils_display_wait=0;
	if (count){
		var cid=SezWho.Utils.ScoreDisplay.getCountSpan(n);
		cid.title = count;
		if (count == "1")
		cid.innerHTML = " ("+count+" person)";
		else
		cid.innerHTML = " ("+count+" people)";
	}
	SezWho.Utils.ScoreDisplay.revert();
    };
    this.revert=function() {
	if (sz_utils_display_wait) return;
	var n=sz_utils_display_id;
	var oli = SezWho.Utils.ScoreDisplay.getLi(n);
	var o = document.getElementById(n);
	var v;
	if (o && oli){ 
		var dimensions = sz_cp_proto.getDimensions(n);
		v=parseFloat(SezWho.Utils.ScoreDisplay.getLi(n).title);

		if (sz_ratings_display.hover_handle_type == "width")
			oli.style.width=Math.round(v*dimensions.width/sz_ratings_display.number_values)+'px';
		sz_cp_proto.stopObserving (document, "mousemove", SezWho.Utils.ScoreDisplay.mousemove);
		SezWho.Utils.ScoreDisplay.getLi(n).className=SezWho.Utils.ScoreDisplay.getLiClass(n);
		o.className=SezWho.Utils.ScoreDisplay.getClass(n);
	}
	else{
		v=parseFloat(SezWho.Utils.ScoreDisplay.getScoreSpan(n).title);
	}
        SezWho.Utils.ScoreDisplay.getScoreSpan(n).innerHTML=(v>0?v:'');
	sz_utils_display_stop=1;
    };
};


var sz_utils_display_stop = 1;
var sz_utils_display_wait = 0;
var sz_utils_display_id = "";

sz_cp_proto.observe(window, "load", SezWho.Utils.callJSFramework , false );
/*@cc_on @*/
/*@if (@_win32)
document.write("<script id=__sz_ie_onload defer src=javascript:void(0)><\/script>");
var script = document.getElementById("__sz_ie_onload");
script.onreadystatechange = function() {
    if (this.readyState == "complete") {
        SezWho.Utils.callJSFramework(); // call the onload handler
    }
};
try {
  document.execCommand("BackgroundImageCache", false, true);
} catch(err) {}
/*@end @*/


/* prevent memory leaks in IE */
if (SezWho.Utils.isIE()) {
	sz_cp_proto.observe(window, 'unload', sz_cp_proto.unloadCache, false);
}

SezWho.BlogPlatformCallbackJS = new function() {
        this.show_up_level = show_up_level ;
        this.header_up_level = header_up_level ;
        this.comment_id_prefix = "sz_profile_link:" ;

        this.get_comment_node = function (comment_id) {
                var comment_hiddentag = document.getElementById(this.comment_id_prefix+SezWho.BlogPlatformCallbackJS.getIndexFromID(comment_id));
                var comment_header = comment_hiddentag;
                var comment_header_obj = null ;
                if (sz_is_auto_layout && comment_header){
                        comment_header_obj = (comment_header != null) ? comment_header.parentNode : null ;
                        var ID_Match = new RegExp('\\b'+comment_id+'\\b');
                        while(comment_header_obj != null && comment_header_obj.tagName != null) {
                                var iterationTagName = comment_header_obj.tagName.toLowerCase() ;
                                if (iterationTagName == 'body') {
                                        comment_header_obj = null;
                                        // no "li" found up till the "body" tag, hence breaking
                                        break ;
                                } else {
                                        if (ID_Match.test(comment_header_obj.id))
                                                if (iterationTagName == 'div' || iterationTagName == 'li' || iterationTagName == 'blockquote' || iterationTagName == 'dd' || iterationTagName == 'dt' || iterationTagName == 'tr' || iterationTagName == 'td' || iterationTagName == 'table') break ;
                                }
                                comment_header_obj = comment_header_obj.parentNode ;
                        }
                }
                return comment_header_obj;
        };

        this.getIndexFromID = function(id) {
                if (window.sz_global_config_params == null || window.sz_comment_config_params == null || window.sz_comment_config_params.comment_number == null)
                        return -1;
                for (var co2 = 0 ; co2 < sz_comment_config_params.comment_number; co2++)
                        if (sz_comment_config_params.sz_comment_data[co2].comment_id == id) return co2;

                return -1;
        };

        // gets called if the "commentblockhiddentag" tag is not found,
	// try and navigate up from the 1st comment's hidden image
        this.get_top_of_first_comment = function (comment_id) {
                var top_of_comment = SezWho.BlogPlatformCallbackJS.get_comment_node(comment_id);
                if (sz_is_auto_layout && top_of_comment) return top_of_comment;
                if (show_up_level == header_up_level) return SezWho.BlogPlatformCallbackJS.get_comment_node_by_calc(comment_id, 0);

                var comment_hiddentag = document.getElementById(this.comment_id_prefix+SezWho.BlogPlatformCallbackJS.getIndexFromID(comment_id));
                top_of_comment = comment_hiddentag ;
                if (top_of_comment) {
                        for (i = 0 ; i < this.show_up_level ; i++) {
                                top_of_comment = top_of_comment.parentNode ;
                        }
                }
                return top_of_comment;
        };

        // gets called if the "get_comment_node" function returns null
        // Try and see if we can locate the node manually here.
        this.get_comment_node_by_calc = function (comment_id, isCompositeCreate) {
                var comment_header = document.getElementById(this.comment_id_prefix+SezWho.BlogPlatformCallbackJS.getIndexFromID(comment_id));
                var comment_header_obj = null;
                if (comment_header) {
                        // initialize comment_header_obj to the initial comment_header so as to proceed with the
                        // traversal as per the DB levels
                        comment_header_obj = comment_header ;
                        for (i = 0 ; i < this.header_up_level ; i++) {
                                comment_header_obj = comment_header_obj.parentNode ;
                        }
                        if (!sz_high_sibling && !sz_low_sibling) return comment_header_obj;
                        if (!comment_header_obj) return comment_header_obj;
                        // In case there are not multiple top level siblings in a comment block, return the current node
                        // If there are multiple nodes, we create a div and add all those nodes to one node to help with
                        // opening and collapsing of the comments. We return the newly created node.
                        var lc = sz_high_sibling;
                        while (lc){
                                comment_header_obj = comment_header_obj.previousSibling;
                                if (comment_header_obj.tagName)lc-- ;
                        }
                        if (!isCompositeCreate) return comment_header_obj;
                        var commentDiv = document.createElement("div") ;
                        var tmp;
                        if (comment_header_obj && comment_header_obj.parentNode) comment_header_obj.parentNode.insertBefore(commentDiv, comment_header_obj);

                        tmp = comment_header_obj.nextSibling;
                        commentDiv.appendChild(comment_header_obj);
                        comment_header_obj = tmp;

                        lc = sz_high_sibling + sz_low_sibling;
                        while (lc){
                                if (!comment_header_obj) break;
                                if (comment_header_obj.tagName)lc-- ;
                                if (comment_header_obj.nextSibling) tmp = comment_header_obj.nextSibling;
                                commentDiv.appendChild(comment_header_obj) ;
                                comment_header_obj = tmp;
                        }
                        return commentDiv;
                }

                return comment_header_obj ;
        };
	this.get_platform= function (){return "wordpress";};
	this.get_comment_bottom_block = function (comment_id) {};
	this.get_comment_author_name = function (comment_id, url) {};
	this.get_blog_data = function() {};
        this.get_post_node = function (post_id) {};
};



