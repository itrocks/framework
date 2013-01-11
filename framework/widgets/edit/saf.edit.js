$("document").ready(function() {

	$.datepicker.setDefaults($.datepicker.regional["fr"]);
	$("form").build(function() {
		var $this = $(this);

		// .autowidth
		var width_function = function() {
			var $this = $(this);
			$this.width(getInputTextWidth($this.val()));
		};
		$this.find(".autowidth").each(width_function);
		$this.find(".autowidth").keyup(width_function);

		// .collection
		$this.find(".minus").click(function() {
			var row = $(this).closest("tr");
			row.parentNode.removeChild(row);
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
			showOtherMonths: true,
			selectOtherMonths: true
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
