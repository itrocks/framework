$("document").ready(function()
{

	$(".window.list").build(function()
	{

		$(this).find("table.list").droppable({
			accept:     ".property",
			hoverClass: "candrop",
			tolerance:  "touch",

			drop: function(event, ui)
			{
			},

			over: function(event, ui)
			{
				console.log($(this));
				ui.draggable.data("over_droppable", $(this));
			},

			out: function(event, ui)
			{
				ui.draggable.removeData("over_droppable");
			}

		});

	});

});
