$('document').ready(function()
{
	window.zindex_counter = 0;

	$('body').build(function()
	{
		if (!this.length) return;

		this.xtarget({
			url_append:      'as_widget',
			draggable_blank: '.window>h2',
			popup_element:   'section',
			success:         function() { $(this).autofocus(); },
			history: {
				condition: '.window>h2',
				title:     '.window>h2'
			}
		});

		// messages is draggable
		this.inside('#messages').draggable();

		// tab controls
		this.inside('.tabber').tabber();

		// draggable objects brought to front on mousedown
		this.inside('.ui-draggable').mousedown(function()
		{
			$(this).css('z-index', ++window.zindex_counter);
		});

		// minimize menu
		this.inside('.menu.output').minimize();

	});

	// focus first form element
	$(this).autofocus();

});
