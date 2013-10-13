$("document").ready(function()
{

	$("body").build(function()
	{

		// implicit ajax links
		this.xtarget({ url_append: "as_widget=1" });
		this.aform();

		// can enter tab characters into textarea
		this.in("textarea").presstab();

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

});
