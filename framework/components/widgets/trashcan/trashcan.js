$("document").ready(function() {

	$("body").build(function() {
		var $this = $(this);

		// trashable objects
		window.zindex_counter = 0;
		$this.find(".trashable:has(h2)").draggable({
			appendTo: ":parent(div):first",
			containment: "body",
			cursorAt: { left: 2, top: 16 },
			delay: 500,
			handle: "h2",
			scroll: false,
			helper: function() {
				return $('<div class="dragging">' + $(this).children("h2").text() + "</div>")
					.css("background-image", $(this).children("h2").css("background-image"))
					.css("z-index", ++window.zindex_counter);
			}
		});
		$this.find(".trashable:not(:has(h2))").draggable({
			appendTo: ":parent(div):first",
			containment: "body",
			cursorAt: { left: 2, top: 16 },
			delay: 500,
			scroll: false,
			helper: function() {
				return $('<div class="dragging">' + $(this).text() + "</div>")
					.css("background-image", $(this).css("background-image"))
					.css("z-index", ++zindex_counter);
			}
		});

		// trash is droppable
		$this.find(".trashcan a").droppable({
			accept: ".trashable",
			hoverClass: "candrop",
			tolerance: "touch",
			drop: function(event, ui) {
				// start
				var href = event.target.href;
				var search = event.target.search;
				var hash = event.target.hash;
				// click
				event.target.href = event.target.pathname + "/drop";
				var $window = ui.draggable.parent().closest("div.window");
				if ($window.length) {
					// - a sub-element of a window
					event.target.href += $window.attr("id");
					var app = window.app;
					var trash = (ui.draggable.classVar("trash"));
					var id = (ui.draggable.attr("id") != undefined)
						? ui.draggable.attr("id")
						: ui.draggable.parent().attr("id");
					if (id == undefined && ui.draggable.attr("href")) {
						id = ui.draggable.attr("href").rParse(app.script_name).lParse("?");
					}
					if ((trash == undefined) && ((id == undefined) || (id.indexOf("/") == -1))) {
						if (ui.draggable.hasClass("button")) {
							trash = "Button";
							if (id == undefined) {
								id = ui.draggable.attr("href")
									.rLastParse("/", 1, true).lLastParse("?");
							}
						}
						else if (
							(id != undefined) && (id.indexOf("/") == -1)
							) {
							trash = "Property";
						}
						else if (ui.draggable.hasClass("ui-tabber-tab")) {
							trash = "Tab";
							id = ui.draggable.find("a").attr("href").rLastParse("#");
						}
					}
					else {
						// - a window itself
						event.target.href += ui.draggable.attr("id");
					}
					if ((id != undefined) && (id.substr(0, 1) != "/")) {
						id = "/" + id;
					}
					if (trash) {
						event.target.href += "/" + trash;
					}
					if (id != undefined) {
						event.target.href += id;
					}
				}
				event.target.href += search + hash;
				event.target.click();
				// end
				event.target.href = href;
			}
		});

		// trash message can be hidden
		$this.find(".trashcan .delete.message").click(function() { $(this).remove(); });

	});

});
