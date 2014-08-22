$('document').ready(function()
{
	window.zindex_counter = 0;

	$('body').build(function()
	{

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
		this.in('#messages').draggable();

		// tab controls
		this.in('.tabber').tabber();

		// draggable objects brought to front on mousedown
		this.in('.ui-draggable').mousedown(function()
		{
			$(this).css('z-index', ++window.zindex_counter);
		});

		// minimize menu
		this.in('.menu.output').minimize();

	});

	// focus first form element
	$(this).autofocus();

});
