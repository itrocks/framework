$("document").ready(function()
{

	$(".window.list").build(function()
	{

		var endDrop = function($this, event, ui)
		{
			var insert_after = $this.data("insert_after");
			if (insert_after != undefined) {
				$this.find("th:nth-child(" + insert_after + "),td:nth-child(" + insert_after + ")")
					.removeClass("insert_after");
				$this.removeData("insert_after");
			}
			ui.draggable.removeData("over_droppable");
		};

		$(this).find("table.list").droppable({
			accept:     ".property",
			tolerance:  "touch",

			drop: function(event, ui)
			{
				var $this = $(this);
				var insert_after = $this.data("insert_after");
				if (insert_after != undefined) {
					var $th = $this.find("tr:first th:nth-child(" +insert_after + ")");
					var $draggable = ui.draggable;
					console.log("add " + $draggable.attr("id") + " after " + $th.attr("id"));
					endDrop($this, event, ui);
				}
			},

			over: function(event, ui)
			{
				ui.draggable.data("over_droppable", $(this));
			},

			out: function(event, ui)
			{
				endDrop($(this), event, ui);
			}

		});

	});

});
