$("document").ready(function()
{

	$(".import.preview.window").build(function()
	{

		this.in("select").change(function()
		{
			var $this = $(this);
			var $selected = $this.find(":selected");
			$this.css("background",  $selected.css("background"));
			$this.css("color",       $selected.css("color"));
		}).change();

	});

});
