$("document").ready(function()
{
	window.zindex_counter = 0;

	$("body").build(function()
	{

		this.xtarget({
			url_append: "as_widget=1",
			success: function() { $(this).autofocus(); }
		});

		// messages is draggable
		this.in("#messages").draggable();

		// tab controls
		this.in(".tabber").tabber();

		// window objects brought to front
		window.zindex_counter = 0;
		this.in("div.window").mousedown(function()
		{
			$(this).css("z-index", ++window.zindex_counter);
		});

	});

	$(this).autofocus();

});
