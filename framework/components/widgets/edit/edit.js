$("document").ready(function()
{

	$("form").build(function()
	{
		//noinspection JSUnresolvedVariable
		var app = window.app;

		// .autowidth
		var autowidth_function = function()
		{
			var $this = $(this);
			var previous_width = parseInt($this.attr("ui-text-width"));
			var new_width = getInputTextWidth($this);
			$this.attr("ui-text-width", new_width);
			if (new_width != previous_width) {
				var $table = $this.closest("table.collection, table.map");
				if (!$table.length) {
					// single element
					$this.width(new_width);
				}
				else {
					// element into a collection / map
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
		var autowidth_elements = this.in(".autowidth");
		autowidth_elements.each(autowidth_function);
		autowidth_elements.keyup(autowidth_function);

		// .autoheight
		var autoheight_function = function()
		{
			var $this = $(this);
			var previous_height = parseInt($this.attr("ui-text-height"));
			var new_height = getInputTextHeight($this);
			$this.attr("ui-text-height", new_height);
			if (new_height != previous_height) {
				var $table = $this.closest("table.collection, table.map");
				if (!$table.length) {
					// single element
					$this.height(new_height);
					$this.css("overflow","hidden");
				}
				else {
					// element into a collection / map
					// is element not named and next to a named element ? next_input = true
					var name = $this.attr("name");
					var next_input = false;
					if (name == undefined) {
						name = $this.prev("input").attr("name");
						next_input = true;
					}
					// calculate th's previous max height
					var position = $this.closest("td").prevAll("td").length;
					var $td = $($table.find("thead tr:first th")[position]);
					var previous_max_height = $td.height();
					if (new_height > previous_max_height) {
						// the element became wider than the widest element
						$td.height(new_height + $td.css("padding-top") + $td.css("padding-bottom"));
						$td.css("max-height", new_height + "px");
						$td.css("min-height", new_height + "px");
					}
					else if (previous_height == previous_max_height) {
						// the element was the widest element : grow or shorten
						new_height = 0;
						$table.find("[name='" + name + "']").each(function()
						{
							var $this = $(this);
							if (next_input) {
								$this = $this.next("input");
							}
							var ui_text_height = parseInt($this.attr("ui-text-height"));
							if (ui_text_height == 0) {
								$this.attr(
									"ui-text-height", ui_text_height = getTextHeight($this.val())
								);
							}
							if (ui_text_height > new_height) {
								new_height = ui_text_height;
							}
						});
						$td.height(new_height);
						$td.css("max-height", new_height + "px");
						$td.css("min-height", new_height + "px");
					}
				}
			}
		};
		var autoheight_elements = this.in(".autoheight");
		autoheight_elements.each(autoheight_function);
		autoheight_elements.keyup(autoheight_function);

		// .collection / .map
		this.in(".minus").click(function()
		{
			if ($(this).closest("tbody").children().length > 1) {
				$(this).closest("tr").remove();
			}
		});

		this.in("table.collection, table.map").each(function()
		{
			var $this = $(this);
			$this.data("saf_add", $this.find("tr.new").clone());
		});

		this.in("input, textarea").focus(function()
		{
			var $tr = $(this).closest("tr");
			if ($tr.length && !$tr.next("tr").length) {
				var $collection = $tr.closest("table.collection, table.map");
				if ($collection.length) {
					var $table = $($collection[0]);
					var $new_row = $table.data("saf_add").clone();
					$table.children("tbody").append($new_row);
					$new_row.build();
				}
			}
		});

		// .datetime
		this.in("input.datetime").datepicker({
			dateFormat:        dateFormatToDatepicker(app.date_format),
			showOn:            "button",
			showOtherMonths:   true,
			selectOtherMonths: true
		});

		this.in("input.datetime").blur(function()
		{
			$(this).datepicker("hide");
		});

		this.in("input.datetime").keyup(function(event)
		{
			if ((event.keyCode != 13) && (event.keyCode != 27)) {
				$(this).datepicker("show");
			}
		});

		// .object
		this.in("input.combo").change(function()
		{
			var $this = $(this);
			if (!$this.val().length) {
				$this.prev().removeAttr("value");
			}
		});

		// .object combo
		this.in("input.combo").autocomplete({
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
					app.uri_base + "/" + $(this.element).classVar("class") + "/json",
					request,
					function(data) { response(data); }
				);
			},

			select: function(event, ui)
			{
				$(this).prev().val(ui.item.id);
			}

		});

		// .object add button
		this.in("a.add.action").attr("tabindex", -1);
		if (this.attr("id") && (this.attr("id").substr(0, 6) == "window")) {
			this.in(".close.button")
				.attr("href", "javascript:$('#" + this.attr("id") + "').remove()")
				.attr("target", "");
			var $write = this.in(".write.button");
			$write.attr("href", $write.attr("href") +
				(($write.attr("href").indexOf("?") > -1) ? "&" : "?")
				+ "close=" + this.attr("id")
			);
		}
		this.in("input.combo").each(function()
		{
			var $this = $(this);
			var $field = $this.parents("div.field");
			var $anchor = $this.parent().children("a.add.action");
			$field.mouseenter(function()
			{
				if ($anchor.data("saf_visibility")) {
					$anchor.data("saf_visibility", $anchor.data("saf_visibility") + 1);
				}
				else {
					$anchor.data("saf_visibility", 1);
				}
				$anchor.addClass("visible");
			});
			$field.mouseleave(function()
			{
				$anchor.data("saf_visibility", $anchor.data("saf_visibility") - 1);
				if (!$anchor.data("saf_visibility")) {
					$anchor.removeClass("visible");
				}
			});
		});

	});

});
