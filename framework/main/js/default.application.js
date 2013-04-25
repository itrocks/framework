$("document").ready(function()
{

	$("body").build(function()
	{

		window.zindex_counter = 0;

		this.xtarget({ url_append: "as_widget=1" });

		// messages is draggable
		this.in("#messages").draggable();

		// tab controls
		this.in(".tabber").tabber();

		// window objects brought to front
		this.in("div.window").mousedown(function()
		{
			$(this).css("z-index", ++zindex_counter);
		});

	});

});
