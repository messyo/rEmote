function fade( obj, opacity )
{
	obj.style.opacity = opacity;
	obj.style.filter  = "alpha(opacity="+parseInt(opacity*100)+")";
}

function keyDownFun(e)
{
	if(!e)
		e = window.event;

	if(e.keyCode == 27)
		hideConfirm();
}

function showConfirm( link , action )
{
	var text;
	var confadd = '&confirm=true';
	var con, bgObj;
	
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
	
	if(!(bgObj = document.getElementById('bgObj')))
	{
		bgObj = document.createElement('div');
		bgObj.id = "bgObj";
		bgObj.className = "framebox";
		var body = document.getElementsByTagName('BODY');
		body[0].insertBefore( bgObj, document.getElementById("main"));
	}
	else
		bgObj.style.display = "block";

	form  = '<input type="submit" value="' + lngyes + '" class="yes" onclick="location.href = \'' + link.href + confadd + '\';" />';
	form += '<input type="submit" value="' + lngno  + '" class="no" onclick="hideConfirm();" />';
	con.innerHTML = "<div><div><p>" + text + "</p><p id=\"confbuttons\">"+ form + "</p></div></div>";
   con.style.zIndex = 999;
   bgObj.style.zIndex = 998;
   bgObj.style.backgroundColor = 'black';

	var max = 0.7;

	fade(bgObj, 0.0);
	setTimeout(function(){ fade(bgObj, max/12 *  1); }, 1000/12 *  1);
	setTimeout(function(){ fade(bgObj, max/12 *  2); }, 1000/12 *  2);
	setTimeout(function(){ fade(bgObj, max/12 *  3); }, 1000/12 *  3);
	setTimeout(function(){ fade(bgObj, max/12 *  4); }, 1000/12 *  4);
	setTimeout(function(){ fade(bgObj, max/12 *  5); }, 1000/12 *  5);
	setTimeout(function(){ fade(bgObj, max/12 *  6); }, 1000/12 *  6);
	setTimeout(function(){ fade(bgObj, max/12 *  7); }, 1000/12 *  7);
	setTimeout(function(){ fade(bgObj, max/12 *  8); }, 1000/12 *  8);
	setTimeout(function(){ fade(bgObj, max/12 *  9); }, 1000/12 *  9);
	setTimeout(function(){ fade(bgObj, max/12 * 10); }, 1000/12 * 10);
	setTimeout(function(){ fade(bgObj, max/12 * 11); }, 1000/12 * 11);
	setTimeout(function(){ fade(bgObj, max/12 * 12); }, 1000/12 * 12);

	return false;
}

function hideConfirm()
{
	var con, bgObj;
	
	if(con = document.getElementById('confirm'))
		con.style.display = "none";
	
	if(bgObj = document.getElementById('bgObj'))
		bgObj.style.display = "none";
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

window.addEventListener('load',     adaptWidth, false);
window.addEventListener('resize',   adaptWidth, false);
window.addEventListener('keypress', keyDownFun, false);
