
var timerlen = 10;    // do not disturb them
var slideAniLen = 300;  // do not disturb them

var timerID = new Array();
var startTime = new Array();
var obj = new Array();
var endHeight = new Array();
var moving = new Array();
var dir = new Array();

function slidedown(objname){
        if(moving[objname])
                return;

        if(document.getElementById(objname).style.display != "none")
                return; // cannot slide down something that is already visible

        moving[objname] = true;
        dir[objname] = "down";
        startslide(objname);

}

function slideup(objname){

        if(moving[objname])
                return;

        if(document.getElementById(objname).style.display == "none")
                return; // cannot slide up something that is already hidden

        moving[objname] = true;
        dir[objname] = "up";
        startslide(objname);
	
}

function startslide(objname){
        obj[objname] = document.getElementById(objname);	
	endHeight[objname] = getHeight(objname);
        startTime[objname] = (new Date()).getTime();
        if(dir[objname] == "down"){
                obj[objname].style.height = "1px";
        }      
	obj[objname].style.display = "block";
        timerID[objname] = setInterval('slidetick(\'' + objname + '\');',timerlen);
}

function slidetick(objname){
        var elapsed = (new Date()).getTime() - startTime[objname];

        if (elapsed > slideAniLen)
                endSlide(objname)
        else {
                var d =Math.round(elapsed / slideAniLen * endHeight[objname]);
                if(dir[objname] == "up")
                        d = endHeight[objname] - d;

                obj[objname].style.height = d + "px";
        }

        return;
}

function endSlide(objname){
        clearInterval(timerID[objname]);

        if(dir[objname] == "up")
                obj[objname].style.display = "none";

        obj[objname].style.height = endHeight[objname] + "px";

        delete(moving[objname]);
        delete(timerID[objname]);
        delete(startTime[objname]);
        delete(endHeight[objname]);
        delete(obj[objname]);
        delete(dir[objname]);

        return;
}

function insertAfter(targetElement) {
	var newElement = document.getElementById();
	var parent = targetElement.parentNode;
	if (parent.lastChild == targetElement) {
		parent.appendChild(newElement);
	} else {
	parent.insertBefore(newElement,targetElement.nextSibling);
	}
	slidedown('');
}

	var sz_fm = false;
	var sz_mvs = false;
	var sz_ci = false;
	var sz_cpi = false;
	// no frills JS code
	sz_add_comment = function(comment_id,id) {
		if(!sz_fm) { sz_fm = document.getElementById('sz_comment_form_inline'); }
		if(!sz_ci) { sz_ci = document.getElementById('comment_inline'); }
		if(!sz_cpi) { sz_cpi = document.getElementById('sz_comment_parent_inline'); }
		if(!sz_mvs) { sz_move_comment_subscription(); }
		
		if(sz_fm.style.display == 'block') {
			sz_fm.style.display = 'none';
			sz_check_comment_open(comment_id,id,sz_fm);
		}
		else { sz_move_comment_form(comment_id,id,sz_fm); }
	}
	
	sz_cancel_add = function() {
		slideup(sz_fm.id);
		//sz_fm.style.display = 'none';
		sz_ci.value = '';
		sz_cpi.value = '0';		
	}
	
	sz_check_comment_open = function(comment_id,id,elem) {
		if(elem.comment_open && elem.comment_open !== comment_id) { sz_move_comment_form(comment_id,id,elem); }
		else { elem.comment_open = ''; }
	}
	
	sz_move_comment_form = function(comment_id,id,elem) {
		sz_cpi.value = id;
		elem.comment_open = comment_id;
		document.getElementById(comment_id).appendChild(elem);

		slidedown(elem.id);
	}
	
	sz_submit_comment = function(smb,form) {
		// eventually do some minor validation here
		document.commentforminline.submit();
	}
	
	sz_move_comment_subscription = function() {
		var ss = document.getElementById('sz_comment_form').getElementsByTagName('p');
		var ss_clone = false;
		for(i=0;i<ss.length;i++) {
			if(ss[i].className == 'subscribe-to-comments') { ss_clone = ss[i].cloneNode(true); }
		}
		if(ss_clone !== false) { document.getElementById('commentforminline').appendChild(ss_clone); }
		sz_mvs = true; 
	}
	
	getHeight = function(element) {
                return getDimensions(element).height;
        }

	getDimensions = function (element_id) {
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
	}

