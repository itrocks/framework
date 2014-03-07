$("document").ready(function()
{
	window.zindex_counter = 0;

	$("body").build(function()
	{

		this.xtarget({
			url_append: "as_widget=1",
			success: function() { $(this).autofocus(); }
		});
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

		// checkboxes values are set on their hidden input
		this.in("input[type=checkbox]").change(function()
		{
			var $this = $(this);
			$this.prev().filter("input[type=hidden]").val($this.is(":checked") ? 1 : 0);
		});

		// apply readonly on checkboxes patch
		this.in("input[type=checkbox][readonly]").click(function(event)
		{
			console.log("prevent");
			event.preventDefault();
		});

	});

	// focus first form element
	$(this).autofocus();

});
