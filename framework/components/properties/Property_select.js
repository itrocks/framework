$("document").ready(function()
{

	$(".window").build(function()
	{
		var $this = $(this);

		// decoration
		$this.find(".property.select").prepend($("<span>").addClass("joint"));

		// create tree
		$this.find("ul.treeview li a").click(function(event)
		{
			var $this = $(this);
			var $li = $(this).closest("li");
			if ($li.children("div").length) {
				if ($li.children("div:visible").length) {
					$this.removeClass("expanded");
					$li.children("div:visible").hide();
				}
				else {
					$this.addClass("expanded");
					$li.children("div:not(:visible)").show();
				}
				event.stopImmediatePropagation();
				event.preventDefault();
			}
			else {
				$this.addClass("expanded");
			}
		});

		// draggable items
		$this.find(".property").draggable({

			appendTo:    "body",
			containment: "body",
			cursorAt:    { left: 2, top: 10 },
			delay:       500,
			scroll:      false,

			helper: function()
			{
				var $this = $(this);
				return $('<div>')
					.addClass("property")
					.attr("id", $this.attr("id"))
					.css("background-color", "white")
					.css("z-index", ++zindex_counter)
					.html($this.text());
			},

			drag: function(event, ui)
			{
				var $droppable = $(this).data("over_droppable");
				if ($droppable != undefined) {
					var draggable_left = ui.offset.left;
					var count = 0;
					var found = 0;
					$droppable.find("tr:first th:not(:first)").each(function() {
						count ++;
						var $this = $(this);
						var $prev = $this.prev("th");
						var left = $prev.offset().left + $prev.width();
						var right = $this.offset().left + $this.width();
						if ((draggable_left > left) && (draggable_left <= right)) {
							found = (draggable_left <= ((left + right) / 2))
								? count
								: (count + 1);
							var old = $droppable.data("insert_after");
							if (found != old) {
								if (old != undefined) {
									$droppable.find("th:nth-child(" + old + "),td:nth-child(" + old + ")")
										.removeClass("insert_after");
								}
								$droppable.find("th:nth-child(" + found + "),td:nth-child(" + found + ")")
									.addClass("insert_after");
								$droppable.data("insert_after", found);
							}
						}
					});
				}
			},

			stop: function(event, ui)
			{
				var $droppable = $(this).data("over_droppable");
				if ($droppable != undefined) {
				}
			}

		});

		// focus into search input
		$this.find(".property.select .search input").focus();

	});

});
