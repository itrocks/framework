$("document").ready(function()
{

	$("body").build(function()
	{

		this.in(".object").draggable({

			appendTo: "body",
			containment: "body",
			cursorAt: { left: 10, top: 10 },
			delay: 500,
			scroll: false,

			helper: function()
			{
				var $this = $(this);
				var id = ($this.is("a")) ? $this.attr("href") : $this.attr("id");
				if ((id == undefined) || !id.length) id = $this.closest("[id]").attr("id");
				var text = $this.find("h2").text();
				if (!text.length) text = $this.text();
				return $('<div>')
					.addClass("object")
					.attr("id", id)
					.html(text)
					.css("z-index", ++zindex_counter);
			}

		});

		// trash is droppable
		this.in("#trashcan a").droppable({

			accept:     ".column label, .object, .objects, .property",
			hoverClass: "candrop",
			tolerance:  "touch",

			drop: function(event, ui)
			{
				//noinspection JSUnresolvedVariable
				var app = window.app;
				// start
				var href =   event.target.href;
				var id =     ui.helper.attr("id");
				var search = event.target.search;
				var hash =   event.target.hash;
				// calculate destination href
				if (id.substr(0, app.uri_base.length) == app.uri_base) {
					id = id.substr(app.uri_base.length);
				}
				event.target.href = event.target.pathname + "/drop";
				// after trash call is complete, the source window is reloaded to update displayed content
				var $window = ui.draggable.closest(".window");
				if ($window.length) {
					var window_id = $window.attr("id");
					if ((window_id != undefined) && (window_id.indexOf("/") != -1)) {
						$(event.target).data("on-success", function() {
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
				}
				event.target.href += id + search + hash;
				event.target.click();
				// end
				event.target.href = href;
			}
		});

		// trash message can be hidden
		this.in("#trashcan .delete.message").click(function() { $(this).remove(); });

	});

});
