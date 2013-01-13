$("document").ready(function() {

	$.datepicker.setDefaults($.datepicker.regional["fr"]);
	$("form").build(function() {
		var $this = $(this);

		// .autowidth
		var width_function = function() {
			var $this = $(this);
			var already = this.text_width;
			this.text_width = getInputTextWidth($this.val());
			$table = $this.closest("table.collection");
			if ($table.length) {
				var name = $this.attr("name");
				var next_input = false;
				if (name == undefined) {
					name = $this.prev("input").attr("name");
					next_input = true;
				}
				var width = this.text_width;
				var $list = $table.find("[name='" + name + "']");
				$list.each(function() {
					var $element = next_input ? $(this).next("input") : $(this);
					element = $element.get();
					// TODO : is always undefined, should not
					if (element.text_width == undefined) {
						element.text_width = getInputTextWidth($element.val());
					}
					width = Math.max(width, element.text_width);
				});
				$list.each(function() {
					var $this = next_input ? $(this).next("input") : $(this);
					$this.width(width);
				});
			}
			else {
				$this.width(this.text_width);
			}
		};
		$this.find(".autowidth").each(width_function);
		$this.find(".autowidth").keyup(width_function);

		// .collection
		$this.find(".minus").click(function() {
			$(this).closest("tr").remove();
		});

		$this.find(".plus").each(function() {
			this.saf_add = $(this).closest("table").find("tr.new").clone();
		});

		$this.find(".plus").click(function() {
			var row = this.saf_add.clone();
			$(this).closest("table").children("tbody").append(row);
			row.build();
		});

		$this.find(".plusplus").click(function() {
			var plus = $(this).closest("table").find(".plus");
			for (var i = 0; i < 10; i ++) {
				plus.click();
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
