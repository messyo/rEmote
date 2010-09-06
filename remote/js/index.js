var newtr = null;
var details = null;
var hash = null;

if(window.XMLHttpRequest)
	details = new XMLHttpRequest();
else
	alert('No Ajax possible, XMLHttpRequest-Object could not be found');


function modmenu( mod )
{
	var c = new Array();

	for(var x = 0; x < 5; x++)
	{
		if(x == mod)
			c[x] = ' class="viewselon"';
		else
			c[x] = '';
	}
	var points  = '<li id="ajaxmodselect0"' + c[0] + '><a href="#" onclick="return modchange( 0 );">' + lngfiletree + '</a></li>';
		 points += '<li id="ajaxmodselect1"' + c[1] + '><a href="#" onclick="return modchange( 1 );">' + lngfilelist + '</a></li>';
		 points += '<li id="ajaxmodselect2"' + c[2] + '><a href="#" onclick="return modchange( 2 );">' + lnginfos    + '</a></li>';
		 points += '<li id="ajaxmodselect3"' + c[3] + '><a href="#" onclick="return modchange( 3 );">' + lngtracker  + '</a></li>';
		 points += '<li id="ajaxmodselect4"' + c[4] + '><a href="#" onclick="return modchange( 4 );">' + lngpeers    + '</a></li>';
		 //points += '<li id="ajaxmodclose"><a href="#" onclick="return modchange(-1 );">' + lngclose    + '</a></li>';

	return('<div id="ajaxmodmenu"><ul class="vtabs">' + points + '</ul></div>');
}

function ajax_details( mod, td )
{
	if(details != null)
	{
		//details.onreadystatechange = process_details( td );
		details.onreadystatechange = function()
		{
			if(details.readyState == 4)
				process_details( mod, td );
		}
		details.open("GET", "ajax/details.ajx.php?hash=" + hash + '&mod=' + mod + '&' + sid, true);
		details.send(null);
	}
}

function process_details( mod, td )
{
	details.responseText;
	/**/
	if(details.responseText.substr(0, 5) == 'ERROR')
	{
		alert(details.responseText);
	}
	else
	{
		td.innerHTML = modmenu(mod) + '<div id="ajaxmodcontent">' + details.responseText + '</div>';
		if(mod == 0)
		{
			if(!closeAll())
				window.setTimeout("closeAll()", 500);
		}
	}
}

function checkall( value )
{
	var boxes;

	boxes = document.controls.getElementsByTagName('input');

	for (var i = 0; i < boxes.length; i++)
	{
		if(boxes[i].type == 'checkbox')
			boxes[i].checked = value;
	}
}

function checkrange( value , offset, rangeend )
{
	var boxes;
	var x;

	x = 0;
	boxes = document.controls.getElementsByTagName('input');
	if(rangeend <= boxes.length)
	{
		for (var i = 0; i < boxes.length; i++)
		{
			if(boxes[i].type == 'checkbox')
			{
				if(x >= offset && x < rangeend)
					boxes[i].checked = value;
				x++;
			}
		}
	}
}

function invisible()
{
	document.getElementById('fsubmit').style.display = "none";
}

function filter( filterinput )
{
	var filter = filterinput.value.toLowerCase();
	var rows0 = document.getElementById('torrenttable').getElementsByClassName('row0');
	var name;
	var link;
	var rows;
	var x, y;
	var n;

	for(x = 0; x < rows0.length; x = x + trows)
	{
		rows = rows0[x].getElementsByClassName('name');
		if(rows.length == 0) // In the case an inline-detail is visible
		{
			rows0[x].style.display = rows0[x - 1].style.display;
      	x = x - trows + 1;
			continue;
		}
		link = rows[0].getElementsByTagName('a');
		name = link[0].textContent.toLowerCase();
		if(name.search(filter) != -1)
			n = "table-row";
		else
			n = "none";

		for(y = 0; y < trows; y++)
			rows0[x+y].style.display = n;
	}

	var rows0 = document.getElementById('torrenttable').getElementsByClassName('row1');

	for(x = 0; x < rows0.length; x = x + trows)
	{
		rows = rows0[x].getElementsByClassName('name');
		if(rows.length == 0)
		{
			rows0[x].style.display = rows0[x - 1].style.display;
      	x = x - trows + 1;
			continue;
		}
		link = rows[0].getElementsByTagName('a');
		name = link[0].textContent.toLowerCase();
		if(name.search(filter) != -1)
			n = "table-row";
		else
			n = "none";

		for(y = 0; y < trows; y++)
			rows0[x+y].style.display = n;
	}

}

function detailsclose()
{
	if(newtr)
	{
		table = document.getElementById("torrenttable").lastChild; // Get tbody
		table.removeChild(newtr);
		newtr = null;
		hash = null;
	}
}

function popupfun( link )
{
	var url = link.href;

	if(detailsstyle == 1)
	{
		var width  = 680;
		var height = 600;
		var ptop  = window.innerHeight/2-height/2;
		var pleft = window.innerWidth/2-width/2;

		var myWindow = window.open(url, '', 'width=' + width + ',height=' + height + ', top=' + ptop + ', left=' + pleft + ', resizable,scrollbars');
		myWindow.focus();

		return false;
	}
	else if(detailsstyle == 2)
	{
		var par = link.parentNode;


		while(par.tagName != 'TR')  // Find parenting TR
			par = par.parentNode;

		while(!par.id || par.id.substr(0, 7) != 'torrent') // Find first TR of Torrent
			par = par.previousSibling;


		for(var x = 1; x < trows; x++)   // Goto last TR of Torrent
			par = par.nextSibling;

		table = document.getElementById("torrenttable").lastChild; // Get tbody


		var params = url.split('?', '2');
		var parts  = params[1].split('&');
		var lr;
		var newhash;
		for(var x = 0; x < parts.length; x++)
		{
			lr = parts[x].split('=', 2);
			if(lr[0] != "hash")
				continue;
			newhash = lr[1];
			break;
		}

		if(newtr)
		{
			if(hash == newhash)
			{
				detailsclose();
				return false;
			}
			else
				detailsclose();
		}

		hash = newhash;

		newtr = document.createElement('TR');
		newtr.className = par.className;
		table.insertBefore( newtr, par.nextSibling);



		newtr.innerHTML = '<td colspan="' + numcolumns + '"><img src="' + imagedir + 'loading.gif" alt="' + lngloading + '" /></td>';
		var td = newtr.getElementsByTagName('TD');
		ajax_details(0, td[0]);

		return false;
	}
	else
	{
		return true;
	}
}

function modchange( mod )
{
	if( mod < 0)
	{
		detailsclose();
		return false;
	}
	var td = newtr.getElementsByTagName('TD');
	document.getElementById('ajaxmodcontent').innerHTML = '<div style="text-align: center; margin-top: 30px;"><img src="' + imagedir + 'loading.gif" alt="' + lngloading + '" /></div>';
	ajax_details(mod , td[0]);

	return false;
}

// function showConfirm( link , action)
// {
// 
// 	var text;
// 	
// 	if(action == "del")
// 		text = lngdelconfirm;
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
// 	form  = '<input type="submit" value="' + lngyes + '" class="yes" onclick="location.href = \'' + link.href + '\';" />';
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

function delConfirm()
{
	return confirm(lngdelconfirm);
}
