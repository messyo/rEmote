var ajax = null;
var timer = null;

ajax_exec();
timer = window.setInterval("ajax_exec()", refreshinterval*1000);

if(window.XMLHttpRequest)
	ajax = new XMLHttpRequest();
else
	alert('No Ajax possible, XMLHttpRequest-Object could not be found');

function ajax_exec()
{
	if(ajax != null)
	{
		ajax.onreadystatechange = process_ajax();
		ajax.open("GET", "ajax/index.ajx.php?" + sid, true);
		ajax.send(null);
	}
}

function process_ajax()
{
	var text;

	if(ajax.readyState == 4)
	{
		text = ajax.responseText;
		/**/
		if(text.substr(0, 5) == 'ERROR')
		{
			alert(text);
			window.clearInterval(timer);
		}
		else
		{
			//JSON

			var x = eval("(" + text + ")");

			stats_refresh( x );
		}
	}
}


function stats_refresh( data )
{
	var field;

	document.title = data[0];

	document.getElementById('sidebarup').innerHTML = data[1];
	document.getElementById('sidebardown').innerHTML = data[2];
	document.getElementById('sidebardisk').innerHTML = data[3];
	document.getElementById('sidebardisk').innerHTML = data[3];
	
	if(data[4] != '')
	{
		var shouts = data[4].replace(/\\'/g,'\'').replace(/\\"/g,'"').replace(/\\0/g,'\0').replace(/\\\\/g,'\\');
		document.getElementById('shouts').innerHTML = shouts;
	}
	if(data[5] != '')
		document.getElementById('sidebarload').innerHTML = data[5];

	/* Start shifted by 5 as title and the 4 Sidebar-Values are sent at first */
	if(data.length < 5)
		return;

	var z;
	var found;
	var obj;
	var rows;

	for(y = 6; y < data.length; y = y + 3)
	{
		/*
		data[y] = Hash/"x".groupid   (x could not be contained in a hash)
		data[y + 1] = Field
		data[y + 2] = Content
      */

		found = false;

		if(data[y].substring(0, 5) == "group")
		{
			obj = document.getElementById(data[y]);
			rows = 1;
		}
		else
		{
			obj = document.getElementById("torrent"+data[y]);
			rows = trows;
		}
		for(z = 0; z < rows; z++)
		{
			field = obj.getElementsByClassName(data[y+1]);
			if(field.length == 0)
			{
				obj = obj.nextSibling;
				continue;
			}
			field[0].innerHTML = data[y + 2];
			found = true;
			break;
		}
	}
}
