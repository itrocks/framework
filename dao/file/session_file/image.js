$('document').ready(function()
{

	//-------------------------------------------------------------------------------- img.zoom click
	$('img.zoom').build(function()
	{
		this.click(function() {
			$(this).parent().remove();
		});
	});

});
