function closeAll()
{
	var tree;
	var uls;
   var i;
	var imgs;

	tree = document.getElementById('tree');
	if(!tree)
		return false;
	uls  = tree.getElementsByTagName('ul');
	
	for( i = 0; i < uls.length; i++)
		uls[i].style.display = 'none';

	imgs = tree.getElementsByTagName('img');

	for( i = 0; i < imgs.length; i++)
		if(imgs[i].className == "plusminus")
			imgs[i].src = imagedir + 'plus.png';

	updateAll();
}

function branchopenclose(button, foldername)
{
	var branch;

	branch = document.getElementById(foldername);

	if(branch.style.display == 'none')
	{
   	branch.style.display = 'block';
		button.src = imagedir + 'minus.png';
	}
	else
	{
   	branch.style.display = 'none';
		button.src = imagedir + 'plus.png';
	}
}

function intval( check )
{
	if(check.checked)
		return 2;
	else
		return 0;
}

function updateTree( check )
{
// filecheck, foldercheck
	var tree;
	var a;
	var sum;
	var r;

	if(check.className == "filecheck")
		return intval( check );
	else
	{
   	tree = check.nextSibling.nextSibling.nextSibling.nextSibling;
		a = tree.firstChild;
		sum = updateTree(a.firstChild);
		while(a = a.nextSibling)
		{
      	r = updateTree( a.firstChild);
			if(sum != r)
				sum = 1;
		}
		
		if(sum == 2)
			check.checked = true;
		else
		{
      	check.checked = false;
			if(sum == 1)
				check.style.background = '#ccc';
			else
				check.style.background = 'none';
		}
		return sum;
	}
}

function updateAll()
{
	updateTree( document.getElementById('tree').firstChild );		
}

function markbranch( check )
{
	var checkboxes;
   var i;
	var parentcheck;


	checkboxes = document.getElementById(check.name).getElementsByTagName('input');

	for( i = 0; i < checkboxes.length; i++ )
		checkboxes[i].checked = check.checked;

	updateAll();
}
