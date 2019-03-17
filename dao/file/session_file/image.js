$('document').ready(function()
{
	$('img.zoom').build(function()
	{
		this.inside('img.zoom').click(function() {
			$(this).parent().remove();
		});
	});
});
