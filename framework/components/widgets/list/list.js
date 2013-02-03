$("document").ready(function()
{

	$(".window.list").build(function()
	{

		// search objects
		this.in(".search input, .search textarea").keydown(function(event)
		{
			if (event.keyCode == 13) {
				$(this).closest("form").submit();
			}
		});

		// column select popup
		this.in(".column_select a").click(function(event)
		{
			var $this = $(this);
			var $div = $this.closest(".column_select").find("#column_select");
			if ($div.length) {
				if ($div.is(":visible")) {
					$div.hide();
				}
				else {
					$div.show();
				}
				event.stopImmediatePropagation();
				event.preventDefault();
			}
		});

		// property drop into column
		var end = function($this, event, ui)
		{
			var insert_after = $this.data("insert_after");
			if (insert_after != undefined) {
				$this.find("th:nth-child(" + insert_after + "),td:nth-child(" + insert_after + ")")
					.removeClass("insert_after");
				$this.removeData("insert_after");
			}
			ui.draggable.removeData("over_droppable");
		};

		this.in("table.list").droppable({

			accept:    ".property",
			tolerance: "touch",

			drop: function(event, ui)
			{
				var $this = $(this);
				var insert_after = $this.data("insert_after");
				if (insert_after != undefined) {
					//noinspection JSUnresolvedVariable
					var app = window.app;
					var $window = $this.closest(".window.list");
					var $th = $this.find("tr:first th:nth-child(" +insert_after + ")");
					var $draggable = ui.draggable;
					var property_name = $draggable.attr("id");
					var after_property_name = $th.attr("id");
					var url = app.uri_base + "/Property/add";
					url += $window.attr("id") + "/" + property_name
						+ "?PHPSESSID=" + app.PHPSESSID + "&as_widget=1"
						+ "&after=" + ((after_property_name != undefined) ? after_property_name : "");
					end($this, event, ui);
					$draggable.closest(".column_select").find("#column_select").hide();

					$.ajax({ url: url, success: function()
					{
						var url = app.uri_base + $window.attr("id")
							+ "?PHPSESSID=" + app.PHPSESSID + "&as_widget=1";
						$.ajax({ url: url, success: function(data)
						{
							var $container = $window.parent();
							$container.html(data);
							$container.children().build();
						} });
					} });

				}
			},

			over: function(event, ui)
			{
				ui.draggable.data("over_droppable", $(this));
			},

			out: function(event, ui)
			{
				end($(this), event, ui);
			}

		});

	});

});
