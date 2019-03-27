$('document').ready(function()
{
	window.zindex_counter = 0;

	$('body').build({ always: true, callback: function()
	{
		// auto-focus on first focusable input / select / textarea sub-element
		this.autofocus();

		// confirm dialog
		this.confirm();

		// change all titles attributes to tooltips
		this.tooltip();

		// change all a / form target="#target" to ajax calls
		this.xtarget({
			auto_empty: {'main': 'section#messages'},
			draggable_blank: 'article > header > h2',
			url_append: 'as_widget',
			success: function () {
				$(this).autofocus();
			},
			history: {
				condition: 'article > header > h2',
				title: 'article > header > h2',
				without_get_vars: ['.*/list\\?.*']
			}
		});
	}});

	//------------------------------------------------------------ section#messages draggable & click
	$('section#messages').build(function()
	{
		this.draggable().click(function(event)
		{
			if (event.offsetX > (this.clientWidth - 10) && (event.offsetY < 10)) {
				$(this).empty();
			}
		});
	});

	//----------------------------------------------------------------------- .list-filter listFilter
	$('.list-filter').build($.fn.listFilter);

	//-------------------------------------------------------------------------------- .tabber tabber
	$('.tabber').build($.fn.tabber);

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

	//------------------------------------------------------------ autoHeight, autoWidth, changeState
	$('.auto_height').autoHeight();
	$('.auto_width').autoWidth();
	$('input:visible, textarea').changeState();

});
