
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
   	setTimeout(function(){
				con.style.opacity = max
			}, count * 50);

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
