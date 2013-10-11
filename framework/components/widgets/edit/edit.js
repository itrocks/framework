$("document").ready(function()
{

	$("form").build(function()
	{
		//noinspection JSUnresolvedVariable
		var app = window.app;

		//--------------------------------------------------------------------- .autoheight, .autowidth
		this.in(".autoheight").autoheight();
		this.in(".autowidth").autowidth();

		//-------------------------------------------------------------------------------------- .minus
		this.in(".minus").click(function()
		{
			if ($(this).closest("tbody").children().length > 1) {
				$(this).closest("tr").remove();
			}
		});

		//----------------------------------------------------------------- table.collection, table.map
		this.in("table.collection, table.map").each(function()
		{
			var $this = $(this);
			$this.data("saf_add", $this.children("tbody").children("tr.new").clone());
			$this.data("saf_add_indice", $this.children("tbody").children("tr").length - 1);
		});

		this.in("input, textarea").focus(function()
		{
			var $tr = $(this).closest("tr");
			if ($tr.length && !$tr.next("tr").length) {
				var $collection = $tr.closest("table.collection, table.map");
				if ($collection.length) {
					var $table = $($collection[0]);
					var $new_row = $table.data("saf_add").clone();
					var indice = $table.children("tbody").children("tr").length;
					var old_indice = $table.data("saf_add_indice");
					$new_row.html($new_row.html().repl("][" + old_indice + "]", "][" + indice + "]"));
					$table.children("tbody").append($new_row);
					$new_row.build();
				}
			}
		});

		//------------------------------------------------------------------- input.datetime datepicker
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

		//-------------------------------------------------------------------------- input.combo change
		this.in("input.combo").change(function()
		{
			var $this = $(this);
			if (!$this.val().length) {
				$this.prev().removeAttr("value");
			}
		});

		//-------------------------------------------------------------------- input.combo autocomplete
		this.in("input.combo").autocomplete(
		{
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
				var $element = this.element;
				if (!app.use_cookies) request["PHPSESSID"] = app.PHPSESSID;
				var filters = $element.attr("data-combo-filters");
				if (filters != undefined) {
					filters = filters.split(",");
					for (var key in filters) if (filters.hasOwnProperty(key)) {
						var filter = filters[key].split("=");
						var $filter_element = $(this.element.get(0).form).find('[name="' + filter[1] + '"]');
						if ((filter[0].substr(0, 3) != "id_") || $filter_element.val()) {
							request["filters[" + filter[0] + "]"] = $filter_element.val();
						}
					}
				}
				$.getJSON(
					app.uri_base + "/" + $element.attr("data-combo-class") + "/json",
					$.param(request),
					function(data) { response(data); }
				);
			},

			select: function(event, ui)
			{
				$(this).prev().val(ui.item.id);
			}

		});

		// --------------------------------------------------------------------- input.combo ctrl+click
		this.in("input.combo").click(function(event)
		{
			if (event.ctrlKey) {
				$(this).parent().children("a.add.action").click();
			}
		})
		.keyup(function(event) {
			if (event.keyCode == 27) {
				$(this).val("");
				$(this).prev().val("");
			}
		});

		// ------------------------------------------------------------------------------- a.add.action
		this.in("a.add.action").click(function()
		{
			var $this = $(this);
			var $input = $this.parent().children("input.combo");
			if (!$this.data("link")) {
				$this.data("link", $this.attr("href"));
			}
			var href = $this.data("link");
			var id = $input.prev().val();
			$this.attr("href", id ? href.repl("/new?", "/" + $input.prev().val() + "/edit?") : href);
		});
		this.in("a.add.action").attr("tabindex", -1);
		if (this.attr("id") && (this.attr("id").substr(0, 6) == "window")) {
			this.in(".close.button a")
				.attr("href", "javascript:$('#" + this.attr("id") + "').remove()")
				.attr("target", "");
			var $button = this.in(".write.button a");
			$button.attr("href", $button.attr("href") +
				(($button.attr("href").indexOf("?") > -1) ? "&" : "?")
				+ "close=" + this.attr("id")
			);
		}
		this.in("input.combo").each(function()
		{
			$(this).parent()
				.mouseenter(function() { $(this).children("a.add.action").addClass("visible"); })
				.mouseleave(function() { $(this).children("a.add.action").removeClass("visible"); });
		});

		//-------------------------------------------------------------------------- button.more.action
		this.in("button.more.action").click(function(event)
		{
			event.preventDefault();
			var $combo = $($(this).parent().find("input.combo"));
			if (!$combo.autocomplete("widget").is(":visible")) {
				$combo.focus();
				$combo.autocomplete("search", "");
			}
		});

		//---------------------------------------------------------------------- input[data-conditions]
		this.in("input[data-conditions]").each(function()
		{
			var $this = $(this);
			var conditions = $this.attr("data-conditions");
			if (conditions != undefined) {
				conditions = conditions.split(",");
				for (var key in conditions) if (conditions.hasOwnProperty(key)) {
					var condition = conditions[key].split("=");
					var $condition_element = $($this.get(0).form).find('[name="' + condition[0] + '"]');
					if ($condition_element.data("condition_of") == undefined) {
						$condition_element.data("condition_of", [{ element: $this, value: condition[1] }]);

						$condition_element.change(function()
						{
							var $this = $(this);
							var condition_of = $this.data("condition_of");
							for (var key in condition_of) if (condition_of.hasOwnProperty(key)) {
								var condition = condition_of[key];
								if ($this.val() == condition.value) {
									condition.element.parent().find("input, button").show();
								}
								else {
									condition.element.parent().find("input, button").hide();
								}
							}
						});

					}
					else {
						var condition_of = $condition_element.data("condition_of");
						condition_of.push({ element: $this, value: condition[1] });
						$condition_element.data("condition_of", condition_of);
					}
					$condition_element.change();
				}
			}
		});

	});

});
