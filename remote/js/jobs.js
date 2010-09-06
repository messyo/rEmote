var jobtimer = null;
var jobajax  = null;

jobtimer = window.setInterval("job_check()", 1000*10);

if(window.XMLHttpRequest)
	jobajax = new XMLHttpRequest();
else
	alert('No Ajax possible, XMLHttpRequest-Object could not be found');

function job_check()
{
	if(jobajax != null)
	{
		jobajax.onreadystatechange = process_job();
		jobajax.open("GET", "ajax/jobs.ajx.php?" + sid, true);
		jobajax.send(null);
	}
}

function process_job()
{
	var text;
   var num;

	if(jobajax.readyState == 4)
	{
		text = jobajax.responseText;
		/* No Comment */
		if(text.substr(0, 5) == 'ERROR')
		{
			alert(text);
				window.clearInterval(jobtimer);
		}
		else
		{
			num = parseInt(text.substr(0, 1));
			// No More Jobs Running
			if(num == 0)
				window.clearInterval(jobtimer);

			if(text.length > 1)
				alert(text.substring(1, text.length));
		}
	}
}
