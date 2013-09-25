$("document").ready(function()
{

	$("body").build(function()
	{

		this.xtarget({ url_append: "as_widget=1" });

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

		// fix a growing td height bug when contained anchor is empty
		this.in("td a:empty").html("&nbsp;");

	});

});
