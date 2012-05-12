var ajax = null;
var timer = null;

if(window.XMLHttpRequest)
	ajax = new XMLHttpRequest();
else
	alert('No Ajax possible, XMLHttpRequest-Object could not be found');

function ajax_exec(sid, action, value, button, rep)
{
	if(ajax != null)
	{
		if(button != null)
		{
			var back = button.text
			button.text = rep;
		}
		ajax.onreadystatechange = function()
		{
			var text;

			if(ajax.readyState == 4)
			{
				//button.innerHtml = back;
				text = ajax.responseText;
				if(button != null)
					button.text = back;
				if(text.substr(0, 5) == 'ERROR')
				{
					alert(text);
					window.clearInterval(timer);
				}
				else
				{
					if(action == "which")
					{
						var arr = document.getElementsByTagName("input");
						for(var x = 0; x < arr.length; x++)
						{
							if(arr[x].name == value)
							{
								arr[x].value = text;
								break;
							}
						}
					}
					else if(action == 'info')
					{
						var cont = document.getElementById('notifycontainer')
						cont.textContent = text;
						cont.className = 'notify';
					}
				}
			}
		};
		ajax.open("GET", "ajax/settings.ajx.php?" + action + "=" + value + sid, true);
		ajax.send(null);
		return false;
	}
	else
		return true;

}
