$("document").ready(function()
{

	$("body").build(function()
	{

		/*
		// trashable objects : those which have <h2> are windows
		this.in(".trashable:has(h2)").draggable({

			appendTo: ":parent(div):first",
			containment: "body",
			cursorAt: { left: 2, top: 16 },
			delay: 500,
			handle: "h2",
			scroll: false,

			helper: function()
			{
				return $('<div class="dragging">' + $(this).children("h2").text() + "</div>")
					.css("background-image", $(this).children("h2").css("background-image"))
					.css("z-index", ++window.zindex_counter);
			}

		});

		// trashable objects : all the others
		this.in(".trashable:not(:has(h2))").draggable({

			appendTo: ":parent(div):first",
			containment: "body",
			cursorAt: { left: 2, top: 16 },
			delay: 500,
			scroll: false,

			helper: function()
			{
				return $('<div class="dragging">' + $(this).text() + "</div>")
					.css("background-image", $(this).css("background-image"))
					.css("z-index", ++zindex_counter);
			}

		});
		*/

		// trash is droppable
		this.in("#trashcan a").droppable({

			accept: "label, .property",
			hoverClass: "candrop",
			tolerance: "touch",

			drop: function(event, ui)
			{
				// start
				var href =   event.target.href;
				var search = event.target.search;
				var hash =   event.target.hash;
				// click
				event.target.href = event.target.pathname + "/drop";
				var $window = ui.draggable.closest(".window");
				if ($window.length) {
					//noinspection JSUnresolvedVariable
					var app = window.app;
					// after trash call is complete, the source window is reloaded to update displayed content
					var window_id = $window.attr("id");
					if ((window_id != undefined) && (window_id.indexOf("/") != -1)) {
						$(event.target).data("on.success", function() {
							console.log(app.uri_base + window_id + "?as_widget=1" + app.andSID());
							$.ajax({
								url:     app.uri_base + window_id + "?as_widget=1" + app.andSID(),
								success: function(data) {
									var $parent = $window.parent();
									$parent.html(data);
									$parent.children().build();
								}
							});
						});
					}
					// - a sub-element of a window
					event.target.href += $window.attr("id");
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
		this.in("#trashcan .delete.message").click(function() { $(this).remove(); });

	});

});
