var ca_popup_id = -1;
var ca_popup_counter = 0;
var ca_popup_list = new Array();
var ca_forums_list = new Array();
var ca_item;
var ca_item2;
var ca_list;
var ca_list2;
var ca_code;
var ca_timer;
var ca_left;
var ca_exp = new Date();
var ca_qr_init = false,
    cq_qr = false,
    ca_qr_url = false;

ca_exp.setTime(ca_exp.getTime() + (90*24*60*60*1000));
onload_functions[onload_functions.length] = 'ca_popup_init();';
onload_functions[onload_functions.length] = 'ca_forums_init();';
onload_functions[onload_functions.length] = 'ca_resize_images();';

// cookies
function ca_cookie_set(name, value) 
{
	var argv = ca_cookie_set.arguments;
	var argc = ca_cookie_set.arguments.length;
	var expires = (argc > 2) ? argv[2] : ca_exp;
	var path = (argc > 3) ? argv[3] : null;
	var domain = (argc > 4) ? argv[4] : null;
	var secure = (argc > 5) ? argv[5] : false;
	document.cookie = name + "=" + escape(value) +
		((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
		((path == null) ? "" : ("; path=" + path)) +
		((domain == null) ? "" : ("; domain=" + domain)) +
		((secure == true) ? "; secure" : "");
}

function ca_cookie_getval(offset) 
{
	var endstr = document.cookie.indexOf(";",offset);
	if (endstr == -1)
	{
		endstr = document.cookie.length;
	}
	return unescape(document.cookie.substring(offset, endstr));
}

function ca_cookie_get(name) 
{
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while (i < clen) 
	{
		var j = i + alen;
		if (document.cookie.substring(i, j) == arg)
			return ca_cookie_getval(j);
		i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0)
			break;
	} 
	return null;
}


// add new item to queue
function ca_popup_register(id)
{
    ca_popup_list[ca_popup_list.length] = id;
}

// initialize popups
function ca_popup_init()
{
    var id, i, j, do_minimize;
    // check if profiles need to be minimized
    do_minimize = false;
    if(ca_cookie_get('hideprofiles') == 1)
    {
        do_minimize = true;
    }
    // init popups
    for(i=0; i<ca_popup_list.length; i++)
    {
        id = ca_popup_list[i];
        ca_code = "ca_item.onmouseover = function() { ca_popup_show('" + id + "'); }; ca_item.onmouseout = function() { ca_popup_hide('" + id + "'); }; ";
        ca_left = -1;
        // find link
        ca_item = document.getElementById('link' + id);
        if(ca_item)
        {
            eval(ca_code);
            if(ca_item.offsetLeft)
            {
                ca_left = ca_item.offsetLeft;
            }
        }
        // find items
        ca_item = document.getElementById('popup' + id);
        if(ca_item)
        {
            if(ca_left > 0)
            {
                ca_left = ca_left - ca_item.offsetLeft;
                ca_item.style.marginLeft = ca_left + 'px';
            }
            ca_list = ca_item.getElementsByTagName('a');
            for(j=0; j<ca_list.length; j++)
            {
                ca_item = ca_list[j];
                eval(ca_code);
            }
        }
        // minimize profile
        if(do_minimize)
        {
            ca_post_minimize(id);
        }
    }
}

// show popup event
function ca_popup_show(id)
{
    // check previous popup
    if(ca_popup_id != id)
    {
        ca_popup_do_hide(ca_popup_id);
        ca_popup_counter = 0;
        ca_popup_id = id;
    }
    // show popup
    ca_popup_counter ++;
    if(ca_popup_counter < 2)
    {
        ca_popup_do_show(id);
    }
}

// hide popup event
function ca_popup_hide(id)
{
    if(ca_popup_id == id)
    {
        ca_popup_counter --;
        if(ca_popup_counter < 1)
        {
            ca_popup_counter = 0;
            ca_popup_start_hide(id);
        }
    }
}

// show menu
function ca_popup_do_show(id)
{
    ca_item = document.getElementById('popup' + id);
    if(ca_item)
    {
        ca_list = ca_item.getElementsByTagName('ul');
        if(ca_list.length > 0)
        {
            ca_list[0].style.display = 'block';
        }
    }
}

// hide menu
function ca_popup_do_hide(id)
{
    if(id == -1)
    {
        return;
    }
    ca_item = document.getElementById('popup' + id);
    if(ca_item)
    {
        ca_list = ca_item.getElementsByTagName('ul');
        if(ca_list.length > 0)
        {
            ca_list[0].style.display = 'none';
        }
    }
}

// start timer
function ca_popup_start_hide(id)
{
    if(ca_timer)
    {
        clearTimeout(ca_timer);
    }
    ca_timer = setTimeout("ca_popup_end_hide('" + id + "')", 250);
}

// end timer
function ca_popup_end_hide(id)
{
    clearTimeout(ca_timer);
    if(ca_popup_counter > 0)
    {
        return;
    }
    if(ca_popup_id != id)
    {
        return;
    }
    ca_popup_do_hide(id);
}

// parse forums list
function ca_parse_forums()
{
    var i, j;
    // find all categories
    ca_list = document.getElementsByTagName('div');
    ca_item = false;
    for(i=0; i<ca_list.length; i++)
    {
        ca_item = ca_list[i];
        if(ca_item.className == 'forabg')
        {
            // check forums inside category
            ca_list2 = ca_item.getElementsByTagName('li');
            for(j=0; j<ca_list2.length; j++)
            {
                ca_item2 = ca_list2[j];
                if(ca_item2.className == 'row row-new')
                {
                    // found unread forum
                    ca_item.className = 'forabg block-new';
                }
            }
        }
    }
}

// select code
function ca_select_code(a)
{
	// Get ID of code block
	var e = a.parentNode.parentNode.parentNode.getElementsByTagName('CODE')[0];

	// Not IE
	if (window.getSelection)
	{
		var s = window.getSelection();
		// Safari
		if (s.setBaseAndExtent)
		{
			s.setBaseAndExtent(e, 0, e, e.innerText.length - 1);
		}
		// Firefox and Opera
		else
		{
			var r = document.createRange();
			r.selectNodeContents(e);
			s.removeAllRanges();
			s.addRange(r);
		}
	}
	// Some older browsers
	else if (document.getSelection)
	{
		var s = document.getSelection();
		var r = document.createRange();
		r.selectNodeContents(e);
		s.removeAllRanges();
		s.addRange(r);
	}
	// IE
	else if (document.selection)
	{
		var r = document.body.createTextRange();
		r.moveToElementText(e);
		r.select();
	}
}


// expand code
function ca_expand_code(a)
{
	// Get ID of code block
	var e = a.parentNode.parentNode.parentNode.getElementsByTagName('CODE')[0];
	if(e)
	{
	    if(e.style.overflow == 'visible')
	    {
	        e.style.maxHeight = '200px';
	        e.style.overflow = 'auto';
	    }
	    else
	    {
	        e.style.maxHeight = 'none';
	        e.style.overflow = 'visible';
	    }
	}
}


// expand forum
function ca_expand_forum(a, id)
{
	// Find parent block
	var e = a.parentNode.parentNode.parentNode.parentNode.parentNode.getElementsByTagName('UL')[1];
	var expanded = 1;
	if(e)
	{
	    if(e.style.display == 'none')
	    {
	        e.style.display = '';
        	var expanded = 2;
	    }
	    else
	    {
	        e.style.display = 'none';
	    }
	    if(id)
	    {
	        ca_cookie_set('expand' + id, expanded);
        }
	}
}

// add new item to queue
function ca_forum_register(id)
{
    ca_forums_list[ca_forums_list.length] = id;
}

// expand forums
function ca_forums_init()
{
    var id, i, j, do_minimize;
    for(i=0; i<ca_forums_list.length; i++)
    {
        id = ca_forums_list[i];
        // find item, expand block
        ca_item = document.getElementById('forumblock' + id);
        if(ca_item)
        {
            if(ca_cookie_get('expand' + id) == 1)
            {
                ca_expand_forum(ca_item, 0);
            }
        }
    }
}

// minimize/maximize post
function ca_post_minimize(num)
{
    ca_item = document.getElementById('profilediv' + num);
    ca_item2 = document.getElementById('body' + num);
    if(ca_item && ca_item2)
    {
        ca_item.style.display = 'none';
        ca_item2.className = 'postbody post-hidden';
        ca_item = document.getElementById('maximize' + num);
        if(ca_item)
        {
            ca_item.style.display = '';
        }
        ca_item = document.getElementById('author' + num);
        if(ca_item)
        {
            ca_item.style.display = '';
        }
    }
}

function ca_post_maximize(num)
{
    ca_item = document.getElementById('profilediv' + num);
    ca_item2 = document.getElementById('body' + num);
    if(ca_item && ca_item2)
    {
        ca_item.style.display = '';
        ca_item2.className = 'postbody';
        ca_item = document.getElementById('maximize' + num);
        if(ca_item)
        {
            ca_item.style.display = 'none';
        }
        ca_item = document.getElementById('author' + num);
        if(ca_item)
        {
            ca_item.style.display = 'none';
        }
    }
}

function ca_post_minimize_all()
{
    var id, i;
    for(i=0; i<ca_popup_list.length; i++)
    {
        id = ca_popup_list[i];
        ca_post_minimize(id);
    }
    ca_cookie_set('hideprofiles', 1);
}

function ca_post_maximize_all()
{
    var id, i;
    for(i=0; i<ca_popup_list.length; i++)
    {
        id = ca_popup_list[i];
        ca_post_maximize(id);
    }
    ca_cookie_set('hideprofiles', 0);
}

// resize images
function ca_resize_images()
{
    var i, limit, diff;
    limit = 600;
    diff = 200;
    ca_item = document.getElementById('page-body');
    if(ca_item && ca_item.clientWidth)
    {
        limit = ca_item.clientWidth - diff;
    }
    if(limit < 500)
    {
        limit = 500;
    }
    if(document.body.clientWidth && document.body.clientWidth < (limit + diff) && document.body.clientWidth > 800)
    {
        limit = document.body.clientWidth - diff;
    }
    else if(window.innerWidth && window.innerWidth < (limit + diff) && window.innerWidth > 800)
    {
        limit = window.innerWidth - diff;
    }
    if(ca_main_width && ca_main_width.indexOf('%') == -1)
    {
        ca_main_width.replace(/px/, '');
        if(ca_main_width > 0)
        {
            limit = ca_main_width - diff;
        }
    }
    if(ca_item)
    {
        ca_list = ca_item.getElementsByTagName('img');
    }
    else
    {
        ca_list = document.getElementsByTagName('img');
    }
    for(i=0; i<ca_list.length; i++)
    {
        ca_item = ca_list[i];
        if(ca_item.width > limit)
        {
            if(document.all) 
            { 
                ca_item.style.cursor = 'hand'; 
            }
            else
            { 
                ca_item.style.cursor = 'pointer'; 
            }
            ca_item.style.width = (limit - 50) + 'px';
            ca_item.onclick = function() { 
                window.open(this.src, 'image', 'width=700,height=500,resizable=1,scrollbars=1');
            }
        }
    }
}

function ca_init_qr(url)
{
    ca_qr_src = url;
    ca_qr = new Image();
    ca_qr.onload = function() { ca_loaded_qr(false); }
    ca_qr.src = url;
}

// initialize quick reply button
function ca_loaded_qr(reload)
{
    if(!ca_qr.complete || !ca_qr.width || !ca_qr.height)
    {
        // image wasn't loaded yet
        if(!reload)
        {
            setTimeout("ca_loaded_qr(true)", 250);
        }
        return;
    }
    // image is ready
    ca_item = document.getElementById('viewtopic-buttons');
    ca_item.innerHTML = ca_item.innerHTML + '<div class="reply-icon" id="ca-qr" style="width: ' + Math.round(ca_qr.width) + 'px"><a href="javascript:void(0);" onclick="hide_qr(); return false;" title=""><span style="background-image: url(\'' + ca_qr_src + '\');"></span></a></div>';
    document.getElementById('qr_showeditor_div').style.display = 'none';
    ca_qr_init = true;
}