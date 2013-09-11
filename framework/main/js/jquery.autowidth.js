(function($)
{

	$.fn.autowidth = function ()
	{
		this.keyup(function()
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
					if (!$td.length) $td = $($table.find("tbody tr:first td")[position]);
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
		});

	}

})( jQuery );
