$("document").ready(function()
{

	// .datetime
	$.datepicker.setDefaults($.datepicker.regional["fr"]);
	$("input.datetime").datepicker({
		dateFormat: "dd/mm/yy",
		showOtherMonths: true,
		selectOtherMonths: true
	});

	// .object
	$("input.combo").autocomplete({
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

	// .autowidth
	var width_function = function() { $(this).width(getInputTextWidth(this.value)); };
	$(".autowidth").each(width_function);
	$(".autowidth").keyup(width_function);

});
