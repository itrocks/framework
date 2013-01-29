$(window).load(function() {

	$("body").build(function()
	{
		var $this = $(this);
		$this.find(".property.select").prepend($("<span>").addClass("joint"));
		$.ui.dynatree.nodedatadefaults["icon"] = false;
		$this.find(".property.select .tree").dynatree({ debugLevel: 0 });
		$this.find(".property.select input").focus();
		$this.find(".property.select li").draggable({
			appendTo: "body",
			containment: "body",
			cursorAt: { left: 2, top: 10 },
			delay: 500,
			scroll: false,
			helper: function() {
				var $this = $(this);
				console.log($this.attr("id"));
				return $('<div class="property dragging">' + $this.text() + "</div>")
					.attr("id", $this.attr("id"))
					.css("background-color", "white")
					.css("z-index", ++zindex_counter);
			}
		});
	});

});
