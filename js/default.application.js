$('document').ready(function()
{
	window.zindex_counter = 0;

	$('body').build(function()
	{
		if (!this.length) return;

		this.confirm();

		this.xtarget({
			auto_empty:      {'#main': '#messages'},
			draggable_blank: '.window>h2',
			popup_element:   'section',
			url_append:      'as_widget',
			success:         function() { $(this).autofocus(); },
			history: {
				condition:        '.window>h2',
				title:            '.window>h2',
				without_get_vars: ['.*/list\\?.*']
			}
		});

		// messages is draggable and closable
		this.inside('#messages').draggable().click(function(event)
		{
			if (event.offsetX > this.clientWidth - 10 && event.offsetY < 10) {
				$(this).empty();
			}
		});

		// list filter
		this.inside('.list-filter').listFilter();

		// tab controls
		this.inside('.tabber').tabber();

		// draggable objects brought to front on mousedown
		this.inside('.ui-draggable').mousedown(function()
		{
			$(this).css('z-index', ++window.zindex_counter);
		});

		// minimize menu
		$('#menu').minimize({ absolute_next: true });

		// change all titles attributes to tooltips
		this.tooltip();

	}).autofocus();

});
