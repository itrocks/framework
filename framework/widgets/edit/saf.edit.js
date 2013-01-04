$("document").ready(function() {

	$.datepicker.setDefaults($.datepicker.regional["fr"]);
	$("form").build(function() {
		var $this = $(this);

		// .autowidth
		var width_function = function() { $(this).width(getInputTextWidth(this.value)); };
		$this.find(".autowidth").each(width_function);
		$this.find(".autowidth").keyup(width_function);

		// .collection
		$this.find(".minus").click(function() {
			var row = $(this).parents("tr")[0];
			row.parentNode.removeChild(row);
		});

		$this.find(".plus").each(function() {
			this.saf_add = $(this).parents("table").find("tr.new").clone();
		});

		$this.find(".plus").click(function() {
			var row = this.saf_add.clone();
			$(this).parents("table").children("tbody").append(row);
			row.build();
		});

		$this.find(".plusplus").click(function() {
			var plus = $(this).parents("table").find(".plus");
			for (var i = 0; i < 10; i ++) {
				plus.click();
			}
		});

		// .datetime
		$this.find("input.datetime").datepicker({
			dateFormat: "dd/mm/yy",
			showOtherMonths: true,
			selectOtherMonths: true
		});

		// .object
		$this.find("input.combo").autocomplete({
			autoFocus: true,
			minLength: 0,
			close: function(event) {
				$(event.target).keyup();
			},
			source: function(request, response) {
				request["PHPSESSID"] = PHPSESSID;
				$.getJSON(
					uri_root + "/" + script_name + "/" + $(this.element).classVar("class") + "/json",
					request,
					function(data, status, xhr) { response(data); }
				);
			},
			select: function(event, ui) {
				this.previousSibling.value = ui.item.id;
			}
		});
	});

});
