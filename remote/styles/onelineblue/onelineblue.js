
function showConfirm( link , action )
{
	var text;
	var confadd = '&confirm=true';
	
	if(action == "del")
		text = lngdelconfirm;
	else if(action == 'fdel')
		text = lngfbdelconf;
	
	if(!(con = document.getElementById('confirm')))
	{
		con = document.createElement('div');
		con.id = "confirm";
		con.className = "framebox";
		var body = document.getElementsByTagName('BODY');
		body[0].insertBefore( con, document.getElementById("main"));
	}
	else
		con.style.display = "block";

	form  = '<input type="submit" value="' + lngyes + '" class="yes" onclick="location.href = \'' + link.href + confadd + '\';" />';
	form += '<input type="submit" value="' + lngno  + '" class="no" onclick="hideConfirm();" />';
	con.innerHTML = "<div><div><p>" + text + "</p><p id=\"confbuttons\">"+ form + "</p></div></div>";
   
	for(var count = 1; count <= 10; count += 1)
   	setTimeout("con.style.backgroundImage = \"url(images/newblue/empty" + count + ".png)\";", count * 50);

	return false;
}

function hideConfirm()
{
	if(con = document.getElementById('confirm'))
	{
		con.style.backgroundImage = "none";
		con.style.display = "none";
	}
}

function adaptWidth()
{
	var tds = document.getElementById('torrenttable').getElementsByTagName('TD');
	for( var i = 0; i < tds.length; i++)
	{
   	if(tds[i].className == 'name')
		{
      	tds[i].getElementsByTagName('A')[0].style.width = "270px";
		}
	}
	
	var w = 0;
	for( var i = 0; i < tds.length; i++)
	{
   	if(tds[i].className == 'name')
		{
			if( w == 0)
				w = tds[i].offsetWidth + "px";
      	tds[i].getElementsByTagName('A')[0].style.width = w;
		}
	}

}

window.addEventListener('load',   adaptWidth, false);
window.addEventListener('resize', adaptWidth, false);
