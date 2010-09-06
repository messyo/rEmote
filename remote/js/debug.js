var d = null;
var b = null;

function debugwindow()
{
	d = document.createElement('DIV');
	b = document.getElementsByTagName('BODY');
	b[0].insertBefore(d, b[0].firstChild);
	d.style.width = "320px";
	d.style.height = "40px";
	d.style.position = "absolute";
	d.style.left     = "10px";
	d.style.top      = "10px";
	d.style.border   = "1px solid black";
	d.style.background = "white";
	d.style.padding    = "20px 0px 0px 20px";
	d.innerHTML = '<form action="' + document.URL + '" method="post"><input type="text" name="debug" size="40" />&nbsp;<input type="submit" value="Do!" /></form>';
}
