$("document").ready(function()
{

	$(".import.preview.window").build(function()
	{

		//----------------------------------------------------- li.draggable(), .properties.droppable()
		this.in("div.class").each(function()
		{
			var $this = $(this);
			$this.find("li").draggable({
				containment: $this,
				opacity:.7,
				revert: function() { $(this).css({ left: 0, top: 0 }); }
			});
			$this.find(".properties").droppable({
				accept: $this.find("li"),
				drop: function(event, ui)
				{
					var $draggable = ui.draggable;
					var $droppable = $(this);
					var $input = $draggable.closest("div").find("input");
					var property_name = $draggable.attr("class").split(" ")[0];
					$droppable.css({ background: "none", border: "none" });
					var niouk = ("," + $input.val() + ",").replace("," + property_name + ",", ",");
					$input.val((niouk == ",") ? "" : niouk.substr(1, niouk.length - 2));
					$draggable.appendTo($droppable.find("ul"));
					$input = $($draggable.closest("div").find("input"));
					$input.val($input.val() + ($input.val() ? "," : "") + property_name);
				},
				over: function()
				{
					$(this).css({ "background": "#E0E0E0", "border": "1px solid darkgrey" });
				},
				out: function()
				{
					$(this).css({ "background": "white", "border": "1px solid white" });
				}
			});
		});

		//----------------------------------------------------------------------------- select.change()
		this.in("select").change(function()
		{
			var $this = $(this);
			var $selected = $this.find(":selected");
			$this.css("background",  $selected.css("background"));
			$this.css("color",       $selected.css("color"));
		}).change();

	});

});
