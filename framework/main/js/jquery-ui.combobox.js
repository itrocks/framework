(function($) {

	// TODO everything

	$.fn.combobox = function(options)
	{
		//------------------------------------------------------------------------------------ settings
		var settings = $.extend({
			link:  "",
			value: "add"
		}, options);

		this.autocomplete(options);
		this.mouseover(function(event) {
			var position = $(this).offset();
			position.left += $(this).width();
			$('<a href="' + options["link"] + '">' + options["value"] + '</a>')
		});
	}

});
