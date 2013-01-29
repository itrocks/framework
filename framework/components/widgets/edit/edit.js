$("document").ready(function()
{

	$("form").build(function()
	{
		//noinspection JSUnresolvedVariable
		var app = window.app;
		var $this = $(this);

		// .autoheight
		var autoheight_function = function()
		{
			var $this = $(this);
			var match = ($this.val().indexOf("\n") > -1);
			if (match) {
				$this.attr("rows", $this.val().split("\n").length);
			}
		};
		$this.find(".autoheight").each(autoheight_function);
		$this.find(".autoheight").keyup(autoheight_function);

		// .autowidth
		var autowidth_function = function()
		{
			var $this = $(this);
			var previous_width = parseInt($this.attr("ui-text-width"));
			var new_width = getInputTextWidth($this.val());
			$this.attr("ui-text-width", new_width);
			if (new_width != previous_width) {
				var $table = $this.closest("table.collection");
				if (!$table.length) {
					// single element
					$this.width(new_width);
				}
				else {
					// element into a collection
					// is element not named and next to a named element ? next_input = true
					var name = $this.attr("name");
					var next_input = false;
					if (name == undefined) {
						name = $this.prev("input").attr("name");
						next_input = true;
					}
					// calculate th's previous max width
					var position = $this.closest("td").prevAll("td").length;
					var $td = $($table.find("thead tr:first th")[position]);
					var previous_max_width = $td.width();
					if (new_width > previous_max_width) {
						// the element became wider than the widest element
						$td.width(new_width + $td.css("padding-left") + $td.css("padding-right"));
						$td.css("max-width", new_width + "px");
						$td.css("min-width", new_width + "px");
					}
					else if (previous_width == previous_max_width) {
						// the element was the widest element : grow or shorten
						new_width = 0;
						$table.find("[name='" + name + "']").each(function()
						{
							var $this = $(this);
							if (next_input) {
								$this = $this.next("input");
							}
							var ui_text_width = parseInt($this.attr("ui-text-width"));
							if (ui_text_width == 0) {
								$this.attr(
									"ui-text-width", ui_text_width = getInputTextWidth($this.val())
								);
							}
							if (ui_text_width > new_width) {
								new_width = ui_text_width;
							}
						});
						$td.width(new_width);
						$td.css("max-width", new_width + "px");
						$td.css("min-width", new_width + "px");
					}
				}
			}
		};
		$this.find(".autowidth").each(autowidth_function);
		$this.find(".autowidth").keyup(autowidth_function);

		// .collection
		$this.find(".minus").click(function()
		{
			// On empêche la suppression du dernier élément
			if($(this).closest("tbody").children().length > 1)
				$(this).closest("tr").remove();
		});

		$this.find("table.collection").each(function()
		{
			var $this = $(this);
			$this.data("saf_add", $this.find("tr.new").clone());
		});

		$this.find("input, textarea").focus(function()
		{
			var $tr = $(this).closest("tr");
			if ($tr.length && !$tr.next("tr").length) {
				var $collection = $tr.closest("table.collection");
				if ($collection.length) {
					var $table = $($collection[0]);
					var $new_row = $table.data("saf_add").clone();
					$table.children("tbody").append($new_row);
					$new_row.build();
				}
			}
		});

		// .datetime
		$this.find("input.datetime").datepicker({
			dateFormat:        dateFormatToDatepicker(app.date_format),
			showOn:            "button",
			showOtherMonths:   true,
			selectOtherMonths: true
		});

		$this.find("input.datetime").blur(function()
		{
			$(this).datepicker("hide");
		});

		$this.find("input.datetime").keyup(function(event)
		{
			if ((event.keyCode != 13) && (event.keyCode != 27)) {
				$(this).datepicker("show");
			}
		});

		// .object
		$this.find("input.combo").autocomplete({
			autoFocus: true,
			delay: 100,
			minLength: 0,

			close: function(event)
			{
				$(event.target).keyup();
			},

			source: function(request, response)
			{
				//noinspection JSUnresolvedVariable
				var app = window.app;
				request["PHPSESSID"] = app.PHPSESSID;
				$.getJSON(
					app.uri_root + app.script_name + "/" + $(this.element).classVar("class") + "/json",
					request,
					function(data) { response(data); }
				);
			},

			select: function(event, ui)
			{
				$(this).prev().val(ui.item.id);
			}

		});

	});

});
