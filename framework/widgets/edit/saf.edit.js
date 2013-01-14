$("document").ready(function() {

	$.datepicker.setDefaults($.datepicker.regional["fr"]);
	$("form").build(function() {
		var $this = $(this);

		// .autowidth
		var autowidth_function = function() {
			var $this = $(this);
			var previous_width = $this.width();
			var new_width = getInputTextWidth($this.val());
			this.text_width = new_width;
			if (new_width != previous_width) {
				$table = $this.closest("table.collection");
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
						$td.width(new_width);
						$td.css("max-width", new_width + "px");
						$td.css("min-width", new_width + "px");
					}
					else if (previous_width == previous_max_width) {
						// the element was the widest element : grow or shorten
						new_width = 0;
						$table.find("[name='" + name + "']").each(function() {
							var $this = $(this);
							if (this.text_width == undefined) {
								this.text_width = getInputTextWidth($this.val());
							}
							if (this.text_width > new_width) {
								new_width = this.text_width;
							}
						});
						$td.width(new_width);
						$td.css("max-width", new_width + "px");
						$td.css("min-width", new_width + "px");
					}
				}
				console.log("width was set to " + new_width);
			}
		};
		$this.find(".autowidth").each(autowidth_function);
		$this.find(".autowidth").keyup(autowidth_function);

		// .collection
		$this.find(".minus").click(function() {
			$(this).closest("tr").remove();
		});

		$this.find("table.collection").each(function() {
			this.saf_add = $(this).find("tr.new").clone();
		});

		$this.find("input, textarea").focus(function() {
			var $tr = $(this).closest("tr");
			if ($tr.length && !$tr.next("tr").length) {
				var table = $tr.closest("table.collection")[0];
				var $new_row = table.saf_add.clone();
				$(table).children("tbody").append($new_row);
				$new_row.build();
			}
		});

		// .datetime
		$this.find("input.datetime").datepicker({
			dateFormat: "dd/mm/yy",
			showOn: "button",
			showOtherMonths: true,
			selectOtherMonths: true
		});

		$this.find("input.datetime").blur(function() {
			$(this).datepicker("hide");
		});

		$this.find("input.datetime").keyup(function() {
			$(this).datepicker("show");
		});

		// .object
		$this.find("input.combo").autocomplete({
			autoFocus: true,
			delay: 100,
			minLength: 0,
			close: function(event) {
				$(event.target).keyup();
			},
			source: function(request, response) {
				request["PHPSESSID"] = PHPSESSID;
				$.getJSON(
					uri_root + script_name + "/" + $(this.element).classVar("class") + "/json",
					request,
					function(data, status, xhr) { response(data); }
				);
			},
			select: function(event, ui) {
				$(this).prev().val(ui.item.id);
			}
		});
	});

});
