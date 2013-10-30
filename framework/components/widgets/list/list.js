$("document").ready(function()
{

	$(".list.window").build(function()
	{

		this.in(".list.window").each(function()
		{
			var $this = $(this);

			//--------------------------------------------------- .search input, .search textarea keydown
			// reload list when #13 pressed into a search input
			$this.find(".search").find("input, textarea").keydown(function(event)
			{
				if (event.keyCode == 13) {
					$(this).closest("form").submit();
				}
			});

			//-------------------------------------------------------------- .column_select>a.popup click
			// column select popup
			$this.find(".column_select>a.popup").click(function(event)
			{
				var $this = $(this);
				var $div = $this.closest(".column_select").find("#column_select");
				if ($div.length) {
					if ($div.is(":visible")) {
						$div.hide();
					}
					else {
						$div.show();
						$div.find("input").first().focus();
					}
					event.stopImmediatePropagation();
					event.preventDefault();
				}
			});

			//-------------------------------------------------------------------------- >table droppable
			// when a property is dropped between two columns
			var complete = function($this, event, ui)
			{
				var insert_after = $this.data("insert-after");
				if (insert_after != undefined) {
					$this.find("th:nth-child(" + insert_after + "), td:nth-child(" + insert_after + ")")
						.removeClass("insert_after");
					$this.removeData("insert-after");
				}
				ui.draggable.removeData("over-droppable");
			};

			$this.children("table").droppable({
				accept:    ".property",
				tolerance: "touch",

				drop: function(event, ui)
				{
					var $this = $(this);
					var insert_after = $this.data("insert-after");
					if (insert_after != undefined) {
						//noinspection JSUnresolvedVariable
						var app = window.app;
						var $window = $this.closest(".list.window");
						var $th = $this.find("thead>tr:first>th:nth-child(" + insert_after + ")");
						var $draggable = ui.draggable;
						var property_name = $draggable.attr("id");
						var after_property_name = $th.attr("id");
						var class_name = $window.attr("id").split("/")[1];
						var url = app.uri_base + "/" + class_name + "/listSetting"
							+ "?add_property=" + property_name
							+ "&after=" + ((after_property_name != undefined) ? after_property_name : "")
							+ "&as_widget=1"
							+ app.andSID();
						complete($this, event, ui);

						$.ajax({ url: url, success: function()
						{
							var url = app.uri_base + $window.attr("id") + window.app.askSIDand() + "as_widget=1";
							$.ajax({ url: url, success: function(data)
							{
								var $container = $window.parent();
								$container.html(data);
								$container.children().build();
							}});
						}});

					}
				},

				over: function(event, ui)
				{
					ui.draggable.data("over-droppable", $(this));
				},

				out: function(event, ui)
				{
					complete($(this), event, ui);
				}

			});

			//------------------------------------------ .window.title, table.list th.property a modifiable
			// modifiable list and columns titles
			var className = function($this)
			{
				return $this.closest(".list.window").attr("id").split("/")[1];
			};
			var propertyPath = function($this)
			{
				return $this.closest("th").attr("id");
			};
			var uri = window.app.uri_base + "/{className}/listSetting"
				+ window.app.askSIDand() + "as_widget=1";
			// list title (class name) double-click
			$this.children("h2").modifiable({
				done: uri + "&title={value}",
				aliases: { "className": className },
				target: "#messages"
			});
			// list column header (property path) double-click
			$this.find("table>thead>tr>th.property>a").modifiable({
				done: uri + "&property_path={propertyPath}&property_title={value}",
				aliases: { "className": className, "propertyPath": propertyPath },
				target: "#messages"
			});

			//------------------------------------------------------------------------- .vertical.scrollbar
			// vertical scrollbar
			var redeem = function()
			{
				var $this     = $(this);
				var $rowheight = $this.closest("tr").height() - 1;
				var $up       = $this.children(".up");
				var $position = $this.children(".position");
				var $down     = $this.children(".down");
				var start     = $this.data("start") - 1;
				var length    = $this.data("length");
				var total     = $this.data("total");
				var height    = Math.max(($rowheight * length) - 1, $this.innerHeight());
				var real_start  = Math.round((start * height) / total);
				var real_height = Math.max(Math.round((length * height) / total), 16);
				if ((real_start + real_height) > height) {
					real_start = height - real_height;
				}
				var real_end = height - real_start - real_height;
				$up.height(real_start);
				$position.height(real_height);
				$down.height(real_end);
			};

			$this.find(".vertical.scrollbar").each(redeem);
			$this.find(".vertical.scrollbar .position").each(function()
			{
				var $this = $(this);
				$this.draggable({
					containment: $this.parent(),
					opacity: .7,

					stop: function()
					{
						var $this = $(this);
						var $scrollbar = $this.parent();
						var old_start = $scrollbar.data("start");
						var start = $scrollbar.children(".up").height() + parseInt($this.css("top").replace("px", ""));
						var new_start = Math.round(((start * $scrollbar.data("total")) / $scrollbar.innerHeight())) + 1;
						$this.attr("href", $this.attr("href").replace("=" + old_start, "=" + new_start));
						$this.click();
					}

				});
			});
		});
	});
});
