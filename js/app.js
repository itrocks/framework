$(document).ready(function()
{
	window.zindex_counter = 0;

	$('body').build({ always: true, callback: function()
	{
		this.autofocus();
		this.confirm();
		this.tooltip();

		// change all a / form target="#target" to ajax calls
		this.xtarget({
			auto_empty:      {'main': 'div#messages'},
			draggable_blank: 'header',
			history: {
				condition:        'h2',
				title:            'h2',
				without_get_vars: ['.*/list\\?.*']
			},
			url_append: 'as_widget'
		});
	}});

	//---------------------------------------------------------------- div#messages draggable & click
	$('div#messages').build(function()
	{
		this.draggable().click(function(event)
		{
			if (event.offsetX > (this.clientWidth - 10) && (event.offsetY < 10)) {
				$(this).empty();
			}
		});
	});

	//----------------------------------------------------------------------- .ui-draggable mousedown
	/**
	 * draggable objects brought to front on mousedown
	 */
	$('.ui-draggable').build(function() {
		this.mousedown(function()
		{
			$(this).css('z-index', ++window.zindex_counter);
		});
	});

	//----------------------------------------------------------------------------- nav#menu minimize
	$('nav#menu').build(function() {
		this.minimize({ absolute_next: true });
	});

	//--------------------------------------------------------------------- build simple plugin calls
	$('.auto_height').build($.fn.autoHeight);
	$('.auto_width:not(table):not(ul)').build($.fn.autoWidth);
	$('input:visible, textarea:visible').build($.fn.changeState);
	$('.list-filter').build($.fn.listFilter);
	$('.tabber').build($.fn.tabber);
	$('.vertical.scrollbar').build($.fn.verticalscrollbar);

});
