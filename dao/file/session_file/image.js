$(document).ready(function()
{

	//-------------------------------------------------------------------------------- img.zoom click
	$('body').build('click', 'img.zoom', function()
	{
		$(this).parent().remove();
	})

})
