var open    = null;
var openct  = "";
var handler = null;

if(window.XMLHttpRequest)
	handler = new XMLHttpRequest();
else
	alert('No Ajax possible, XMLHttpRequest-Object could not be found');

function ajax_descr( id )
{
	if(handler != null)
	{
		//details.onreadystatechange = process_details( td );
		handler.onreadystatechange = function()
		{
			if(handler.readyState == 4)
				process_descr( );
		}
		handler.open("GET", "ajax/feeds.ajx.php?feedid=" + feedid + '&descrid=' + id + '&' + sid, true);
		handler.send(null);
	}
}

function process_descr( )
{
	handler.responseText;
	/**/
	if(handler.responseText.substr(0, 5) == 'ERROR')
	{
		alert(handler.responseText);
	}
	else
	{
		open.innerHTML = openct + handler.responseText;
	}
}

function getDescr( id, link )
{
	var td = link.parentNode;


	if(open)
	{
		open.innerHTML = openct;
		if(open == td)
		{
			open = null;
			return false;
		}
	}

	open = td;
	openct = td.innerHTML;

	td.innerHTML += '<br><img src="' + imagedir + 'loading.gif" alt="' + lngloading + '" />';
	ajax_descr( id , td );

	return false;
}

function invisible()
{
	document.getElementById('feedapply').style.display = 'none';
}

function changeFeed()
{
	document.getElementById('loading').style.display = 'inline';
	document.feedselect.submit();
}
