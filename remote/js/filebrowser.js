var box = null;

function delcont()
{
	document.newfform.foldername.value = "";
}

function hidebox( e )
{
	if(box != null)
		box.style.display = 'none';
}

function invisible()
{
	// var dir;
	// var links;
	// var i;
	//
	// dir = document.getElementById('folder');

	// if(dir != null)
	// {
	// // 	// List view
	// 	links = dir.getElementsByTagName('a');
	// 	for(i = 0; i < links.length; i++)
	// 	{
	// 		if(links[i].className == 'fbcut')
	// 			links[i].style.display = 'none';
	// 		if(links[i].className == 'fbactions')
	// 			links[i].style.display = 'inline';
	//
	// 	}
	// }

	document.onmouseup = hidebox;
}

function actions( file , type , ev)
{
	var i;


	if(box == null)
	{
		box = document.createElement('DIV');
		document.getElementById('main').insertBefore(box, document.getElementById('content'));
		box.id = "actionsbox";
	}
	else
		box.style.display = 'block';

	box.style.top = ev.pageY + "px";

	if(browsetype == 'list')
	{
		box.style.left = 10;
		box.innerHTML = '';
		box.innerHTML += "<a href=\"filedetails.php?action=archtar&amp;file="    + file + "&amp;" + sid + "\" title=\"" + lngtar        + "\">" + lngtar        + "</a>";
		box.innerHTML += "<a href=\"filedetails.php?action=archrar&amp;file="    + file + "&amp;" + sid + "\" title=\"" + lngrar        + "\">" + lngrar        + "</a>";
		box.innerHTML += "<a href=\"filedetails.php?action=archzip&amp;file="    + file + "&amp;" + sid + "\" title=\"" + lngzip        + "\">" + lngzip        + "</a>";
		box.innerHTML += "<a href=\"filedetails.php?action=archtargz&amp;file="  + file + "&amp;" + sid + "\" title=\"" + lngtargz      + "\">" + lngtargz      + "</a>";
		box.innerHTML += "<a href=\"filedetails.php?action=download&amp;file="   + file + "&amp;" + sid + "\" title=\"" + lngdownload   + "\">" + lngdownload   + "</a>";
		box.innerHTML += "<a href=\"filedetails.php?action=downrar&amp;file="    + file + "&amp;" + sid + "\" title=\"" + lngdownrar    + "\">" + lngdownrar    + "</a>";
		box.innerHTML += "<a href=\"filedetails.php?action=downzip&amp;file="    + file + "&amp;" + sid + "\" title=\"" + lngdownzip    + "\">" + lngdownzip    + "</a>";
		box.innerHTML += "<a href=\"filedetails.php?action=downtarbz2&amp;file=" + file + "&amp;" + sid + "\" title=\"" + lngdowntarbz2 + "\">" + lngdowntarbz2 + "</a>";
	}
	else
	{
		box.style.left = ev.pageX + "px";
		box.innerHTML = '';
		box.style.width = "200px";
		if(type == 'dir')
		{
			box.innerHTML += "<a href=\"filebrowser.php?change_dir="             + file + "&amp;" + sid + "\" title=\"" + lngopen         + "\">" + lngopen        + "</a>";
			box.innerHTML += "<a href=\"filedetails.php?dir="                    + file + "&amp;" + sid + "\" title=\"" + lngshowdetails  + "\" onclick=\"return popupfun( this );\">" + lngshowdetails + "</a>";
			box.innerHTML += "<a href=\"filebrowser.php?action=deldir&amp;dir="  + file + "&amp;" + sid + "\" title=\"" + lngdelete       + "\" onclick=\"return showConfirm( this, 'fdel');\">" + lngdelete      + "</a>";
			box.innerHTML += "<a href=\"filebrowser.php?action=copydir&amp;dir=" + file + "&amp;" + sid + "\" title=\"" + lngcopy         + "\">" + lngcopy        + "</a>";
			box.innerHTML += "<a href=\"filebrowser.php?action=cutdir&amp;dir="  + file + "&amp;" + sid + "\" title=\"" + lngcut          + "\">" + lngcut         + "</a>";
		}
		else
		{
			box.innerHTML += "<a href=\"filedetails.php?file="                     + file + "&amp;" + sid + "\" title=\"" + lngshowdetails  + "\" onclick=\"return popupfun( this );\" >" + lngshowdetails + "</a>";
			box.innerHTML += "<a href=\"filebrowser.php?action=delfile&amp;file="  + file + "&amp;" + sid + "\" title=\"" + lngdelete       + "\" onclick=\"return showConfirm( this, 'fdel' );\">" + lngdelete      + "</a>";
			box.innerHTML += "<a href=\"filebrowser.php?action=copyfile&amp;file=" + file + "&amp;" + sid + "\" title=\"" + lngcopy         + "\">" + lngcopy        + "</a>";
			box.innerHTML += "<a href=\"filebrowser.php?action=cutfile&amp;file="  + file + "&amp;" + sid + "\" title=\"" + lngcut          + "\">" + lngcut         + "</a>";
			box.innerHTML += "<a href=\"filedetails.php?action=download&amp;file=" + file + "&amp;" + sid + "\" title=\"" + lngdownload     + "\">" + lngdownload    + "</a>";
		}
	}

	return false;
}

function popupfun( link )
{
	var url = link.href;

	var width  = 850;
	var height = 680;
	var ptop  = window.innerHeight/2-height/2;
	var pleft = window.innerWidth/2-width/2;

	var myWindow = window.open(url, '', 'width=' + width + ',height=' + height + ', top=' + ptop + ', left=' + pleft + ', resizable,scrollbars');
	myWindow.focus();

	return false;
}

// function showConfirm( link, action )
// {
// 
// 	var text;
// 	
// 	if(action == "del")
// 		text = lngfbdelconf;
// 	
// 	if(!(con = document.getElementById('confirm')))
// 	{
// 		con = document.createElement('div');
// 		con.id = "confirm";
// 		con.className = "framebox";
// 		var body = document.getElementsByTagName('BODY');
// 		body[0].insertBefore( con, document.getElementById("main"));
// 	}
// 	else
// 		con.style.display = "block";
// 
// 	form  = '<input type="submit" value="' + lngyes + '" class="yes" onclick="location.href = \'' + link.href + '&confirm=true\';" />';
// 	form += '<input type="submit" value="' + lngno  + '" class="no" onclick="hideConfirm();" />';
// 	con.innerHTML = "<div><div><p>" + text + "</p><p id=\"confbuttons\">"+ form + "</p></div></div>";
// 
// 	return false;
// }
// 
// function hideConfirm()
// {
// 	if(con = document.getElementById('confirm'))
// 		con.style.display = "none";
// }
