$("document").ready(function() {

	// .datetime
	$.datepicker.setDefaults($.datepicker.regional["fr"]);
	$("input.datetime").datepicker({
		dateFormat: "dd/mm/yy",
		showOtherMonths: true,
		selectOtherMonths: true
	});

	// .object
	$("input.combo").autocomplete({
		source: ["CLI1: Client Un", "CLI2: Client Deux"],
		close: function(event) { $(event.target).keyup(); }
	});

	// .autowidth
	var width_function = function() { $(this).width(getInputTextWidth(this.value)); };
	$(".autowidth").each(width_function);
	$(".autowidth").keyup(width_function);

});
